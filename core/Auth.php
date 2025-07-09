<?php
// core/Auth.php

class Auth {

    private static $smp;
    private static $user_payload = null; // Holds the decoded JWT payload for the current request

    private static function init()
    {
        if (self::$smp === null) {
            global $smp;
            self::$smp = $smp;
        }
    }

    /**
     * Checks for a valid JWT and verifies user permission for a route.
     * Halts execution with an error if authentication or authorization fails.
     *
     * @param string|null $permission_name The name of the permission required.
     */
    public static function check($permission_name) {
        self::init();
        $public_routes = ['auth:login', 'auth:register', 'auth:forgotPassword', 'auth:resetPassword', 'index'];

        // Allow public routes without a token
        if (in_array($permission_name, $public_routes)) {
            return;
        }

        // For all other routes, a valid JWT is required.
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth_header || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'Authorization token not found.']);
            exit;
        }

        $token = $matches[1];
        $decoded = validate_jwt($token);

        if (!$decoded) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'Invalid or expired token.']);
            exit;
        }

        // Store user payload for use in other Auth methods during this request
        self::$user_payload = $decoded->data;

        // If the route requires no specific permission, being logged in is enough.
        if (empty($permission_name)) {
            return;
        }

        // Get role_id from the role_name in the token payload
        $role_id = self::getRoleIdByName(self::$user_payload->role);

        if ($role_id === null) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'User role from token is invalid.']);
            exit;
        }

        // Check if the role has the required permission
        if (!self::hasPermission($role_id, $permission_name)) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'You do not have permission to access this resource.']);
            exit;
        }
    }

    /**
     * Checks if the current user can access a route without halting execution.
     *
     * @param string|null $permission_name The name of the permission to check.
     * @return bool True if the user has permission, false otherwise.
     */
    public static function can($permission_name) {
        if (is_array($permission_name)) {
            error_log('Auth::can() received an array for a permission check.');
            return false;
        }

        self::init();
        $public_routes = ['auth:login', 'auth:register', 'auth:forgotPassword', 'auth:resetPassword', 'index'];

        if (in_array($permission_name, $public_routes)) {
            return true;
        }

        // For non-public routes, a valid user payload must exist (from Auth::check).
        if (self::$user_payload === null) {
            return false;
        }

        // If no specific permission is needed, and the user is authenticated, grant access.
        if (empty($permission_name)) {
            return true;
        }

        $role_id = self::getRoleIdByName(self::$user_payload->role);
        if ($role_id === null) {
            return false;
        }

        return self::hasPermission($role_id, $permission_name);
    }

    /**
     * Queries the database to see if a role has a specific permission.
     *
     * @param int $role_id The ID of the user's role.
     * @param string $permission_name The name of the permission.
     * @return bool True if the role has the permission, false otherwise.
     */
    private static function hasPermission($role_id, $permission_name) {
        self::init();
        $sql = "SELECT COUNT(*)
                FROM `role_permissions` rp
                JOIN `permissions` p ON rp.permission_id = p.id
                WHERE rp.role_id = ? AND p.permission_name = ?";

        $stmt = self::$smp->prepare($sql);
        if ($stmt === false) {
            error_log('MySQL prepare error in Auth::hasPermission(): ' . self::$smp->error);
            return false;
        }
        $stmt->bind_param('is', $role_id, $permission_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }

    /**
     * Generates a list of navigation links available to the current user.
     *
     * @return array A list of link objects accessible by the user.
     */
    public static function getAvailableLinks() {
        self::init();

        $routes_json_path = __DIR__ . '/../routes.json';
        if (!file_exists($routes_json_path)) {
            error_log('Auth::getAvailableLinks() could not find routes.json.');
            return [];
        }

        $routes_json = file_get_contents($routes_json_path);
        $all_routes = json_decode($routes_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Auth::getAvailableLinks() failed to decode routes.json: ' . json_last_error_msg());
            return [];
        }

        $available_links = [];

        foreach ($all_routes as $route_group) {
            if (!is_array($route_group)) continue;

            foreach ($route_group as $route) {
                if (!is_array($route) || !isset($route['method']) || !isset($route['label'])) {
                    continue;
                }

                if ($route['method'] !== 'GET' || empty($route['label'])) {
                    continue;
                }

                $permission = $route['permission'] ?? null;
                // Always include the route but add permission information
                $permission_id = null;
                if ($permission) {
                    // Get permission ID from database
                    $stmt = self::$smp->prepare("SELECT id FROM permissions WHERE permission_name = ?");
                    $stmt->bind_param('s', $permission);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $permission_id = (int)$row['id'];
                    }
                    $stmt->close();
                }

                $available_links[] = [
                    'method' => $route['method'],
                    'url' => $route['url'],
                    'label' => $route['label'],
                    'permission_required' => $permission,
                    'permission_id' => $permission_id,
                    'has_permission' => self::can($permission)
                ];
            }
        }

        return $available_links;
    }

    /**
     * Retrieves the details of the currently authenticated user from the JWT payload.
     *
     * @return array|null An associative array of user details or null if not authenticated.
     */
    public static function getLoggedInUser() {
        self::init();

        if (self::$user_payload === null) {
            return null;
        }

        $user_id = self::$user_payload->user_id;

        $stmt = self::$smp->prepare("SELECT email FROM users WHERE id = ?");
        if ($stmt === false) {
            error_log('MySQL prepare error in Auth::getLoggedInUser(): ' . self::$smp->error);
            return null;
        }

        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user === null) {
            return null; // Should not happen if token is valid
        }

        return [
            'user_id' => $user_id,
            'username' => self::$user_payload->username,
            'email' => $user['email'],
            'role' => self::$user_payload->role
        ];
    }

    /**
     * Retrieves the role ID for a given role name.
     *
     * @param string $role_name The name of the role.
     * @return int|null The role ID or null if not found.
     */
    private static function getRoleIdByName($role_name) {
        self::init();
        $stmt = self::$smp->prepare("SELECT id FROM roles WHERE role_name = ?");
        $stmt->bind_param('s', $role_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'] ?? null;
    }
}

