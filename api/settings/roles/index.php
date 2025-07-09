<?php
// api/settings/roles/index.php

global $smp;

// The Auth::check() in the router has already verified permissions.

require_once __DIR__ . '/../../../core/helpers/pagination.php';

try {
    // Get pagination, sorting, and search parameters from query string, with defaults
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'role_name';
    $sort_direction = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'asc';
    $search_term = isset($_GET['search']) ? $_GET['search'] : null;

    // Define the whitelist of columns that can be sorted
    $allowed_sort_columns = ['id', 'role_name', 'role_description'];

    // Define the whitelist of columns that can be searched
    $searchable_fields = ['role_name', 'role_description'];

    // Define the base query for fetching roles
    $base_query = "SELECT id, role_name, role_description FROM roles";

    // Use the pagination helper with sorting and searching
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

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Roles retrieved successfully.',
        'data' => $paginated_result['data'],
        'pagination' => $paginated_result['pagination']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching roles: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
