<?php
// api/settings/roles/show.php
global $smp, $params;

// The Auth class has already verified the user has the 'roles:show' permission.

// Get role ID from URL parameters
$role_id = (int)($_GET['id'] ?? 0);

if ($role_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid role ID.'
    ]);
    exit;
}

try {
    // Get role details
    $stmt = $smp->prepare("
        SELECT r.*, 
               GROUP_CONCAT(p.id) as permission_ids,
               GROUP_CONCAT(p.permission_name) as permission_names
        FROM roles r
        LEFT JOIN role_permissions rp ON r.id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.id
        WHERE r.id = ?
        GROUP BY r.id
    ");
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
    
    $role = $result->fetch_assoc();
    
    // Format the response
    $response = [
        'id' => (int)$role['id'],
        'role_name' => $role['role_name'],
        'role_description' => $role['role_description'] ?? null,
        'created_at' => $role['created_at'] ?? null,
        'updated_at' => $role['updated_at'] ?? null,
        'permissions' => []
    ];
    
    // Add permissions if they exist
    if (!empty($role['permission_ids'])) {
        $permission_ids = explode(',', $role['permission_ids']);
        $permission_names = explode(',', $role['permission_names']);
        
        $response['permissions'] = array_map(function($id, $name) {
            return [
                'id' => (int)$id,
                'name' => $name
            ];
        }, $permission_ids, $permission_names);
    }
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $response
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching the role: ' . $e->getMessage()
    ]);
}
