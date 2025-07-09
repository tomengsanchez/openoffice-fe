<?php
// api/settings/users/destroy.php
global $smp, $params;

// The Auth class has already verified the user has the 'users:destroy' permission.

// --- Input Validation ---
if (!isset($params['id']) || !ctype_digit((string)$params['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'A valid user ID is required.']);
    exit;
}

$user_id = (int)$params['id'];

// --- Safeguard: Prevent deleting the primary admin user ---
if ($user_id === 1) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'The primary admin user cannot be deleted.']);
    exit;
}

// --- Delete User ---
try {
    $stmt = $smp->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        } else {
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
        }
    } else {
        throw new Exception("Failed to delete user.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while deleting the user: ' . $e->getMessage()
    ]);
}
