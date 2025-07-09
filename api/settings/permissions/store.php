<?php
// api/settings/permissions/store.php

global $smp;

// The Auth::check() in the router has already verified permissions.

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['permission_name']) || empty(trim($data['permission_name']))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Permission name is required.']);
    exit;
}

$permission_name = trim($data['permission_name']);
$description = isset($data['description']) ? trim($data['description']) : null;

// Check if permission already exists
$stmt = $smp->prepare("SELECT id FROM permissions WHERE permission_name = ?");
$stmt->bind_param("s", $permission_name);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'A permission with this name already exists.']);
    exit;
}

try {
    $stmt = $smp->prepare("INSERT INTO permissions (permission_name, permission_description) VALUES (?, ?)");
    $stmt->bind_param("ss", $permission_name, $description);
    $stmt->execute();

    $new_permission_id = $stmt->insert_id;

    $stmt = $smp->prepare("SELECT id, permission_name, permission_description as description FROM permissions WHERE id = ?");
    $stmt->bind_param("i", $new_permission_id);
    $stmt->execute();
    $new_permission = $stmt->get_result()->fetch_assoc();

    http_response_code(201); // Created
    echo json_encode([
        'status' => 'success',
        'message' => 'Permission created successfully.',
        'data' => $new_permission
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while creating the permission: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
