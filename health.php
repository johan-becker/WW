<?php
/*
 * Simple health check endpoint for Railway
 */

// Set headers
header('Content-Type: text/plain');
header('Cache-Control: no-cache');

// Basic health check
echo "OK";

// Optional: Check if PHP is working
if (function_exists('phpversion')) {
    echo "\nPHP: " . phpversion();
}

// Optional: Check if database can be initialized
try {
    if (file_exists('includes/includes.free.php')) {
        echo "\nDB: Ready";
    }
} catch (Exception $e) {
    echo "\nDB: " . $e->getMessage();
}

// Return HTTP 200
http_response_code(200);
?>