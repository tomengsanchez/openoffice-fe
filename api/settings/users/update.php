<?php
// api/settings/users/update.php
global $smp, $params;

// The Auth class has already verified the user has the 'users:update' permission.

// --- Input Validation ---
if (!isset($params['id']) || !ctype_digit((string)$params['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'A valid user ID is required.']);
    exit;
}
$user_id = (int)$params['id'];

// Get the raw PUT data
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'No update data provided.']);
    exit;
}

// --- Check for uniqueness and existence ---
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

// --- Update User ---
try {
    $update_fields = [];
    $bind_types = '';
    $bind_values = [];

    if (!empty($data['username'])) {
        $update_fields[] = 'username = ?';
        $bind_types .= 's';
        $bind_values[] = $data['username'];
    }
    if (!empty($data['email'])) {
        $update_fields[] = 'email = ?';
        $bind_types .= 's';
        $bind_values[] = $data['email'];
    }
    if (!empty($data['password'])) {
        $update_fields[] = 'password = ?';
        $bind_types .= 's';
        $bind_values[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    if (isset($data['role_id'])) {
        $update_fields[] = 'role_id = ?';
        $bind_types .= 'i';
        $bind_values[] = $data['role_id'];
    }

    if (empty($update_fields)) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'No valid fields to update.']);
        exit;
    }

    $bind_types .= 'i';
    $bind_values[] = $user_id;

    $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $stmt = $smp->prepare($sql);
    $stmt->bind_param($bind_types, ...$bind_values);

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'User not found or no changes made.']);
            exit;
        }

        // Fetch the updated user to return in the response
        $stmt_select = $smp->prepare("SELECT u.id, u.username, u.email, u.role_id, r.role_name, u.created_at, u.updated_at FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
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
