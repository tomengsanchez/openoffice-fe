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

    // Start transaction
    $pdo->beginTransaction();

    // Define the required permissions with their descriptions
    $permissions = [
        'dashboard:index' => 'Access to the dashboard interface',
        'permissions:create' => 'Ability to create new permissions',
        'permissions:edit' => 'Ability to modify existing permissions',
        'permissions:delete' => 'Ability to remove permissions',
        'roles:index' => 'Ability to view roles list',
        'roles:create' => 'Ability to create new roles',
        'roles:edit' => 'Ability to modify existing roles',
        'roles:delete' => 'Ability to remove roles',
        'users:index' => 'Ability to view users list',
        'users:create' => 'Ability to create new users',
        'users:edit' => 'Ability to modify existing users',
        'users:delete' => 'Ability to remove users'
    ];

    // Add or update permissions
    $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, permission_description) 
                          VALUES (:name, :description)
                          ON DUPLICATE KEY UPDATE permission_description = :description");

    $permissionIds = [];
    foreach ($permissions as $name => $description) {
        $stmt->execute([':name' => $name, ':description' => $description]);
        $permissionIds[$name] = $pdo->lastInsertId() ?: 
            $pdo->query("SELECT id FROM permissions WHERE permission_name = '" . $name . "'")->fetchColumn();
    }

    // Get admin role ID
    $adminRoleId = $pdo->query("SELECT id FROM roles WHERE role_name = 'admin' LIMIT 1")->fetchColumn();
    
    if (!$adminRoleId) {
        throw new Exception("Admin role not found");
    }

    // Assign all permissions to admin role if not already assigned
    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) 
                          VALUES (:role_id, :permission_id)");
    
    foreach ($permissionIds as $permissionId) {
        $stmt->execute([':role_id' => $adminRoleId, ':permission_id' => $permissionId]);
    }

    // Commit transaction
    $pdo->commit();
    
    echo "Successfully synchronized permissions and assigned to admin role.\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage() . "\n");
}

// Close connection
$pdo = null;
