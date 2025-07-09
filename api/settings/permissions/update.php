<?php
// api/settings/permissions/update.php

global $smp;

// The Auth::check() in the router has already verified permissions.

$data = json_decode(file_get_contents('php://input'), true);

// Get ID from URL parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A valid Permission ID is required in the URL.']);
    exit;
}

if (!isset($data['permission_name']) || empty(trim($data['permission_name']))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Permission name is required.']);
    exit;
}

$id = intval($_GET['id']);
$permission_name = trim($data['permission_name']);
$description = isset($data['description']) ? trim($data['description']) : null;

// Check if another permission with the same name exists (excluding the current one)
$stmt = $smp->prepare("SELECT id FROM permissions WHERE permission_name = ? AND id != ?");
$stmt->bind_param("si", $permission_name, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'Another permission with this name already exists.']);
    exit;
}

try {
    $stmt = $smp->prepare("UPDATE permissions SET permission_name = ?, permission_description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $permission_name, $description, $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Permission not found or no changes were made.']);
        exit;
    }

    $stmt = $smp->prepare("SELECT id, permission_name, permission_description as description FROM permissions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $updated_permission = $stmt->get_result()->fetch_assoc();

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Permission updated successfully.',
        'data' => $updated_permission
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating the permission: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
