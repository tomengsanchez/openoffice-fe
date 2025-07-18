<?php
// api/settings/users/update.php
global $smp, $params;

// The Auth class has already verified the user has the 'users:update' permission.

// --- Input Validation ---
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'A valid user ID is required.']);
    exit;
}

// Get the raw PUT data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'No update data provided.']);
    exit;
}

// Validate required fields
if (empty($data['firstname']) || empty($data['lastname'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'First name and last name are required.']);
    exit;
}

// Check if username already exists for another user
if (!empty($data['username'])) {
    $stmt = $smp->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param('si', $data['username'], $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        exit;
    }
}

// Check if email already exists for another user
if (!empty($data['email'])) {
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
        exit;
    }
    $stmt = $smp->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param('si', $data['email'], $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
        exit;
    }
}

// Check if role_id is valid
if (isset($data['role_id'])) {
    $stmt = $smp->prepare("SELECT id FROM roles WHERE id = ?");
    $stmt->bind_param('i', $data['role_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Invalid role_id.']);
        exit;
    }
}

// Prepare the update query
$query = "UPDATE users SET ";
$params = [];
$types = '';
$updates = [];

// Add fields to update
$fields = [
    'username' => 's',
    'email' => 's',
    'firstname' => 's',
    'lastname' => 's',
    'role_id' => 'i'
];

foreach ($fields as $field => $type) {
    if (isset($data[$field]) && $data[$field] !== '') {
        $updates[] = "$field = ?";
        $params[] = $data[$field];
        $types .= $type;
    }
}

// Handle password separately
if (!empty($data['password'])) {
    $updates[] = "password = ?";
    $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    $types .= 's';
}

if (empty($updates)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'No valid fields to update.']);
    exit;
}

// Add user_id to the params for WHERE clause
$params[] = $user_id;
$types .= 'i';

// Build the final query
$query .= implode(', ', $updates) . " WHERE id = ?";

try {
    $stmt = $smp->prepare($query);
    
    // Bind parameters
    $bindParams = [];
    $bindParams[] = $types;
    
    // Create references for bind_param
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'User not found or no changes made.']);
            exit;
        }

        // Fetch the updated user to return in the response
        $stmt_select = $smp->prepare("SELECT u.id, u.username, u.email, u.firstname, u.lastname, u.role_id, r.role_name, u.created_at, u.updated_at 
                                    FROM users u 
                                    JOIN roles r ON u.role_id = r.id 
                                    WHERE u.id = ?");
        $stmt_select->bind_param('i', $user_id);
        $stmt_select->execute();
        $updated_user = $stmt_select->get_result()->fetch_assoc();

        http_response_code(200); // OK
        echo json_encode([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => $updated_user
        ]);
    } else {
        throw new Exception("Failed to update user.");
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while updating the user: ' . $e->getMessage()
    ]);
}
