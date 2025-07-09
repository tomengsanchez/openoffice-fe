<?php
// reset_admin_password.php
// IMPORTANT: DELETE THIS FILE AFTER USE!

// Include the database configuration which creates the global $smp connection
require_once __DIR__ . '/config.php';

global $smp;

// The new password
$new_password = 'changeme';

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// The username to update
$username = 'admin';

// Prepare and execute the update statement
$stmt = $smp->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param('ss', $hashed_password, $username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Admin password has been successfully reset to 'changeme'.";
    } else {
        echo "Could not find an admin user with that username. No changes were made.";
    }
} else {
    echo "An error occurred while trying to reset the password: " . $stmt->error;
}

$stmt->close();
$smp->close();

echo "<br><br><b>IMPORTANT: Please delete this file (reset_admin_password.php) from your server immediately!</b>";
