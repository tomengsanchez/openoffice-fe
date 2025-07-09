<?php
// api/user/index.php
global $smp;

// The Auth::check() method in the router has already verified the JWT and user permissions.
// We can now safely get the user's details.

$logged_in_user = Auth::getLoggedInUser();
$available_links = Auth::getAvailableLinks();

if (!$logged_in_user) {
    // This case should ideally not be reached if Auth::check() is working correctly,
    // but it's good practice to have a fallback.
    http_response_code(401); // Unauthorized
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve user details from token.',
        'data' => null
    ]);
    exit;
}

// The primary data of this endpoint is the user profile itself.
$response = [
    'status' => 'success',
    'message' => 'User profile retrieved successfully.',
    'data' => $logged_in_user,
    'available_links' => $available_links
];

// Return the final JSON response
http_response_code(200);
echo json_encode($response);
?>
