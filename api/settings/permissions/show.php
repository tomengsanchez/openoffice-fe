<?php
// api/settings/permissions/show.php

global $smp;

// The Auth::check() in the router has already verified permissions.

// Get the permission ID from the URL parameters
$permission_id = $_GET['id'] ?? '';

// Validate the permission ID
if (!is_numeric($permission_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A valid numeric Permission ID is required.']);
    exit;
}

$permission_id = intval($permission_id);

try {
    // Prepare and execute the query to fetch the permission
    $stmt = $smp->prepare("SELECT id, permission_name, permission_description as description FROM permissions WHERE id = ?");
    $stmt->bind_param("i", $permission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Permission not found.'
        ]);
        exit;
    }
    
    $permission = $result->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Permission retrieved successfully.',
        'data' => $permission
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while retrieving the permission.',
        'error' => $e->getMessage()
    ]);
}
