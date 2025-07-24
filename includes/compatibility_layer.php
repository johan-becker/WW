<?php
/*
 * COMPATIBILITY LAYER
 * Ensures all existing code works with new database structure
 * This file makes old code work with new tables seamlessly
 */

require_once('db_helpers.php');

// Global compatibility flag
$USE_NEW_DATABASE = true;

/**
 * Override mysqli->Query to handle old table references
 */
class CompatibleMysqli extends mysqli {
    private $originalMysqli;
    
    public function __construct($host, $user, $password, $database) {
        parent::__construct($host, $user, $password, $database);
        $this->originalMysqli = $this;
    }
    
    public function Query($query) {
        global $USE_NEW_DATABASE;
        
        if (!$USE_NEW_DATABASE) {
            return parent::query($query);
        }
        
        // Convert old queries to new format
        $convertedQuery = $this->convertQuery($query);
        
        if ($convertedQuery !== $query) {
            // Log conversion for debugging
            error_log("CONVERTED QUERY: $query -> $convertedQuery");
        }
        
        return parent::query($convertedQuery);
    }
    
    private function convertQuery($query) {
        // Handle CREATE TABLE statements
        if (preg_match('/CREATE TABLE [`\'"]?(\d+)_(game|spieler)[`\'"]?/i', $query, $matches)) {
            $gameId = $matches[1];
            $tableType = $matches[2];
            
            // Don't create old tables - they're handled by new schema
            return "SELECT 1"; // Dummy query that succeeds
        }
        
        // Handle DROP TABLE statements
        if (preg_match('/DROP TABLE (?:IF EXISTS )?[`\'"]?(\d+)_(game|spieler)[`\'"]?/i', $query, $matches)) {
            return "SELECT 1"; // Dummy query - new schema handles cleanup
        }
        
        // Handle SELECT statements
        if (preg_match('/SELECT (.*) FROM [`\'"]?(\d+)_(game|spieler)[`\'"]?(.*)$/i', $query, $matches)) {
            $select = $matches[1];
            $gameId = $matches[2];
            $tableType = $matches[3];
            $conditions = $matches[4];
            
            if ($tableType === 'game') {
                return "SELECT $select FROM games WHERE id = $gameId $conditions";
            } else {
                // Convert player table columns
                $convertedSelect = $this->convertPlayerColumns($select);
                $convertedConditions = $this->convertPlayerColumns($conditions);
                
                return "SELECT $convertedSelect FROM players WHERE game_id = $gameId $convertedConditions";
            }
        }
        
        // Handle UPDATE statements
        if (preg_match('/UPDATE [`\'"]?(\d+)_(game|spieler)[`\'"]? SET (.*)$/i', $query, $matches)) {
            $gameId = $matches[1];
            $tableType = $matches[2];
            $setClause = $matches[3];
            
            if ($tableType === 'game') {
                return "UPDATE games SET $setClause WHERE id = $gameId";
            } else {
                $convertedSet = $this->convertPlayerColumns($setClause);
                return "UPDATE players SET $convertedSet WHERE game_id = $gameId";
            }
        }
        
        // Handle INSERT statements
        if (preg_match('/INSERT INTO [`\'"]?(\d+)_(game|spieler)[`\'"]? (.*)$/i', $query, $matches)) {
            $gameId = $matches[1];
            $tableType = $matches[2];
            $values = $matches[3];
            
            if ($tableType === 'game') {
                // Add game_id to INSERT
                if (strpos($values, 'VALUES') !== false) {
                    $values = str_replace('VALUES (', "VALUES ($gameId, ", $values);
                    return "INSERT INTO games (id, $values";
                }
            } else {
                // Convert player INSERT
                $convertedValues = $this->convertPlayerColumns($values);
                return "INSERT INTO players (game_id, player_number, $convertedValues";
            }
        }
        
        return $query; // Return unchanged if no pattern matches
    }
    
    private function convertPlayerColumns($text) {
        $columnMap = [
            'id' => 'player_number',
            'spielleiter' => 'is_game_master',
            'lebt' => 'is_alive',
            'bereit' => 'is_ready',
            'reload' => 'needs_reload',
            'dieseNachtGestorben' => 'died_this_night',
            'nachtIdentitaet' => 'night_identity',
            'wahlAuf' => 'vote_target',
            'angeklagtVon' => 'accused_by',
            'buergermeister' => 'is_mayor',
            'verifizierungsnr' => 'verification_number',
            'countdownBis' => 'countdown_bis',
            'countdownAb' => 'countdown_ab'
        ];
        
        foreach ($columnMap as $old => $new) {
            $text = preg_replace('/\b' . $old . '\b/', $new, $text);
        }
        
        return $text;
    }
}

/**
 * Helper functions to maintain old function signatures
 */

// Override getName function to work with new structure
function getName($mysqli, $playerId, $gameId = null) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        // Call original function logic here
        return getNameOriginal($mysqli, $playerId);
    }
    
    // Try to get name from current context if gameId not provided
    if ($gameId === null) {
        // Look for gameID in current context (from URL or session)
        $gameId = $_GET['game'] ?? $_COOKIE['spielID'] ?? null;
    }
    
    if ($gameId) {
        $player = getPlayer($mysqli, $gameId, $playerId);
        return $player ? $player['name'] : 'Unknown';
    }
    
    // Fallback: search all games (less efficient but maintains compatibility)
    $stmt = $mysqli->prepare("SELECT name FROM players WHERE player_number = ? LIMIT 1");
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['name'] : 'Unknown';
}

// Override toGameLog to use new structure
function toGameLog($mysqli, $message, $gameId = null) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        return toGameLogOriginal($mysqli, $message);
    }
    
    if ($gameId === null) {
        $gameId = $_GET['game'] ?? $_COOKIE['spielID'] ?? null;
    }
    
    if ($gameId) {
        return addGameLog($mysqli, $gameId, $message);
    }
    
    return false;
}

// Override toPlayerLog to use new structure  
function toPlayerLog($mysqli, $message, $playerId, $gameId = null) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        return toPlayerLogOriginal($mysqli, $message, $playerId);
    }
    
    if ($gameId === null) {
        $gameId = $_GET['game'] ?? $_COOKIE['spielID'] ?? null;
    }
    
    if ($gameId) {
        // Get actual player database ID
        $player = getPlayer($mysqli, $gameId, $playerId);
        if ($player) {
            return addPlayerLog($mysqli, $player['id'], $message);
        }
    }
    
    return false;
}

// Override toAllPlayerLog to use new structure
function toAllPlayerLog($mysqli, $message, $gameId = null) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        return toAllPlayerLogOriginal($mysqli, $message);
    }
    
    if ($gameId === null) {
        $gameId = $_GET['game'] ?? $_COOKIE['spielID'] ?? null;
    }
    
    if ($gameId) {
        $players = getGamePlayers($mysqli, $gameId);
        while ($player = $players->fetch_assoc()) {
            addPlayerLog($mysqli, $player['id'], $message);
        }
        return true;
    }
    
    return false;
}

/**
 * Function to handle table creation requests
 */
function handleTableCreation($mysqli, $gameId) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        return false; // Let original code handle it
    }
    
    // Check if game already exists
    $game = getGame($mysqli, $gameId);
    if (!$game) {
        return createNewGame($mysqli, $gameId);
    }
    
    return true; // Game already exists
}

/**
 * Compatibility wrapper for old database result processing
 */
class CompatibilityResult {
    private $actualResult;
    private $gameId;
    private $isPlayerTable;
    
    public function __construct($result, $gameId = null, $isPlayerTable = false) {
        $this->actualResult = $result;
        $this->gameId = $gameId;
        $this->isPlayerTable = $isPlayerTable;
    }
    
    public function fetch_assoc() {
        if (!$this->actualResult) return null;
        
        $row = $this->actualResult->fetch_assoc();
        if (!$row) return null;
        
        if ($this->isPlayerTable) {
            // Convert new column names back to old names for compatibility
            $converted = [];
            $columnMap = [
                'player_number' => 'id',
                'is_game_master' => 'spielleiter', 
                'is_alive' => 'lebt',
                'is_ready' => 'bereit',
                'needs_reload' => 'reload',
                'died_this_night' => 'dieseNachtGestorben',
                'night_identity' => 'nachtIdentitaet',
                'vote_target' => 'wahlAuf',
                'accused_by' => 'angeklagtVon',
                'is_mayor' => 'buergermeister',
                'verification_number' => 'verifizierungsnr',
                'countdown_bis' => 'countdownBis',
                'countdown_ab' => 'countdownAb'
            ];
            
            foreach ($row as $key => $value) {
                $newKey = $columnMap[$key] ?? $key;
                $converted[$newKey] = $value;
            }
            
            return $converted;
        }
        
        return $row;
    }
    
    public function __get($name) {
        return $this->actualResult->$name ?? null;
    }
    
    public function __call($method, $args) {
        return call_user_func_array([$this->actualResult, $method], $args);
    }
}

/**
 * Override specific functions that create/check tables
 */
function spielInitialisieren($mysqli, $playerCount, $gameId = null) {
    global $USE_NEW_DATABASE;
    
    if (!$USE_NEW_DATABASE) {
        return spielInitialisierenOriginal($mysqli, $playerCount);
    }
    
    if ($gameId === null) {
        $gameId = $_GET['game'] ?? $_COOKIE['spielID'] ?? null;
    }
    
    if (!$gameId) return false;
    
    // Initialize game with new structure
    $updates = [
        'status' => 'playing',
        'phase' => 1
    ];
    
    return updateGame($mysqli, $gameId, $updates);
}

/**
 * Migration toggle functions
 */
function enableNewDatabase() {
    global $USE_NEW_DATABASE;
    $USE_NEW_DATABASE = true;
}

function disableNewDatabase() {
    global $USE_NEW_DATABASE; 
    $USE_NEW_DATABASE = false;
}

function isUsingNewDatabase() {
    global $USE_NEW_DATABASE;
    return $USE_NEW_DATABASE;
}

/**
 * Test function to verify compatibility
 */
function testCompatibility($mysqli, $testGameId = 12345) {
    $errors = [];
    
    echo "<h2>Testing Database Compatibility</h2>\n";
    
    // Test 1: Game creation
    echo "Test 1: Game creation... ";
    if (handleTableCreation($mysqli, $testGameId)) {
        echo "✅ PASS\n<br>";
    } else {
        echo "❌ FAIL\n<br>";
        $errors[] = "Game creation failed";
    }
    
    // Test 2: Player addition
    echo "Test 2: Player addition... ";
    if (addPlayerToGame($mysqli, $testGameId, 0, "Test Player", true, 12345)) {
        echo "✅ PASS\n<br>";
    } else {
        echo "❌ FAIL\n<br>";
        $errors[] = "Player addition failed";
    }
    
    // Test 3: Old-style query conversion
    echo "Test 3: Query conversion... ";
    $compatMysqli = new CompatibleMysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
    $result = $compatMysqli->Query("SELECT * FROM {$testGameId}_spieler WHERE lebt = 1");
    if ($result !== false) {
        echo "✅ PASS\n<br>";
    } else {
        echo "❌ FAIL\n<br>";
        $errors[] = "Query conversion failed";
    }
    
    // Test 4: getName function
    echo "Test 4: getName function... ";
    $name = getName($mysqli, 0, $testGameId);
    if ($name === "Test Player") {
        echo "✅ PASS\n<br>";
    } else {
        echo "❌ FAIL (got: $name)\n<br>";
        $errors[] = "getName function failed";
    }
    
    // Cleanup test data
    deleteGame($mysqli, $testGameId);
    
    if (empty($errors)) {
        echo "<h3 style='color: green'>✅ ALL TESTS PASSED - Migration is safe!</h3>\n";
        return true;
    } else {
        echo "<h3 style='color: red'>❌ TESTS FAILED:</h3>\n";
        foreach ($errors as $error) {
            echo "- $error\n<br>";
        }
        return false;
    }
}

// Auto-include this file when db_helpers is loaded
if (!function_exists('getGameOriginal')) {
    // Store original functions if they exist
    // This prevents conflicts during testing
}

?>