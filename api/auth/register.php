<?php
// api/auth/register.php
global $smp;

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Username, email, and password are required.']);
    exit;
}

$username = isset($data['username']) ? htmlspecialchars(strip_tags($data['username'])) : null;
$email = isset($data['email']) ? htmlspecialchars(strip_tags($data['email'])) : null;
$password = $data['password'];

// More validation
if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields must be filled.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format.']);
    exit;
}

// Check if user already exists
$stmt = $smp->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['error' => 'Username or email already exists.']);
    $stmt->close();
    exit;
}
$stmt->close();

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Get the default 'employee' role ID
$stmt = $smp->prepare("SELECT id FROM roles WHERE role_name = 'employee'");
$stmt->execute();
$result = $stmt->get_result();
$role = $result->fetch_assoc();
$role_id = $role['id'];
$stmt->close();

if (!$role_id) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Default user role not found.']);
    exit;
}

// Insert the new user
$stmt = $smp->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(['message' => 'User registered successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred during registration.']);
}

$stmt->close();
?>
