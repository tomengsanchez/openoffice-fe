<?php
// api/auth/login.php
global $smp;



// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'error',
        'message' => 'Username and password are required.',
        'data' => null
    ]);
    exit;
}

$username = isset($data['username']) ? htmlspecialchars(strip_tags($data['username'])) : null;
$password = $data['password'];

// Find the user by username
$stmt = $smp->prepare("SELECT users.id, users.username, users.password, users.role_id, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE users.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid credentials.',
        'data' => null
    ]);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify the password
if (password_verify($password, $user['password'])) {
    // Password is correct, generate JWT
    $token = create_jwt($user['id'], $user['username'], $user['role_name']);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful.',
        'data' => [
            'token' => $token
        ]
    ]);
} else {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid credentials.',
        'data' => null
    ]);
}
?>
