<?php
/*
 * Simple health check endpoint for Railway
 */

// Set headers
header('Content-Type: text/plain');
header('Cache-Control: no-cache');

// Basic health check
echo "OK";

// Check if PHP is working
if (function_exists('phpversion')) {
    echo "\nPHP: " . phpversion();
}

// Check if includes.php exists
if (file_exists('includes/includes.php')) {
    echo "\nDB Config: Found";
    
    // Try to initialize database
    try {
        require_once('includes/includes.php');
        echo "\nDB: Connected";
    } catch (Exception $e) {
        echo "\nDB Error: " . $e->getMessage();
    }
} else {
    echo "\nDB Config: Missing includes/includes.php";
}

// Check file structure
echo "\nFiles: ";
if (file_exists('Werwolf.php')) echo "Werwolf.php ";
if (file_exists('includes/')) echo "includes/ ";
if (is_dir('data/')) echo "data/ ";

// Return HTTP 200
http_response_code(200);
?>