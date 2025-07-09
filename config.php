<?php
// Set the default timezone to Manila
date_default_timezone_set('Asia/Manila');
// --- Autoloader ---
// Include the Composer autoloader to manage dependencies.
require_once __DIR__ . '/vendor/autoload.php'; 
/**
 * Config for OpenOffice 
 */
$app_config = array(
    'app_name' => 'OpenOffice',
    'app_version' => '1.0.0',
    'app_description' => 'OpenOffice API',
    'app_author' => 'Michael Sanchez',
    'app_license' => 'GNUGPL',
    'app_url' => 'http://api.openoffice.local/',
    'app_debug' => true
);

if ($app_config['app_debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
}
 $config_db  = array(
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'op_api_db',
    'port' => 3306
 );

$smp = mysqli_connect($config_db['host'], $config_db['user'], $config_db['password'], $config_db['database'], $config_db['port']);
if (!$smp) {
    die("Connection failed: " . mysqli_connect_error());
}

$config_jwt = array(
    'secret_key' => 'YOUR_SUPER_SECRET_KEY_REPLACE_ME', // IMPORTANT: Replace with a strong, random key in production.
    'issuer' => 'http://api.openoffice.local',
    'audience' => 'http://api.openoffice.local',
    'algorithm' => 'HS256',
    'expiration_time' => 3600 // 1 hour in seconds
);

$config_smtp = array(
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'your_username',
    'password' => 'your_password',
    'encryption' => 'tls'
);