<?php 
/*
 * Auto-configured includes for Railway deployment
 * This file is automatically created for free hosting
 */

// Try multiple environment variable formats for Railway
$host = $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? getenv('MYSQLHOST') ?? getenv('DB_HOST') ?? 'localhost';
$user = $_ENV['MYSQLUSER'] ?? $_ENV['DB_USER'] ?? getenv('MYSQLUSER') ?? getenv('DB_USER') ?? 'root';
$password = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? getenv('MYSQLPASSWORD') ?? getenv('DB_PASSWORD') ?? '';
$database = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? getenv('MYSQLDATABASE') ?? getenv('DB_NAME') ?? 'werwolf';
$port = $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? getenv('MYSQLPORT') ?? getenv('DB_PORT') ?? 3306;

// Use SQLite fallback for free hosting
$use_sqlite = true;  // Default to SQLite for Railway free tier

if ($use_sqlite) {
    // SQLite setup for Railway
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
        die("Database Connection failed: " . $e->getMessage());
    }
} else {
    // Use compatibility layer for MySQL if available
    if (file_exists(__DIR__ . '/compatibility_layer.php')) {
        require_once(__DIR__ . '/compatibility_layer.php');
        $mysqli = new CompatibleMysqli($host, $user, $password, $database, $port);
    } else {
        $mysqli = new MySQLi($host, $user, $password, $database, $port);
    }
    
    if (mysqli_connect_errno()) {
        error_log("MySQL failed, falling back to SQLite: " . mysqli_connect_error());
        // Fallback to SQLite
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
        zufaellige_auswahl INTEGER DEFAULT 0,
        zufaellige_auswahl_bonus INTEGER DEFAULT 0,
        werwolfe_einstimmig INTEGER DEFAULT 1,
        werwolf_opfer INTEGER DEFAULT -1,
        werwolf_count INTEGER DEFAULT 0,
        hexe_count INTEGER DEFAULT 0,
        seher_count INTEGER DEFAULT 0,
        jaeger_count INTEGER DEFAULT 0,
        amor_count INTEGER DEFAULT 0,
        beschuetzer_count INTEGER DEFAULT 0,
        par_erm_count INTEGER DEFAULT 0,
        lykantrophen_count INTEGER DEFAULT 0,
        spione_count INTEGER DEFAULT 0,
        idioten_count INTEGER DEFAULT 0,
        pazifisten_count INTEGER DEFAULT 0,
        alten_count INTEGER DEFAULT 0,
        urwolf_count INTEGER DEFAULT 0,
        werwolf_timer1 INTEGER DEFAULT 60,
        werwolf_zusatz1 INTEGER DEFAULT 4,
        werwolf_timer2 INTEGER DEFAULT 50,
        werwolf_zusatz2 INTEGER DEFAULT 3,
        dorf_timer INTEGER DEFAULT 550,
        dorf_zusatz INTEGER DEFAULT 10,
        dorf_stichwahl_timer INTEGER DEFAULT 200,
        dorf_stichwahl_zusatz INTEGER DEFAULT 5,
        inaktiv_zeit INTEGER DEFAULT 40,
        inaktiv_zeit_zusatz INTEGER DEFAULT 0,
        tages_text TEXT,
        waiting_for_others_time INTEGER,
        letzter_aufruf INTEGER,
        spielphase INTEGER DEFAULT 0,
        nacht INTEGER DEFAULT 1,
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
        died_this_night INTEGER DEFAULT 0,
        night_identity INTEGER DEFAULT 0,
        vote_target INTEGER DEFAULT -1,
        accused_by INTEGER DEFAULT -1,
        is_mayor INTEGER DEFAULT 0,
        verliebt_mit INTEGER DEFAULT -1,
        hexe_heiltraenke INTEGER DEFAULT 0,
        hexe_todestraenke INTEGER DEFAULT 0,
        hexe_opfer INTEGER DEFAULT -1,
        hexe_heilt INTEGER DEFAULT 0,
        beschuetzer_letzte_runde_beschuetzt INTEGER DEFAULT -1,
        par_erm_eingesetzt INTEGER DEFAULT 0,
        jaeger_darf_schiessen INTEGER DEFAULT 0,
        buergermeister_darf_weitergeben INTEGER DEFAULT 0,
        urwolf_anzahl_faehigkeiten INTEGER DEFAULT 0,
        countdown_bis INTEGER DEFAULT 0,
        countdown_ab INTEGER DEFAULT 0,
        verification_number INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
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
    
    // Create player_messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS player_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        player_id INTEGER NOT NULL,
        message_text TEXT,
        is_read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (player_id) REFERENCES players(id)
    )");
    
    // Create game_cache table
    $pdo->exec("CREATE TABLE IF NOT EXISTS game_cache (
        game_id INTEGER NOT NULL,
        cache_key TEXT NOT NULL,
        cache_data TEXT,
        updated_at INTEGER,
        PRIMARY KEY (game_id, cache_key),
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
            // Convert some MySQL-specific syntax to SQLite
            $query = str_replace('`', '', $query); // Remove backticks
            $query = preg_replace('/AUTO_INCREMENT/i', 'AUTOINCREMENT', $query);
            
            // Handle old table creation attempts - just return success
            if (preg_match('/CREATE TABLE \d+_(game|spieler)/i', $query)) {
                // Old code trying to create per-game tables - ignore but return success
                return new SQLiteResultWrapper($this->pdo->query("SELECT 1"));
            }
            
            // Handle INSERT INTO old table format - convert to new format
            if (preg_match('/INSERT INTO (\d+)_spieler/i', $query, $matches)) {
                $gameId = $matches[1];
                // Convert to new players table format
                $query = preg_replace('/INSERT INTO \d+_spieler/', 'INSERT INTO players', $query);
                // Add game_id if not present
                if (!preg_match('/game_id/', $query)) {
                    $query = str_replace('(', "(game_id, player_number, ", $query);
                    $query = str_replace('VALUES (', "VALUES ($gameId, ", $query);
                }
            }
            
            // Handle INSERT INTO old game table format
            if (preg_match('/INSERT INTO (\d+)_game/i', $query, $matches)) {
                $gameId = $matches[1];
                $query = preg_replace('/INSERT INTO \d+_game/', 'INSERT INTO games', $query);
                if (!preg_match('/\bid\b/', $query)) {
                    $query = str_replace('(', "(id, ", $query);
                    $query = str_replace('VALUES (', "VALUES ($gameId, ", $query);
                }
            }
            
            // Handle SELECT from old tables
            if (preg_match('/FROM (\d+)_spieler/i', $query, $matches)) {
                $gameId = $matches[1];
                $query = str_replace($matches[0], "FROM players WHERE game_id = $gameId", $query);
                // Fix WHERE clause duplication
                $query = preg_replace('/WHERE game_id = \d+ WHERE/', 'WHERE game_id = '.$gameId.' AND', $query);
            }
            
            if (preg_match('/FROM (\d+)_game/i', $query, $matches)) {
                $gameId = $matches[1];
                $query = str_replace($matches[0], "FROM games WHERE id = $gameId", $query);
            }
            
            // Handle UPDATE old tables
            if (preg_match('/UPDATE (\d+)_spieler/i', $query, $matches)) {
                $gameId = $matches[1];
                $query = str_replace($matches[0], "UPDATE players", $query);
                $query = str_replace('SET ', "SET ", $query);
                $query = str_replace('WHERE ', "WHERE game_id = $gameId AND ", $query);
            }
            
            if (preg_match('/UPDATE (\d+)_game/i', $query, $matches)) {
                $gameId = $matches[1];
                $query = str_replace($matches[0], "UPDATE games", $query);
                $query = str_replace('WHERE ', "WHERE id = $gameId AND ", $query);
            }
            
            $result = $this->pdo->query($query);
            
            // Return a wrapper that mimics mysqli_result
            if ($result) {
                return new SQLiteResultWrapper($result);
            }
            return false;
        } catch (PDOException $e) {
            error_log("SQLite Query Error: " . $e->getMessage() . " Query: " . $query);
            // Return empty result to prevent crashes
            return new SQLiteResultWrapper($this->pdo->query("SELECT 1 WHERE 0"));
        }
    }
    
    public function prepare($query) {
        $query = str_replace('`', '', $query);
        return $this->pdo->prepare($query);
    }
    
    public function real_escape_string($string) {
        return str_replace("'", "''", $string);
    }
    
    public function insert_id() {
        return $this->pdo->lastInsertId();
    }
    
    public function set_charset($charset) {
        // SQLite doesn't need charset setting
        return true;
    }
}

// Wrapper for SQLite results to mimic mysqli_result
class SQLiteResultWrapper {
    private $stmt;
    private $results = [];
    private $index = 0;
    public $num_rows = 0;
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->num_rows = count($this->results);
    }
    
    public function fetch_assoc() {
        if ($this->index < $this->num_rows) {
            return $this->results[$this->index++];
        }
        return null;
    }
    
    public function fetch_array() {
        return $this->fetch_assoc();
    }
}

?>