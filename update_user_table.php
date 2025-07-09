<?php
// Load configuration
require_once __DIR__ . '/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = $config_db['host'];
$dbname = $config_db['database'];
$username = $config_db['user'];
$password = $config_db['password'];
$port = $config_db['port'] ?? 3306;

// DSN for PDO
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    echo "Connecting to database...\n";
    
    // Create connection
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Connected successfully. Checking users table...\n";
    
    // Check if users table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    
    if (!$tableExists) {
        die("Error: The 'users' table does not exist in the database.\n");
    }
    
    // Start transaction
    $pdo->beginTransaction();
    echo "Transaction started. Adding columns if they don't exist...\n";

    // Check if columns already exist
    $stmt = $pdo->query("SHOW COLUMNS FROM `users`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnsToAdd = [];
    if (!in_array('firstname', $columns)) {
        $columnsToAdd[] = "ADD COLUMN `firstname` VARCHAR(100) NULL AFTER `email`";
    }
    if (!in_array('lastname', $columns)) {
        $columnsToAdd[] = "ADD COLUMN `lastname` VARCHAR(100) NULL AFTER `firstname`";
    }
    
    if (!empty($columnsToAdd)) {
        $alterSql = "ALTER TABLE `users` " . implode(', ', $columnsToAdd);
        $pdo->exec($alterSql);
        echo "Added columns: " . implode(', ', array_map(function($col) {
            return explode(' ', $col)[2]; // Extract column name from SQL
        }, $columnsToAdd)) . "\n";
    } else {
        echo "All columns already exist. No changes needed.\n";
    }

    // Update existing users with default names
    $stmt = $pdo->prepare("UPDATE `users` SET `firstname` = :firstname, `lastname` = :lastname WHERE `username` = :username");
    
    $users = [
        ['username' => 'admin', 'firstname' => 'System', 'lastname' => 'Administrator'],
        ['username' => 'manager', 'firstname' => 'Department', 'lastname' => 'Manager'],
        ['username' => 'employee', 'firstname' => 'Regular', 'lastname' => 'Employee']
    ];

    echo "Updating user records...\n";
    foreach ($users as $user) {
        $stmt->execute($user);
        echo "Updated user: {$user['username']} - {$user['firstname']} {$user['lastname']}\n";
    }

    // Commit transaction
    $pdo->commit();
    
    echo "\nSuccessfully updated users table with firstname and lastname fields.\n";
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "Transaction rolled back due to error.\n";
    }
    die("\nError: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("\nError: " . $e->getMessage() . "\n");
} finally {
    // Close connection
    $pdo = null;
    echo "Database connection closed.\n";
}
