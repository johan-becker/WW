<?php 
/*
 * DigitalOcean Optimized Database Configuration
 * Use this for DigitalOcean App Platform or Droplet deployment
 */

// DigitalOcean environment variables (App Platform)
$id = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? "your_db_user";
$pw = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? "your_db_password";
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? "your_db_host";
$db = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? "werwolf";
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 25060;

// Use compatibility layer for seamless migration
require_once(__DIR__ . '/compatibility_layer.php');

// DigitalOcean Managed Database connection with SSL
$mysqli = new CompatibleMysqli($host, $id, $pw, $db, $port);

// Set SSL mode for DigitalOcean managed databases
if (isset($_ENV['DB_CA_CERT'])) {
    $mysqli->ssl_set(null, null, $_ENV['DB_CA_CERT'], null, null);
}

// Connection options for DigitalOcean
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
$mysqli->options(MYSQLI_OPT_READ_TIMEOUT, 30);

if (mysqli_connect_errno()) {
    // Log connection error for debugging
    error_log("MySQL Connection Error: " . mysqli_connect_error());
    
    // User-friendly error message
    printf("Database connection failed. Please check your configuration.\n");
    exit;
}

// Set charset
$mysqli->set_charset("utf8mb4");

// Connection pool optimization for DigitalOcean
$mysqli->query("SET SESSION wait_timeout=600");
$mysqli->query("SET SESSION interactive_timeout=600");

?>