<?php
// api/settings/users/show.php
global $smp, $params;

// The Auth class has already verified the user has the 'users:show' permission.

// --- Input Validation ---
if (!isset($params['id']) || !ctype_digit((string)$params['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'A valid user ID is required.']);
    exit;
}

$user_id = (int)$params['id'];

// --- Fetch User ---
try {
    $stmt = $smp->prepare("SELECT u.id, u.username, u.email, u.role_id, r.role_name, u.created_at, u.updated_at 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }

    $user = $result->fetch_assoc();

    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'message' => 'User retrieved successfully.',
        'data' => $user
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching the user: ' . $e->getMessage()
    ]);
}
