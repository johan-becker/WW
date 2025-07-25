<?php

/*

werwolfonline, a php web game
    Copyright (C) 2023

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

class SecurityHelper {
    
    /**
     * Validate and sanitize game ID
     */
    public static function validateGameID($gameID) {
        if (!isset($gameID) || !is_numeric($gameID)) {
            throw new InvalidArgumentException("Invalid game ID");
        }
        
        $gameID = intval($gameID);
        if ($gameID < 10000 || $gameID > 99999) {
            throw new InvalidArgumentException("Game ID out of range");
        }
        
        return $gameID;
    }
    
    /**
     * Validate and sanitize player ID
     */
    public static function validatePlayerID($playerID) {
        if (!isset($playerID) || !is_numeric($playerID)) {
            throw new InvalidArgumentException("Invalid player ID");
        }
        
        $playerID = intval($playerID);
        if ($playerID < 1 || $playerID > _MAXPLAYERS) {
            throw new InvalidArgumentException("Player ID out of range");
        }
        
        return $playerID;
    }
    
    /**
     * Validate player name
     */
    public static function validatePlayerName($name) {
        if (!isset($name) || empty(trim($name))) {
            throw new InvalidArgumentException("Player name cannot be empty");
        }
        
        $name = trim($name);
        if (strlen($name) > 50) {
            throw new InvalidArgumentException("Player name too long");
        }
        
        if (!preg_match('/^[a-zA-Z0-9äöüÄÖÜßáéíóúàèìòùâêîôû\s\-_]+$/', $name)) {
            throw new InvalidArgumentException("Player name contains invalid characters");
        }
        
        return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate character ID
     */
    public static function validateCharacterID($charID) {
        if (!isset($charID) || !is_numeric($charID)) {
            throw new InvalidArgumentException("Invalid character ID");
        }
        
        $charID = intval($charID);
        if ($charID < 0 || $charID > 14) {
            throw new InvalidArgumentException("Character ID out of range");
        }
        
        return $charID;
    }
    
    /**
     * Validate phase ID
     */
    public static function validatePhaseID($phaseID) {
        if (!isset($phaseID) || !is_numeric($phaseID)) {
            throw new InvalidArgumentException("Invalid phase ID");
        }
        
        $phaseID = intval($phaseID);
        if ($phaseID < 0 || $phaseID > 16) {
            throw new InvalidArgumentException("Phase ID out of range");
        }
        
        return $phaseID;
    }
    
    /**
     * Sanitize log message
     */
    public static function sanitizeLogMessage($message) {
        if (!isset($message)) {
            return "";
        }
        
        $message = trim($message);
        if (strlen($message) > 1000) {
            $message = substr($message, 0, 1000);
        }
        
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate boolean values
     */
    public static function validateBoolean($value) {
        if (!isset($value)) {
            return false;
        }
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return intval($value) === 1;
        }
        
        if (is_string($value)) {
            return strtolower($value) === 'true' || $value === '1';
        }
        
        return false;
    }
    
    /**
     * Validate timer values
     */
    public static function validateTimer($timer) {
        if (!isset($timer) || !is_numeric($timer)) {
            return 0;
        }
        
        $timer = intval($timer);
        if ($timer < 0 || $timer > 86400) { // Max 24 hours
            throw new InvalidArgumentException("Timer value out of range");
        }
        
        return $timer;
    }
    
    /**
     * Validate role count
     */
    public static function validateRoleCount($count) {
        if (!isset($count) || !is_numeric($count)) {
            return 0;
        }
        
        $count = intval($count);
        if ($count < 0 || $count > _MAXPLAYERS) {
            throw new InvalidArgumentException("Role count out of range");
        }
        
        return $count;
    }
}

class DatabaseHelper {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Safe table name construction
     */
    private function getTableName($gameID, $suffix) {
        $gameID = SecurityHelper::validateGameID($gameID);
        return $gameID . "_" . $suffix;
    }
    
    /**
     * Check if game exists safely
     */
    public function gameExists($gameID) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            $tableName = $this->getTableName($gameID, 'game');
            
            $stmt = $this->mysqli->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
            if (!$stmt) {
                throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
            }
            
            $stmt->bind_param("s", $tableName);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();
            
            return $exists;
        } catch (Exception $e) {
            error_log("Error checking game existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get game data safely
     */
    public function getGameData($gameID) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            
            if (!$this->gameExists($gameID)) {
                throw new RuntimeException("Game does not exist");
            }
            
            $tableName = $this->getTableName($gameID, 'game');
            $stmt = $this->mysqli->prepare("SELECT * FROM `{$tableName}` LIMIT 1");
            if (!$stmt) {
                throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            
            return $data;
        } catch (Exception $e) {
            error_log("Error getting game data: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get player data safely
     */
    public function getPlayerData($gameID, $playerID = null) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            
            if (!$this->gameExists($gameID)) {
                throw new RuntimeException("Game does not exist");
            }
            
            $tableName = $this->getTableName($gameID, 'spieler');
            
            if ($playerID !== null) {
                $playerID = SecurityHelper::validatePlayerID($playerID);
                $stmt = $this->mysqli->prepare("SELECT * FROM `{$tableName}` WHERE id = ?");
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
                }
                $stmt->bind_param("i", $playerID);
            } else {
                $stmt = $this->mysqli->prepare("SELECT * FROM `{$tableName}` ORDER BY id");
                if (!$stmt) {
                    throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
                }
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($playerID !== null) {
                $data = $result->fetch_assoc();
            } else {
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            error_log("Error getting player data: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update game phase safely
     */
    public function updateGamePhase($gameID, $newPhase) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            $newPhase = SecurityHelper::validatePhaseID($newPhase);
            
            if (!$this->gameExists($gameID)) {
                throw new RuntimeException("Game does not exist");
            }
            
            $tableName = $this->getTableName($gameID, 'game');
            
            // Use transaction for consistency
            $this->mysqli->begin_transaction();
            
            $stmt = $this->mysqli->prepare("UPDATE `{$tableName}` SET spielphase = ?, letzterAufruf = ? WHERE 1");
            if (!$stmt) {
                $this->mysqli->rollback();
                throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
            }
            
            $currentTime = time();
            $stmt->bind_param("ii", $newPhase, $currentTime);
            
            if (!$stmt->execute()) {
                $this->mysqli->rollback();
                throw new RuntimeException("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            $this->mysqli->commit();
            
            return true;
        } catch (Exception $e) {
            if ($this->mysqli) {
                $this->mysqli->rollback();
            }
            error_log("Error updating game phase: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update game settings safely
     */
    public function updateGameSettings($gameID, $settings) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            
            if (!$this->gameExists($gameID)) {
                throw new RuntimeException("Game does not exist");
            }
            
            $tableName = $this->getTableName($gameID, 'game');
            
            // Validate all settings
            $validatedSettings = [];
            $allowedSettings = [
                'buergermeisterWeitergeben', 'charaktereAufdecken', 'seherSiehtIdentitaet',
                'werwolfzahl', 'hexenzahl', 'jaegerzahl', 'seherzahl', 'amorzahl',
                'beschuetzerzahl', 'parErmZahl', 'lykantrophenzahl', 'spionezahl',
                'idiotenzahl', 'pazifistenzahl', 'altenzahl', 'urwolfzahl',
                'werwolftimer1', 'werwolfzusatz1', 'werwolftimer2', 'werwolfzusatz2',
                'dorftimer', 'dorfzusatz', 'dorfstichwahltimer', 'dorfstichwahlzusatz',
                'zufaelligeAuswahl', 'zufaelligeAuswahlBonus', 'inaktivzeit', 'inaktivzeitzusatz'
            ];
            
            foreach ($allowedSettings as $setting) {
                if (isset($settings[$setting])) {
                    if (strpos($setting, 'timer') !== false || strpos($setting, 'zusatz') !== false) {
                        $validatedSettings[$setting] = SecurityHelper::validateTimer($settings[$setting]);
                    } elseif (strpos($setting, 'zahl') !== false || strpos($setting, 'zahl') !== false) {
                        $validatedSettings[$setting] = SecurityHelper::validateRoleCount($settings[$setting]);
                    } else {
                        $validatedSettings[$setting] = SecurityHelper::validateBoolean($settings[$setting]) ? 1 : 0;
                    }
                }
            }
            
            if (empty($validatedSettings)) {
                throw new InvalidArgumentException("No valid settings provided");
            }
            
            // Build dynamic query
            $setParts = [];
            $types = '';
            $values = [];
            
            foreach ($validatedSettings as $key => $value) {
                $setParts[] = "`{$key}` = ?";
                $types .= 'i';
                $values[] = $value;
            }
            
            $this->mysqli->begin_transaction();
            
            $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setParts) . " WHERE 1";
            $stmt = $this->mysqli->prepare($sql);
            
            if (!$stmt) {
                $this->mysqli->rollback();
                throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                $this->mysqli->rollback();
                throw new RuntimeException("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            $this->mysqli->commit();
            
            return true;
        } catch (Exception $e) {
            if ($this->mysqli) {
                $this->mysqli->rollback();
            }
            error_log("Error updating game settings: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete old games safely with proper locking
     */
    public function deleteOldGames($timeThreshold = 7200) {
        try {
            $zeitpunkt = time() - $timeThreshold;
            
            // Get table lock to prevent race conditions
            $this->mysqli->query("LOCK TABLES information_schema.tables READ");
            
            $deletedGames = 0;
            
            for ($i = 10000; $i <= 99999; $i++) {
                try {
                    if ($this->gameExists($i)) {
                        $gameData = $this->getGameData($i);
                        
                        if ($gameData && isset($gameData['letzterAufruf']) && $gameData['letzterAufruf'] < $zeitpunkt) {
                            // Delete game tables
                            $gameTable = $this->getTableName($i, 'game');
                            $playerTable = $this->getTableName($i, 'spieler');
                            
                            $this->mysqli->query("DROP TABLE IF EXISTS `{$gameTable}`");
                            $this->mysqli->query("DROP TABLE IF EXISTS `{$playerTable}`");
                            
                            $deletedGames++;
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error deleting game {$i}: " . $e->getMessage());
                    continue;
                }
            }
            
            $this->mysqli->query("UNLOCK TABLES");
            
            return $deletedGames;
        } catch (Exception $e) {
            $this->mysqli->query("UNLOCK TABLES");
            error_log("Error in deleteOldGames: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update player data safely
     */
    public function updatePlayerData($gameID, $playerID, $data) {
        try {
            $gameID = SecurityHelper::validateGameID($gameID);
            $playerID = SecurityHelper::validatePlayerID($playerID);
            
            if (!$this->gameExists($gameID)) {
                throw new RuntimeException("Game does not exist");
            }
            
            $tableName = $this->getTableName($gameID, 'spieler');
            
            $allowedFields = ['reload', 'bereit', 'lebt', 'nachtIdentitaet', 'playerlog'];
            $setParts = [];
            $types = '';
            $values = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $setParts[] = "`{$field}` = ?";
                    if ($field === 'playerlog') {
                        $types .= 's';
                        $values[] = SecurityHelper::sanitizeLogMessage($value);
                    } elseif ($field === 'nachtIdentitaet') {
                        $types .= 'i';
                        $values[] = SecurityHelper::validateCharacterID($value);
                    } else {
                        $types .= 'i';
                        $values[] = SecurityHelper::validateBoolean($value) ? 1 : 0;
                    }
                }
            }
            
            if (empty($setParts)) {
                throw new InvalidArgumentException("No valid fields provided");
            }
            
            $this->mysqli->begin_transaction();
            
            $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setParts) . " WHERE id = ?";
            $stmt = $this->mysqli->prepare($sql);
            
            if (!$stmt) {
                $this->mysqli->rollback();
                throw new RuntimeException("Prepare failed: " . $this->mysqli->error);
            }
            
            $types .= 'i';
            $values[] = $playerID;
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                $this->mysqli->rollback();
                throw new RuntimeException("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            $this->mysqli->commit();
            
            return true;
        } catch (Exception $e) {
            if ($this->mysqli) {
                $this->mysqli->rollback();
            }
            error_log("Error updating player data: " . $e->getMessage());
            throw $e;
        }
    }
}

class ErrorHandler {
    private static $logFile = null;
    
    public static function init($logFile = null) {
        self::$logFile = $logFile ?: __DIR__ . '/../log/error.log';
        
        // Ensure log directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);        
    }
    
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorMessage = "Error [{$severity}]: {$message} in {$file}:{$line}";
        self::logError($errorMessage);
        
        // Don't show errors to users in production
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            return true;
        }
        
        return false;
    }
    
    public static function handleException($exception) {
        $errorMessage = "Uncaught Exception: " . $exception->getMessage() . 
                       " in " . $exception->getFile() . ":" . $exception->getLine();
        self::logError($errorMessage);
        
        // Show user-friendly error page
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo self::getErrorPage("Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.");
    }
    
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorMessage = "Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}";
            self::logError($errorMessage);
        }
    }
    
    private static function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        if (self::$logFile) {
            error_log($logMessage, 3, self::$logFile);
        } else {
            error_log($message);
        }
    }
    
    private static function getErrorPage($message) {
        return "
        <!DOCTYPE html>
        <html lang='de'>
        <head>
            <meta charset='UTF-8'>
            <title>Fehler - Werwolf Online</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f0f0; }
                .error-container { background: white; padding: 30px; border-radius: 10px; max-width: 500px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #cc0000; }
                .btn { background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>🐺 Werwolf Online</h1>
                <h2>Oops! Etwas ist schiefgelaufen</h2>
                <p>{$message}</p>
                <a href='/' class='btn'>Zurück zur Startseite</a>
            </div>
        </body>
        </html>";
    }
}

?>