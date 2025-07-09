<?php
/**
 * User Seeder
 * 
 * This script generates 1,000 random users with roles 1, 2, or 3.
 * Passwords are hashed using password_hash() with PASSWORD_DEFAULT.
 * 
 * Usage: php seeders/UserSeeder.php
 */

// Database configuration - Update these values to match your database setup
const DB_HOST = 'localhost';
const DB_USER = 'root';  // Your database username
const DB_PASS = '';      // Your database password
const DB_NAME = 'op_api_db'; // Your database name

// First names for random generation
$firstNames = [
    'John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Robert', 'Jennifer', 'William', 'Lisa',
    'James', 'Michelle', 'Richard', 'Laura', 'Joseph', 'Amy', 'Thomas', 'Kimberly', 'Daniel', 'Jessica',
    'Matthew', 'Rebecca', 'Christopher', 'Amanda', 'Anthony', 'Stephanie', 'Charles', 'Melissa', 'Mark', 'Nicole'
];

// Last names for random generation
$lastNames = [
    'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilson',
    'Martinez', 'Anderson', 'Taylor', 'Thomas', 'Hernandez', 'Moore', 'Martin', 'Jackson', 'Thompson', 'White'
];

// Departments for random assignment
$departments = [
    'Engineering', 'Marketing', 'Sales', 'HR', 'Finance', 'IT', 'Operations', 'Customer Support', 'Product', 'Design'
];

// Function to generate a random string
function randomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Function to generate a random date within a range
function randomDate($startDate, $endDate) {
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    $randomDate = date('Y-m-d H:i:s', rand($start, $end));
    return $randomDate;
}

try {
    // Create database connection
    $smp = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($smp->connect_error) {
        die("Connection failed: " . $smp->connect_error);
    }
    
    // Disable foreign key checks for faster insertion
    $smp->query("SET FOREIGN_KEY_CHECKS = 0");
    $smp->query("SET NAMES utf8mb4");
    $smp->query("SET time_zone = '+00:00'");
    
    // Truncate users table (be careful with this in production!)
    // $smp->query("TRUNCATE TABLE users");
    
    // Prepare the insert statement with firstname and lastname
    $stmt = $smp->prepare("INSERT INTO users (username, password, email, firstname, lastname, role_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    // Start transaction for better performance
    $smp->begin_transaction();
    
    $batchSize = 10;
    $totalUsers = 10000;
    $inserted = 0;
    $startTime = microtime(true);
    
    echo "Starting to seed $totalUsers users...\n";
    
    for ($i = 1; $i <= $totalUsers; $i++) {
        // Generate user data
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        // Generate a unique username with first initial, last name, and random string
        $randomStr = bin2hex(random_bytes(3)); // Add 6 random hex characters
        $username = strtolower($firstName[0] . $lastName . $randomStr);
        // Generate email with the same random string for uniqueness
        $email = strtolower($firstName . '.' . $lastName . $randomStr . '@example.com');
        $password = password_hash('password123', PASSWORD_DEFAULT); // Default password for all seeded users
        $roleId = rand(1, 3); // Random role between 1 and 3
        
        // Bind parameters and execute
        $stmt->bind_param('sssssi', $username, $password, $email, $firstName, $lastName, $roleId);
        
        if (!$stmt->execute()) {
            echo "Error inserting user $i: " . $stmt->error . "\n";
            continue;
        }
        
        $inserted++;
        
        // Show progress
        if ($i % $batchSize === 0) {
            $progress = ($i / $totalUsers) * 100;
            echo sprintf("Progress: %.1f%% - Inserted %d users\n", $progress, $i);
            
            // Commit the current batch
            $smp->commit();
            
            // Start a new transaction for the next batch
            $smp->begin_transaction();
        }
    }
    
    // Commit any remaining inserts
    $smp->commit();
    
    // Re-enable foreign key checks
    $smp->query("SET FOREIGN_KEY_CHECKS = 1");
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    echo "\nSeeding completed successfully!\n";
    echo "Total users inserted: $inserted\n";
    echo "Execution time: $executionTime seconds\n";
    
    // Close the connection
    $smp->close();
    
} catch (Exception $e) {
    // Rollback the transaction on error
    if (isset($smp)) {
        $smp->rollback();
    }
    
    echo "An error occurred: " . $e->getMessage() . "\n";
    exit(1);
}
