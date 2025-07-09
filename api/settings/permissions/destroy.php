<?php
// api/settings/permissions/destroy.php

global $smp;

// The Auth::check() in the router has already verified permissions.

// Get ID from URL parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A valid Permission ID is required in the URL.']);
    exit;
}

$id = intval($_GET['id']);

// Start a transaction to ensure atomicity
$smp->begin_transaction();

try {
    // First, delete any associations in the role_permissions table
    $stmt1 = $smp->prepare("DELETE FROM role_permissions WHERE permission_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Then, delete the permission itself
    $stmt2 = $smp->prepare("DELETE FROM permissions WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    if ($stmt2->affected_rows === 0) {
        // If no rows were deleted from the permissions table, the permission didn't exist.
        $smp->rollback();
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Permission not found.']);
        exit;
    }

    // If all went well, commit the transaction
    $smp->commit();

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Permission and its associations deleted successfully.',
        'data' => ['id' => $id]
    ]);

} catch (Exception $e) {
    // If any error occurred, roll back the transaction
    $smp->rollback();

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while deleting the permission: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
