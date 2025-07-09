<?php
// Database configuration
$host = 'localhost';
$dbname = 'op_api_db';
$username = 'root';
$password = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add the new permission
    $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, permission_description) 
                          VALUES (:name, :description)
                          ON DUPLICATE KEY UPDATE permission_description = :description");
    
    $stmt->execute([
        ':name' => 'roles:show',
        ':description' => 'Ability to view a single role with its permissions'
    ]);

    // Assign to admin role
    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id)
                          SELECT r.id, p.id 
                          FROM roles r, permissions p 
                          WHERE r.role_name = 'admin' AND p.permission_name = 'roles:show'");
    
    $stmt->execute();

    echo "Successfully added 'roles:show' permission and assigned it to admin role.\n";

} catch(PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
