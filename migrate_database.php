<?php
/*
 * Database Migration Script
 * Migrates from table-per-game to scalable single-table design
 */

require_once('includes/includes.php');
require_once('includes/constants.php');

// Configuration
$BACKUP_DIR = 'backups/';
$LOG_FILE = 'migration.log';

function logMessage($message) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($LOG_FILE, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

function createBackupDir() {
    global $BACKUP_DIR;
    if (!is_dir($BACKUP_DIR)) {
        mkdir($BACKUP_DIR, 0755, true);
    }
}

function backupExistingData($mysqli) {
    global $BACKUP_DIR;
    logMessage("Starting data backup...");
    
    // Find all existing game tables
    $tables = [];
    $result = $mysqli->query("SHOW TABLES LIKE '%_game'");
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        $gameId = str_replace('_game', '', $table);
        $tables[] = $gameId;
    }
    
    logMessage("Found " . count($tables) . " existing games to backup");
    
    // Backup each game's data
    foreach ($tables as $gameId) {
        backupGameData($mysqli, $gameId);
    }
    
    logMessage("Backup completed");
    return $tables;
}

function backupGameData($mysqli, $gameId) {
    global $BACKUP_DIR;
    
    $gameTable = $gameId . '_game';
    $playerTable = $gameId . '_spieler';
    
    // Backup game data
    $gameResult = $mysqli->query("SELECT * FROM `$gameTable`");
    if ($gameResult && $gameResult->num_rows > 0) {
        $gameData = $gameResult->fetch_assoc();
        file_put_contents($BACKUP_DIR . "game_$gameId.json", json_encode($gameData, JSON_PRETTY_PRINT));
    }
    
    // Backup player data
    $playerResult = $mysqli->query("SELECT * FROM `$playerTable`");
    if ($playerResult && $playerResult->num_rows > 0) {
        $players = [];
        while ($row = $playerResult->fetch_assoc()) {
            $players[] = $row;
        }
        file_put_contents($BACKUP_DIR . "players_$gameId.json", json_encode($players, JSON_PRETTY_PRINT));
    }
}

function createNewTables($mysqli) {
    logMessage("Creating new scalable tables...");
    
    // Read and execute the new schema
    $sql = file_get_contents('database_migration.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        if (!$mysqli->query($statement)) {
            logMessage("ERROR: " . $mysqli->error . " in statement: " . substr($statement, 0, 100) . "...");
            return false;
        }
    }
    
    logMessage("New tables created successfully");
    return true;
}

function migrateGameData($mysqli, $gameIds) {
    logMessage("Starting data migration...");
    
    foreach ($gameIds as $gameId) {
        migrateGame($mysqli, $gameId);
    }
    
    logMessage("Data migration completed");
}

function migrateGame($mysqli, $gameId) {
    logMessage("Migrating game $gameId...");
    
    $gameTable = $gameId . '_game';
    $playerTable = $gameId . '_spieler';
    
    try {
        // Migrate game data
        $gameResult = $mysqli->query("SELECT * FROM `$gameTable`");
        if ($gameResult && $gameResult->num_rows > 0) {
            $gameData = $gameResult->fetch_assoc();
            insertGameData($mysqli, $gameId, $gameData);
        }
        
        // Migrate player data
        $playerResult = $mysqli->query("SELECT * FROM `$playerTable`");
        if ($playerResult && $playerResult->num_rows > 0) {
            while ($playerData = $playerResult->fetch_assoc()) {
                insertPlayerData($mysqli, $gameId, $playerData);
            }
        }
        
        // Migrate cached lists if they exist
        if (isset($gameData['list_lebe']) && !empty($gameData['list_lebe'])) {
            insertCacheData($mysqli, $gameId, 'list_alive', $gameData['list_lebe'], $gameData['list_lebe_aktualisiert'] ?? 0);
        }
        
        if (isset($gameData['list_tot']) && !empty($gameData['list_tot'])) {
            insertCacheData($mysqli, $gameId, 'list_dead', $gameData['list_tot'], $gameData['list_tot_aktualisiert'] ?? 0);
        }
        
        logMessage("Game $gameId migrated successfully");
        
    } catch (Exception $e) {
        logMessage("ERROR migrating game $gameId: " . $e->getMessage());
    }
}

function insertGameData($mysqli, $gameId, $data) {
    $stmt = $mysqli->prepare("
        INSERT INTO games (
            id, phase, night_number, charaktere_aufdecken, buergermeister_weitergeben,
            seher_sieht_identitaet, zufaellige_auswahl, zufaellige_auswahl_bonus,
            werwolfe_einstimmig, werwolf_opfer, werwolf_count, hexe_count, seher_count,
            jaeger_count, amor_count, beschuetzer_count, par_erm_count, lykantrophen_count,
            spione_count, idioten_count, pazifisten_count, alten_count, urwolf_count,
            werwolf_timer1, werwolf_zusatz1, werwolf_timer2, werwolf_zusatz2,
            dorf_timer, dorf_zusatz, dorf_stichwahl_timer, dorf_stichwahl_zusatz,
            inaktiv_zeit, inaktiv_zeit_zusatz, tages_text, waiting_for_others_time,
            letzter_aufruf
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii",
        $gameId,
        $data['spielphase'] ?? 0,
        $data['nacht'] ?? 1,
        $data['charaktereAufdecken'] ?? 0,
        $data['buergermeisterWeitergeben'] ?? 0,
        $data['seherSiehtIdentitaet'] ?? 1,
        $data['zufaelligeAuswahl'] ?? 0,
        $data['zufaelligeAuswahlBonus'] ?? 0,
        $data['werwolfeinstimmig'] ?? 1,
        $data['werwolfopfer'] ?? -1,
        $data['werwolfzahl'] ?? 0,
        $data['hexenzahl'] ?? 0,
        $data['seherzahl'] ?? 0,
        $data['jaegerzahl'] ?? 0,
        $data['amorzahl'] ?? 0,
        $data['beschuetzerzahl'] ?? 0,
        $data['parErmZahl'] ?? 0,
        $data['lykantrophenzahl'] ?? 0,
        $data['spionezahl'] ?? 0,
        $data['idiotenzahl'] ?? 0,
        $data['pazifistenzahl'] ?? 0,
        $data['altenzahl'] ?? 0,
        $data['urwolfzahl'] ?? 0,
        $data['werwolftimer1'] ?? 60,
        $data['werwolfzusatz1'] ?? 4,
        $data['werwolftimer2'] ?? 50,
        $data['werwolfzusatz2'] ?? 3,
        $data['dorftimer'] ?? 550,
        $data['dorfzusatz'] ?? 10,
        $data['dorfstichwahltimer'] ?? 200,
        $data['dorfstichwahlzusatz'] ?? 5,
        $data['inaktivzeit'] ?? 40,
        $data['inaktivzeitzusatz'] ?? 0,
        $data['waiting_for_others_time'] ?? 0,
        $data['letzterAufruf'] ?? 0
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert game data: " . $stmt->error);
    }
    
    // Insert game log if exists
    if (!empty($data['log'])) {
        insertLogData($mysqli, $gameId, 'game', null, $data['log']);
    }
    
    // Insert tages_text separately if needed
    if (!empty($data['tagestext'])) {
        $mysqli->query("UPDATE games SET tages_text = '" . $mysqli->real_escape_string($data['tagestext']) . "' WHERE id = $gameId");
    }
}

function insertPlayerData($mysqli, $gameId, $data) {
    $stmt = $mysqli->prepare("
        INSERT INTO players (
            game_id, player_number, name, is_alive, is_game_master, is_ready,
            needs_reload, died_this_night, night_identity, vote_target, accused_by,
            is_mayor, verliebt_mit, hexe_heiltraenke, hexe_todestraenke, hexe_opfer,
            hexe_heilt, beschuetzer_letzte_runde_beschuetzt, par_erm_eingesetzt,
            jaeger_darf_schiessen, buergermeister_darf_weitergeben, urwolf_anzahl_faehigkeiten,
            countdown_bis, countdown_ab, verification_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iisiiiiiiiiiiiiiiiiiiiiii",
        $gameId,
        $data['id'] ?? 0,
        $data['name'] ?? '',
        $data['lebt'] ?? 1,
        $data['spielleiter'] ?? 0,
        $data['bereit'] ?? 0,
        $data['reload'] ?? 0,
        $data['dieseNachtGestorben'] ?? 0,
        $data['nachtIdentitaet'] ?? 0,
        $data['wahlAuf'] ?? -1,
        $data['angeklagtVon'] ?? -1,
        $data['buergermeister'] ?? 0,
        $data['verliebtMit'] ?? -1,
        $data['hexeHeiltraenke'] ?? 0,
        $data['hexeTodestraenke'] ?? 0,
        $data['hexenOpfer'] ?? -1,
        $data['hexeHeilt'] ?? 0,
        $data['beschuetzerLetzteRundeBeschuetzt'] ?? -1,
        $data['parErmEingesetzt'] ?? 0,
        $data['jaegerDarfSchiessen'] ?? 0,
        $data['buergermeisterDarfWeitergeben'] ?? 0,
        $data['urwolf_anzahl_faehigkeiten'] ?? 0,
        $data['countdownBis'] ?? 0,
        $data['countdownAb'] ?? 0,
        $data['verifizierungsnr'] ?? 0
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert player data: " . $stmt->error);
    }
    
    $playerId = $mysqli->insert_id;
    
    // Insert player log if exists
    if (!empty($data['playerlog'])) {
        insertLogData($mysqli, $gameId, 'player', $playerId, $data['playerlog']);
    }
    
    // Insert popup message if exists
    if (!empty($data['popup_text'])) {
        $stmt = $mysqli->prepare("INSERT INTO player_messages (player_id, message_text) VALUES (?, ?)");
        $stmt->bind_param("is", $playerId, $data['popup_text']);
        $stmt->execute();
    }
}

function insertLogData($mysqli, $gameId, $type, $playerId, $content) {
    $stmt = $mysqli->prepare("INSERT INTO game_logs (game_id, log_type, player_id, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $gameId, $type, $playerId, $content);
    $stmt->execute();
}

function insertCacheData($mysqli, $gameId, $key, $data, $timestamp) {
    $stmt = $mysqli->prepare("INSERT INTO game_cache (game_id, cache_key, cache_data, updated_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $gameId, $key, $data, $timestamp);
    $stmt->execute();
}

function cleanupOldTables($mysqli, $gameIds) {
    logMessage("Cleaning up old tables...");
    
    foreach ($gameIds as $gameId) {
        $gameTable = $gameId . '_game';
        $playerTable = $gameId . '_spieler';
        
        $mysqli->query("DROP TABLE IF EXISTS `$gameTable`");
        $mysqli->query("DROP TABLE IF EXISTS `$playerTable`");
    }
    
    logMessage("Old tables cleaned up");
}

// Main migration process
function runMigration() {
    global $mysqli;
    
    logMessage("=== Starting Database Migration ===");
    
    try {
        createBackupDir();
        
        // Step 1: Backup existing data
        $gameIds = backupExistingData($mysqli);
        
        // Step 2: Create new tables
        if (!createNewTables($mysqli)) {
            throw new Exception("Failed to create new tables");
        }
        
        // Step 3: Migrate data
        migrateGameData($mysqli, $gameIds);
        
        // Step 4: Cleanup (commented out for safety)
        // cleanupOldTables($mysqli, $gameIds);
        
        logMessage("=== Migration Completed Successfully ===");
        logMessage("IMPORTANT: Test the new system before running cleanup!");
        logMessage("To cleanup old tables, uncomment the cleanupOldTables call");
        
    } catch (Exception $e) {
        logMessage("MIGRATION FAILED: " . $e->getMessage());
        return false;
    }
    
    return true;
}

// Run migration if called directly
if (php_sapi_name() === 'cli' || isset($_GET['migrate'])) {
    if (isset($_GET['migrate']) && $_GET['migrate'] !== 'confirm') {
        echo "<h1>Database Migration</h1>";
        echo "<p><strong>WARNING:</strong> This will migrate your database structure!</p>";
        echo "<p>Make sure you have backups before proceeding.</p>";
        echo "<p><a href='?migrate=confirm' style='background: red; color: white; padding: 10px; text-decoration: none;'>CONFIRM MIGRATION</a></p>";
    } else {
        runMigration();
    }
}
?>