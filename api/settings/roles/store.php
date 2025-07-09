<?php
// api/settings/roles/store.php
global $smp, $params;

// The Auth class has already verified the user has the 'roles:store' permission.

// Get the request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['role_name'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Role name is required.'
    ]);
    exit;
}

// Check if role with the same name already exists
$stmt = $smp->prepare("SELECT id FROM roles WHERE role_name = ?");
$stmt->bind_param('s', $data['role_name']);
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

try {
    // Start transaction
    $smp->begin_transaction();

    // Insert the new role
    $stmt = $smp->prepare("INSERT INTO roles (role_name, role_description) VALUES (?, ?)");
    $role_description = $data['role_description'] ?? null;
    $stmt->bind_param('ss', $data['role_name'], $role_description);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create role: " . $stmt->error);
    }
    
    $role_id = $smp->insert_id;
    
    // If permissions are provided, assign them to the role
    if (!empty($data['permissions']) && is_array($data['permissions'])) {
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
    
    // Get the newly created role with its permissions
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
    
    http_response_code(201);
    echo json_encode([
        'status' => 'success',
        'message' => 'Role created successfully.',
        'data' => $role
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $smp->rollback();
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while creating the role: ' . $e->getMessage()
    ]);
}
