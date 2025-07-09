<?php
require_once __DIR__ . '/Auth.php';

class Router {
    private $routes = [];

    public function __construct() {
        $this->loadRoutes();
    }

    private function loadRoutes() {
        $json = file_get_contents(__DIR__ . '/../routes.json');
        $all_routes = json_decode($json, true);
        // Combine all route groups into a single array, with better error handling
        if (is_array($all_routes)) {
            foreach ($all_routes as $route_group) {
                if (is_array($route_group)) {
                    $this->routes = array_merge($this->routes, $route_group);
                }
            }
        }
    }

    public function dispatch() {
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $request_method = $_SERVER['REQUEST_METHOD'];

        $base_path = dirname($_SERVER['SCRIPT_NAME']);

        if ($base_path !== '/' && strpos($request_uri, $base_path) === 0) {
            $route_path = substr($request_uri, strlen($base_path));
        } else {
            $route_path = $request_uri;
        }

        // Trim trailing slash if it's not the root path
        if (strlen($route_path) > 1) {
            $route_path = rtrim($route_path, '/');
        }

        if (empty($route_path)) {
            $route_path = '/';
        }

        // Add debugging information to the error log
        $log_message = "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $log_message .= "Request Method: " . $request_method . "\n";
        $log_message .= "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
        $log_message .= "Calculated Route Path: " . $route_path . "\n";
        $log_message .= "Routes Loaded: " . (empty($this->routes) ? 'No routes loaded or routes.json is invalid.' : 'Routes loaded successfully.') . "\n\n";
        file_put_contents(__DIR__ . '/../error.log', $log_message, FILE_APPEND);

        foreach ($this->routes as $route) {
            // Convert route URL to a regex pattern
            $pattern = preg_replace('/\//', '\/', $route['url']); // Escape forward slashes
            $pattern = preg_replace('/:([^\/]+)/', '(?P<$1>[^\/]+)', $pattern); // Convert :param to named capture group
            $pattern = "/^" . $pattern . "$/"; // Add start and end anchors
            
            // Check if the current request matches the route pattern
            if (preg_match($pattern, $route_path, $matches) && $route['method'] === $request_method) {
                // The new Auth::check handles public routes and exits on failure
                Auth::check($route['permission'] ?? null);
                
                // Set URL parameters in $_GET
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $_GET[$key] = $value;
                    }
                }

                $handler_path = __DIR__ . '/../api/' . $route['handler'] . '.php';
                if (file_exists($handler_path)) {
                    require_once $handler_path;
                    return; // Stop processing once a match is found
                }
            }
        }

        // Handle 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found', 'requested_path' => $route_path]);
    }
}
?>
