<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Creates a new JWT token.
 *
 * @param int $user_id The ID of the user.
 * @param string $username The username.
 * @param string $role The user's role.
 * @return string The generated JWT token.
 */
function create_jwt($user_id, $username, $role) {
    global $config_jwt;

    $issued_at = time();
    $expiration_time = $issued_at + $config_jwt['expiration_time'];

    $payload = array(
        'iat' => $issued_at,
        'exp' => $expiration_time,
        'iss' => $config_jwt['issuer'],
        'aud' => $config_jwt['audience'],
        'data' => array(
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role
        )
    );

    return JWT::encode($payload, $config_jwt['secret_key'], $config_jwt['algorithm']);
}

/**
 * Validates a JWT token and returns the decoded payload.
 *
 * @param string $token The JWT token to validate.
 * @return object|null The decoded payload if the token is valid, otherwise null.
 */
function validate_jwt($token) {
    global $config_jwt;

    try {
        $decoded = JWT::decode($token, new Key($config_jwt['secret_key'], $config_jwt['algorithm']));
        return $decoded;
    } catch (Exception $e) {
        return null;
    }
}
?>
