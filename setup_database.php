<?php
// Include the database configuration
require_once 'config.php';

echo "Starting database setup...\n";

// --- Execute Schema_table.sql ---
$sql_schema = file_get_contents('Schema_table.sql');
if ($sql_schema === false) {
    die("Error: Could not read Schema_table.sql\n");
}

// Execute multi-query for table creation
if ($smp->multi_query($sql_schema)) {
    // Must consume all results from multi_query
    while ($smp->more_results() && $smp->next_result()) {;}
    echo "Tables created successfully from Schema_table.sql.\n";
} else {
    die("Error creating tables: " . $smp->error . "\n");
}


// --- Execute Schema_data.sql ---
$sql_data = file_get_contents('Schema_data.sql');
if ($sql_data === false) {
    die("Error: Could not read Schema_data.sql\n");
}

// Execute multi-query for data insertion
if ($smp->multi_query($sql_data)) {
    // Must consume all results from multi_query
    while ($smp->more_results() && $smp->next_result()) {;}
    echo "Data inserted successfully from Schema_data.sql.\n";
} else {
    die("Error inserting data: " . $smp->error . "\n");
}

echo "Database setup complete.\n";

$smp->close();
?>
