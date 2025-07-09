<?php
// api/settings/roles/destroy.php
global $smp, $params;

// The Auth class has already verified the user has the 'roles:destroy' permission.

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
$stmt = $smp->prepare("SELECT id, role_name FROM roles WHERE id = ?");
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

// Prevent deletion of default roles
$default_roles = ['admin', 'manager', 'employee'];
if (in_array(strtolower($role['role_name']), $default_roles)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Default roles cannot be deleted.'
    ]);
    exit;
}

// Check if there are users assigned to this role
$stmt = $smp->prepare("SELECT COUNT(*) as user_count FROM users WHERE role_id = ?");
$stmt->bind_param('i', $role_id);
$stmt->execute();
$result = $stmt->get_result();
$user_count = $result->fetch_assoc()['user_count'];

if ($user_count > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Cannot delete role because there are users assigned to it.'
    ]);
    exit;
}

try {
    // Start transaction
    $smp->begin_transaction();
    
    // First, delete role_permissions entries
    $stmt = $smp->prepare("DELETE FROM role_permissions WHERE role_id = ?");
    $stmt->bind_param('i', $role_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to remove role permissions: " . $stmt->error);
    }
    
    // Then delete the role
    $stmt = $smp->prepare("DELETE FROM roles WHERE id = ?");
    $stmt->bind_param('i', $role_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete role: " . $stmt->error);
    }
    
    // Commit the transaction
    $smp->commit();
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Role deleted successfully.'
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $smp->rollback();
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while deleting the role: ' . $e->getMessage()
    ]);
}
