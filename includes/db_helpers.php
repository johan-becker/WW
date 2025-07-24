<?php
/*
 * New Database Helper Functions
 * For the scalable single-table design
 */

// Get game data
function getGame($mysqli, $gameId) {
    $stmt = $mysqli->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get all players in a game
function getGamePlayers($mysqli, $gameId, $aliveOnly = false) {
    $sql = "SELECT * FROM players WHERE game_id = ?";
    if ($aliveOnly) {
        $sql .= " AND is_alive = 1";
    }
    $sql .= " ORDER BY player_number";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    return $stmt->get_result();
}

// Get specific player
function getPlayer($mysqli, $gameId, $playerNumber, $verificationNumber = null) {
    $sql = "SELECT * FROM players WHERE game_id = ? AND player_number = ?";
    $params = "ii";
    $values = [$gameId, $playerNumber];
    
    if ($verificationNumber !== null) {
        $sql .= " AND verification_number = ?";
        $params .= "i";
        $values[] = $verificationNumber;
    }
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($params, ...$values);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Update player
function updatePlayer($mysqli, $gameId, $playerNumber, $updates) {
    if (empty($updates)) return false;
    
    $setParts = [];
    $params = "";
    $values = [];
    
    foreach ($updates as $field => $value) {
        $setParts[] = "$field = ?";
        if (is_int($value)) {
            $params .= "i";
        } else {
            $params .= "s";
        }
        $values[] = $value;
    }
    
    $sql = "UPDATE players SET " . implode(", ", $setParts) . " WHERE game_id = ? AND player_number = ?";
    $params .= "ii";
    $values[] = $gameId;
    $values[] = $playerNumber;
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($params, ...$values);
    return $stmt->execute();
}

// Update game
function updateGame($mysqli, $gameId, $updates) {
    if (empty($updates)) return false;
    
    $setParts = [];
    $params = "";
    $values = [];
    
    foreach ($updates as $field => $value) {
        $setParts[] = "$field = ?";
        if (is_int($value)) {
            $params .= "i";
        } else {
            $params .= "s";
        }
        $values[] = $value;
    }
    
    $sql = "UPDATE games SET " . implode(", ", $setParts) . " WHERE id = ?";
    $params .= "i";
    $values[] = $gameId;
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($params, ...$values);
    return $stmt->execute();
}

// Get players by role
function getPlayersByRole($mysqli, $gameId, $roleId, $aliveOnly = true) {
    $sql = "SELECT * FROM players WHERE game_id = ? AND night_identity = ?";
    if ($aliveOnly) {
        $sql .= " AND is_alive = 1";
    }
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $gameId, $roleId);
    $stmt->execute();
    return $stmt->get_result();
}

// Count votes for a player
function countVotes($mysqli, $gameId, $targetPlayerNumber) {
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) as vote_count 
        FROM players 
        WHERE game_id = ? AND vote_target = ? AND is_alive = 1
    ");
    $stmt->bind_param("ii", $gameId, $targetPlayerNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['vote_count'];
}

// Get vote results
function getVoteResults($mysqli, $gameId) {
    $stmt = $mysqli->prepare("
        SELECT 
            vote_target, 
            COUNT(*) as votes,
            GROUP_CONCAT(name) as voters
        FROM players 
        WHERE game_id = ? AND vote_target >= 0 AND is_alive = 1
        GROUP BY vote_target
        ORDER BY votes DESC
    ");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    return $stmt->get_result();
}

// Add game log entry
function addGameLog($mysqli, $gameId, $message) {
    $stmt = $mysqli->prepare("INSERT INTO game_logs (game_id, log_type, content) VALUES (?, 'game', ?)");
    $stmt->bind_param("is", $gameId, $message);
    return $stmt->execute();
}

// Add player log entry
function addPlayerLog($mysqli, $playerId, $message) {
    // Get game_id from player
    $stmt = $mysqli->prepare("SELECT game_id FROM players WHERE id = ?");
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $player = $result->fetch_assoc();
    
    if ($player) {
        $stmt = $mysqli->prepare("INSERT INTO game_logs (game_id, log_type, player_id, content) VALUES (?, 'player', ?, ?)");
        $stmt->bind_param("iis", $player['game_id'], $playerId, $message);
        return $stmt->execute();
    }
    return false;
}

// Get game logs
function getGameLogs($mysqli, $gameId, $logType = 'game', $playerId = null) {
    $sql = "SELECT * FROM game_logs WHERE game_id = ? AND log_type = ?";
    $params = "is";
    $values = [$gameId, $logType];
    
    if ($logType === 'player' && $playerId !== null) {
        $sql .= " AND player_id = ?";
        $params .= "i";
        $values[] = $playerId;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($params, ...$values);
    $stmt->execute();
    return $stmt->get_result();
}

// Cache management
function setGameCache($mysqli, $gameId, $key, $data) {
    $timestamp = microtime(true) * 1000;
    $stmt = $mysqli->prepare("
        INSERT INTO game_cache (game_id, cache_key, cache_data, updated_at) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE cache_data = VALUES(cache_data), updated_at = VALUES(updated_at)
    ");
    $stmt->bind_param("issi", $gameId, $key, $data, $timestamp);
    return $stmt->execute();
}

function getGameCache($mysqli, $gameId, $key, $maxAge = 3000) {
    $minTimestamp = microtime(true) * 1000 - $maxAge;
    $stmt = $mysqli->prepare("
        SELECT cache_data 
        FROM game_cache 
        WHERE game_id = ? AND cache_key = ? AND updated_at > ?
    ");
    $stmt->bind_param("isi", $gameId, $key, $minTimestamp);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['cache_data'] : null;
}

// Create new game (replaces old table creation)
function createNewGame($mysqli, $gameId) {
    $stmt = $mysqli->prepare("INSERT INTO games (id, status) VALUES (?, 'setup')");
    $stmt->bind_param("i", $gameId);
    return $stmt->execute();
}

// Add player to game
function addPlayerToGame($mysqli, $gameId, $playerNumber, $name, $isGameMaster = false, $verificationNumber = 0) {
    $stmt = $mysqli->prepare("
        INSERT INTO players (game_id, player_number, name, is_game_master, verification_number) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisii", $gameId, $playerNumber, $name, $isGameMaster, $verificationNumber);
    return $stmt->execute();
}

// Delete game and all related data (cascades automatically)
function deleteGame($mysqli, $gameId) {
    $stmt = $mysqli->prepare("DELETE FROM games WHERE id = ?");
    $stmt->bind_param("i", $gameId);
    return $stmt->execute();
}

// Set player message
function setPlayerMessage($mysqli, $playerId, $message) {
    // Clear old messages first
    $stmt = $mysqli->prepare("DELETE FROM player_messages WHERE player_id = ?");
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    
    // Add new message
    if (!empty($message)) {
        $stmt = $mysqli->prepare("INSERT INTO player_messages (player_id, message_text) VALUES (?, ?)");
        $stmt->bind_param("is", $playerId, $message);
        return $stmt->execute();
    }
    return true;
}

// Get player message
function getPlayerMessage($mysqli, $playerId) {
    $stmt = $mysqli->prepare("SELECT message_text FROM player_messages WHERE player_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['message_text'] : '';
}

// Mark player message as read
function markPlayerMessageRead($mysqli, $playerId) {
    $stmt = $mysqli->prepare("UPDATE player_messages SET is_read = 1 WHERE player_id = ?");
    $stmt->bind_param("i", $playerId);
    return $stmt->execute();
}

// Legacy compatibility functions (for gradual migration)
function getGameTableName($gameId) {
    // Return null to indicate we should use new functions
    return null;
}

function getPlayerTableName($gameId) {
    // Return null to indicate we should use new functions  
    return null;
}
?>