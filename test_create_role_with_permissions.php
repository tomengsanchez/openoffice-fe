<?php
/**
 * Test script for creating a role with permissions
 * 
 * Usage:
 *   php test_create_role_with_permissions.php
 * 
 * Make sure to update the JWT token and base URL as needed.
 */

// Configuration
$baseUrl = 'http://api.openoffice.local';
$jwtToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTIwMjQzNTcsImV4cCI6MTc1MjAyNzk1NywiaXNzIjoiaHR0cDovL2FwaS5vcGVub2ZmaWNlLmxvY2FsIiwiYXVkIjoiaHR0cDovL2FwaS5vcGVub2ZmaWNlLmxvY2FsIiwiZGF0YSI6eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIn19.grg9dKPGaLvKEafTFkxbmltFrgqQpn-pv8eZ0EJ0y5o'; // Replace with a valid JWT token

// Test data for creating a role with permissions
$roleData = [
    'role_name' => 'Open Office Manager 1',
    'role_description' => 'Manage the open office',
    'permissions' => [1, 4, 5, 6, 7, 8, 12] // Example permission IDs
];

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $baseUrl . '/settings/roles',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($roleData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $jwtToken
    ]
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch) . "\n";
} else {
    // Output the response
    echo "HTTP Status: $httpCode\n";
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Response: $response\n";
    }
}

// Close cURL resource
curl_close($ch);

echo "\nTest completed.\n";
?>
