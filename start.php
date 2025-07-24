<?php
/*
 * Simple PHP Server Start Script
 * Works on most free hosting platforms
 */

// Get port from environment
$port = $_SERVER['PORT'] ?? getenv('PORT') ?? 8000;
$host = '0.0.0.0';

// Start PHP built-in server
echo "Starting Werewolf Game server on $host:$port\n";
echo "Visit: http://localhost:$port/setup-free.php\n";

// Change to web directory
chdir(__DIR__);

// Start server
$command = "php -S $host:$port -t .";
echo "Running: $command\n";

// Execute the server
exec($command);
?>