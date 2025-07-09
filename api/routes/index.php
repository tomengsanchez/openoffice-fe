<?php
// api/routes/index.php
global $smp;

// The Auth class has already verified the user has the 'routes:index' permission.

try {
    // The getAvailableLinks method already filters routes based on the logged-in user's permissions.
    $available_routes = Auth::getAvailableLinks();

    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'message' => 'Available routes retrieved successfully.',
        'data' => $available_routes
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching routes: ' . $e->getMessage()
    ]);
}
