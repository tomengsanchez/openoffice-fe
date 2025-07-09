<?php
// core/helpers/pagination.php

/**
 * A reusable pagination function for the API.
 *
 * @param mysqli $smp The database connection object.
 * @param string $base_query The base SQL query (SELECT ... FROM ... WHERE ...).
 * @param array $params An array of parameters for the prepared statement.
 * @param int $page The current page number, defaults to 1.
 * @param int $per_page The number of items per page, defaults to 15.
 * @return array An array containing 'data' and 'pagination' info.
 */
function paginate(mysqli $smp, string $base_query, array $params = [], int $page = 1, int $per_page = 15, string $sort_by = 'id', string $sort_direction = 'asc', array $allowed_sort_columns = ['id'], string $search_term = null, array $searchable_fields = []): array
{
    // --- Sanitize and validate inputs ---
    $page = max(1, $page);
    $per_page = max(1, $per_page);
    $offset = ($page - 1) * $per_page;

    // Security: Whitelist and sanitize the sort column
    if (!in_array($sort_by, $allowed_sort_columns)) {
        $sort_by = $allowed_sort_columns[0];
    }
    $sort_direction = strtoupper($sort_direction) === 'DESC' ? 'DESC' : 'ASC';
    // Handle qualified column names for sorting (e.g., `users`.`username`)
    $safe_sort_by = (strpos($sort_by, '.') === false) ? "`{$sort_by}`" : $sort_by;

    // --- Handle Search --- 
    $query_params = $params;
    if ($search_term && !empty($searchable_fields)) {
        $search_like_term = "%{$search_term}%";
        $search_conditions = [];
        $search_params = [];
        foreach ($searchable_fields as $field) {
            // Security check for valid column name format
            if (preg_match('/^[a-zA-Z0-9_.]+$/', $field)) {
                // Handle qualified column names for searching
                $safe_field = (strpos($field, '.') === false) ? "`{$field}`" : $field;
                $search_conditions[] = "{$safe_field} LIKE ?";
                $search_params[] = $search_like_term;
            }
        }
        if (!empty($search_conditions)) {
            $where_clause = "(" . implode(' OR ', $search_conditions) . ")";
            $base_query .= (stripos($base_query, 'WHERE') === false ? ' WHERE ' : ' AND ') . $where_clause;
            $query_params = array_merge($query_params, $search_params);
        }
    }

    // --- Get total record count ---
    // The 's' modifier allows '.' to match newlines, fixing issues with multi-line queries.
    $count_query = preg_replace('/SELECT\s.*\sFROM/is', 'SELECT COUNT(*) as total FROM', $base_query, 1);
    $stmt_count = $smp->prepare($count_query);
    if (!empty($query_params)) {
        $stmt_count->bind_param(str_repeat('s', count($query_params)), ...$query_params);
    }
    $stmt_count->execute();
    $total_records = $stmt_count->get_result()->fetch_assoc()['total'];
    $total_pages = (int)ceil($total_records / $per_page);

    // --- Get the data for the current page ---
    $data_query = $base_query . " ORDER BY {$safe_sort_by} {$sort_direction} LIMIT ? OFFSET ?";
    $stmt_data = $smp->prepare($data_query);
    $all_data_params = array_merge($query_params, [$per_page, $offset]);
    $types = str_repeat('s', count($query_params)) . 'ii';
    $stmt_data->bind_param($types, ...$all_data_params);
    $stmt_data->execute();
    $data = $stmt_data->get_result()->fetch_all(MYSQLI_ASSOC);

    // --- Build pagination metadata ---
    $base_url = strtok($_SERVER["REQUEST_URI"], '?');
    $http_query = http_build_query([
        'per_page' => $per_page,
        'sort_by' => $sort_by,
        'sort_direction' => strtolower($sort_direction),
        'search' => $search_term
    ]);
    $next_page = ($page < $total_pages) ? $page + 1 : null;
    $prev_page = ($page > 1) ? $page - 1 : null;

    $pagination = [
        'total_records' => (int)$total_records,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'per_page' => $per_page,
        'sort_by' => $sort_by,
        'sort_direction' => strtolower($sort_direction),
        'search_term' => $search_term,
        'next_page_url' => $next_page ? "{$base_url}?page={$next_page}&{$http_query}" : null,
        'prev_page_url' => $prev_page ? "{$base_url}?page={$prev_page}&{$http_query}" : null,
    ];

    return ['data' => $data, 'pagination' => $pagination];
}
?>
