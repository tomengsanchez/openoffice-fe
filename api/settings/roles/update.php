<?php
// api/settings/roles/update.php
global $smp, $params;

// The Auth class has already verified the user has the 'roles:update' permission.

// Get the request body
$data = json_decode(file_get_contents('php://input'), true) ?: [];

// Get role ID from URL parameter
$role_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate role ID
if ($role_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid role ID.'
    ]);
    exit;
}

// Check if role exists
$stmt = $smp->prepare("SELECT id FROM roles WHERE id = ?");
$stmt->bind_param('i', $role_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Role not found.'
    ]);
    exit;
}

// Check if role with the same name already exists (if name is being updated)
if (!empty($data['role_name'])) {
    $stmt = $smp->prepare("SELECT id FROM roles WHERE role_name = ? AND id != ?");
    $stmt->bind_param('si', $data['role_name'], $role_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode([
            'status' => 'error',
            'message' => 'A role with this name already exists.'
        ]);
        exit;
    }
}

try {
    // Start transaction
    $smp->begin_transaction();

    // Update role details if provided
    if (!empty($data['role_name']) || isset($data['role_description'])) {
        $update_fields = [];
        $update_types = '';
        $update_values = [];
        
        if (!empty($data['role_name'])) {
            $update_fields[] = 'role_name = ?';
            $update_types .= 's';
            $update_values[] = $data['role_name'];
        }
        
        if (isset($data['role_description'])) {
            $update_fields[] = 'role_description = ?';
            $update_types .= 's';
            $update_values[] = $data['role_description'];
        }
        
        if (!empty($update_fields)) {
            $update_types .= 'i'; // for the WHERE clause
            $update_values[] = $role_id;
            
            $sql = "UPDATE roles SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $smp->prepare($sql);
            $stmt->bind_param($update_types, ...$update_values);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update role: " . $stmt->error);
            }
        }
    }
    
    // Update permissions if provided
    if (isset($data['permissions']) && is_array($data['permissions'])) {
        // Remove existing permissions
        $stmt = $smp->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->bind_param('i', $role_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to remove existing permissions: " . $stmt->error);
        }
        
        // Add new permissions if any
        $permission_ids = array_map('intval', $data['permissions']);
        $permission_ids = array_unique($permission_ids);
        
        if (!empty($permission_ids)) {
            // Verify that all permission IDs exist
            $placeholders = implode(',', array_fill(0, count($permission_ids), '?'));
            $types = str_repeat('i', count($permission_ids));
            
            $stmt = $smp->prepare("SELECT id FROM permissions WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$permission_ids);
            $stmt->execute();
            $existing_permissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (count($existing_permissions) !== count($permission_ids)) {
                throw new Exception("One or more permission IDs are invalid.");
            }
            
            // Insert role-permission relationships
            $stmt = $smp->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            
            foreach ($permission_ids as $permission_id) {
                $stmt->bind_param('ii', $role_id, $permission_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to assign permission: " . $stmt->error);
                }
            }
        }
    }
    
    // Commit the transaction
    $smp->commit();
    
    // Get the updated role with its permissions
    $stmt = $smp->prepare("
        SELECT r.*, GROUP_CONCAT(rp.permission_id) as permission_ids
        FROM roles r
        LEFT JOIN role_permissions rp ON r.id = rp.role_id
        WHERE r.id = ?
        GROUP BY r.id
    ");
    $stmt->bind_param('i', $role_id);
    $stmt->execute();
    $role = $stmt->get_result()->fetch_assoc();
    
    if ($role) {
        // Convert the comma-separated permission_ids to an array
        $role['permission_ids'] = $role['permission_ids'] ? explode(',', $role['permission_ids']) : [];
    }
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Role updated successfully.',
        'data' => $role
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $smp->rollback();
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating the role: ' . $e->getMessage()
    ]);
}
