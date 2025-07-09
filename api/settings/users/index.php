<?php
// api/settings/users/index.php
global $smp;

// The Auth class has already verified the user has the 'users:index' permission.
require_once __DIR__ . '/../../../core/helpers/pagination.php';

try {
    // Get pagination, sorting, and search parameters from query string, with defaults
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'u.id';
    $sort_direction = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'asc';
    $search_term = isset($_GET['search']) ? $_GET['search'] : null;

    // Define the whitelist of columns that can be sorted
    $allowed_sort_columns = ['u.id', 'u.username', 'u.email', 'r.role_name'];

    // Define the whitelist of columns that can be searched
    $searchable_fields = ['u.username', 'u.email', 'r.role_name'];

    // Define the base query for fetching users with their roles
    $base_query = "SELECT u.id, u.username, u.email, u.firstname, u.lastname, r.role_name 
                   FROM users u 
                   JOIN roles r ON u.role_id = r.id";

    // Use the pagination helper
    $paginated_result = paginate(
        $smp,
        $base_query,
        [],
        $page,
        $per_page,
        $sort_by,
        $sort_direction,
        $allowed_sort_columns,
        $search_term,
        $searchable_fields
    );

    // Get additional details for the standardized response
    $logged_in_user = Auth::getLoggedInUser();
    $available_links = Auth::getAvailableLinks();

    // Wrap the user list in the standardized response format
    $response = [
        'status' => 'success',
        'message' => 'Users retrieved successfully.',
        'data' => $paginated_result['data'],
        'pagination' => $paginated_result['pagination'],
        'logged_in_user' => $logged_in_user,
        'available_links' => $available_links
    ];

    // Return the final JSON response
    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching users: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
