<?php 
/*
 * Free Hosting Database Configuration
 * Works with Railway, Heroku, and other free platforms
 */

// Try multiple environment variable formats
$host = $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? getenv('MYSQLHOST') ?? getenv('DB_HOST') ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? $_ENV['DB_USER'] ?? getenv('MYSQLUSER') ?? getenv('DB_USER') ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? getenv('MYSQLPASSWORD') ?? getenv('DB_PASSWORD') ?? '';
$database = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? getenv('MYSQLDATABASE') ?? getenv('DB_NAME') ?? 'werwolf';
$port = $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? getenv('MYSQLPORT') ?? getenv('DB_PORT') ?? 3306;

// Fallback to SQLite for completely free hosting
$use_sqlite = false;
if (empty($host) || $host === 'localhost') {
    $use_sqlite = true;
}

if ($use_sqlite) {
    // SQLite fallback for free hosting without MySQL
    $db_file = __DIR__ . '/../data/werewolf.db';
    
    // Create data directory if it doesn't exist
    $data_dir = dirname($db_file);
    if (!is_dir($data_dir)) {
        mkdir($data_dir, 0755, true);
    }
    
    try {
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables if they don't exist
        createSQLiteTables($pdo);
        
        // Create a MySQL-compatible wrapper
        $mysqli = new SQLiteToMySQLiWrapper($pdo);
        
    } catch (PDOException $e) {
        die("SQLite Connection failed: " . $e->getMessage());
    }
} else {
    // Use compatibility layer for seamless migration
    require_once(__DIR__ . '/compatibility_layer.php');
    
    // MySQL connection for platforms with database
    $mysqli = new CompatibleMysqli($host, $user, $password, $database, $port);
    
    if (mysqli_connect_errno()) {
        // Fallback to SQLite if MySQL fails
        error_log("MySQL failed, falling back to SQLite: " . mysqli_connect_error());
        $use_sqlite = true;
        
        $db_file = __DIR__ . '/../data/werewolf.db';
        $data_dir = dirname($db_file);
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }
        
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        createSQLiteTables($pdo);
        $mysqli = new SQLiteToMySQLiWrapper($pdo);
    } else {
        $mysqli->set_charset("utf8mb4");
    }
}

function createSQLiteTables($pdo) {
    // Create games table
    $pdo->exec("CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY,
        status TEXT DEFAULT 'setup',
        phase INTEGER DEFAULT 0,
        night_number INTEGER DEFAULT 1,
        charaktere_aufdecken INTEGER DEFAULT 0,
        buergermeister_weitergeben INTEGER DEFAULT 0,
        seher_sieht_identitaet INTEGER DEFAULT 1,
        werwolf_count INTEGER DEFAULT 0,
        hexe_count INTEGER DEFAULT 0,
        seher_count INTEGER DEFAULT 0,
        jaeger_count INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create players table
    $pdo->exec("CREATE TABLE IF NOT EXISTS players (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER NOT NULL,
        player_number INTEGER NOT NULL,
        name TEXT NOT NULL,
        is_alive INTEGER DEFAULT 1,
        is_game_master INTEGER DEFAULT 0,
        is_ready INTEGER DEFAULT 0,
        needs_reload INTEGER DEFAULT 0,
        night_identity INTEGER DEFAULT 0,
        vote_target INTEGER DEFAULT -1,
        accused_by INTEGER DEFAULT -1,
        is_mayor INTEGER DEFAULT 0,
        verliebt_mit INTEGER DEFAULT -1,
        verification_number INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (game_id) REFERENCES games(id)
    )");
    
    // Create game_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS game_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        game_id INTEGER NOT NULL,
        log_type TEXT DEFAULT 'game',
        player_id INTEGER NULL,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (game_id) REFERENCES games(id)
    )");
}

// Simple wrapper to make SQLite work with mysqli calls
class SQLiteToMySQLiWrapper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function Query($query) {
        try {
            return $this->pdo->query($query);
        } catch (PDOException $e) {
            error_log("SQLite Query Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function prepare($query) {
        return $this->pdo->prepare($query);
    }
    
    public function real_escape_string($string) {
        return str_replace("'", "''", $string);
    }
    
    public function insert_id() {
        return $this->pdo->lastInsertId();
    }
}

?>