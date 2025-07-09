<?php
// api/settings/users/store.php
global $smp;

// The Auth class has already verified the user has the 'users:store' permission.

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// --- Input Validation ---
if (empty($data['username']) || empty($data['email']) || empty($data['password']) || !isset($data['role_id']) || 
    empty($data['firstname']) || empty($data['lastname'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields: username, email, password, firstname, lastname and role_id are required.'
    ]);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

// --- Check for uniqueness and existence ---
// Check if username already exists
$stmt = $smp->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param('s', $data['username']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
    exit;
}

// Check if email already exists
$stmt = $smp->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $data['email']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
    exit;
}

// Check if role_id is valid
$stmt = $smp->prepare("SELECT id FROM roles WHERE id = ?");
$stmt->bind_param('i', $data['role_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid role_id.']);
    exit;
}

// --- Create User ---
try {
    // Hash the password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Set default values if not provided
    $firstname = trim($data['firstname']);
    $lastname = trim($data['lastname']);

    // Insert the new user
    $stmt = $smp->prepare("INSERT INTO users (username, email, password, firstname, lastname, role_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssi', 
        $data['username'], 
        $data['email'], 
        $hashedPassword, 
        $firstname,
        $lastname,
        $data['role_id']
    );

    // Execute the statement
    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;

        // Fetch the newly created user to return in the response
        $stmt_select = $smp->prepare("SELECT u.id, u.username, u.email, u.firstname, u.lastname, u.role_id, r.role_name, u.created_at, u.updated_at FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmt_select->bind_param('i', $new_user_id);
        $stmt_select->execute();
        $new_user = $stmt_select->get_result()->fetch_assoc();

        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => $new_user
        ]);
    } else {
        throw new Exception("Failed to create user.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while creating the user: ' . $e->getMessage()
    ]);
}
