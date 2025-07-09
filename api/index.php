<?php
// api/index.php

// Send a simple welcome message
echo json_encode([
    'message' => 'Welcome to the OpenOffice API',
    'version' => '1.0.0'
]);
?>
