<?php
/*
 * Debug script to test game creation
 */

require_once('includes/includes.php');
require_once('includes/constants.php');

echo "<h1>🐺 Game Creation Debug</h1>";

// Test game creation
$testGameId = rand(10000,99999);
$testPlayerName = "TestPlayer";

echo "<h2>Testing Game Creation Process:</h2>";

echo "<p>1. Testing old-style table creation...</p>";
$createResult = $mysqli->Query("CREATE TABLE `{$testGameId}_game` (spielphase INT DEFAULT 0)");
echo "<p>✅ Table creation result: " . ($createResult ? "Success" : "Failed") . "</p>";

echo "<p>2. Testing game insertion into new format...</p>";
$gameInsertResult = $mysqli->Query("INSERT INTO games (id, status) VALUES ($testGameId, 'setup')");
echo "<p>✅ Game insert result: " . ($gameInsertResult ? "Success" : "Failed") . "</p>";

echo "<p>3. Testing player insertion...</p>";
$playerInsertResult = $mysqli->Query("INSERT INTO players (game_id, player_number, name, is_game_master) VALUES ($testGameId, 0, '$testPlayerName', 1)");
echo "<p>✅ Player insert result: " . ($playerInsertResult ? "Success" : "Failed") . "</p>";

echo "<p>4. Testing game retrieval...</p>";
$gameRetrieveResult = $mysqli->Query("SELECT * FROM games WHERE id = $testGameId");
$gameData = $gameRetrieveResult ? $gameRetrieveResult->fetch_assoc() : null;
echo "<p>✅ Game retrieve result: " . ($gameData ? "Found game ID " . $gameData['id'] : "No game found") . "</p>";

echo "<p>5. Testing player retrieval...</p>";
$playerRetrieveResult = $mysqli->Query("SELECT * FROM players WHERE game_id = $testGameId");
$playerData = $playerRetrieveResult ? $playerRetrieveResult->fetch_assoc() : null;
echo "<p>✅ Player retrieve result: " . ($playerData ? "Found player " . $playerData['name'] : "No player found") . "</p>";

// Clean up test data
$mysqli->Query("DELETE FROM games WHERE id = $testGameId");
$mysqli->Query("DELETE FROM players WHERE game_id = $testGameId");

echo "<h2>🎮 Try Real Game Creation:</h2>";
echo "<form method='POST'>";
echo "<p>Your Name: <input type='text' name='testName' value='YourName'></p>";
echo "<p><input type='submit' name='createTest' value='Create Test Game'></p>";
echo "</form>";

if (isset($_POST['createTest']) && !empty($_POST['testName'])) {
    $playerName = $_POST['testName'];
    $gameId = rand(10000,99999);
    
    echo "<h3>Creating Real Game...</h3>";
    
    // Insert game
    $mysqli->Query("INSERT INTO games (id, status, phase) VALUES ($gameId, 'setup', 0)");
    
    // Insert player
    $mysqli->Query("INSERT INTO players (game_id, player_number, name, is_game_master, is_alive, verification_number) VALUES ($gameId, 0, '$playerName', 1, 1, 12345)");
    
    echo "<p>✅ Game created with ID: <strong>$gameId</strong></p>";
    echo "<p>🎮 <a href='Werwolf.php?game=$gameId&id=0' target='_blank'>Play Game</a></p>";
}

echo "<h2>📊 Current Database Contents:</h2>";
echo "<h3>Games:</h3>";
$gamesResult = $mysqli->Query("SELECT * FROM games ORDER BY id DESC LIMIT 5");
if ($gamesResult && $gamesResult->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Status</th><th>Phase</th><th>Created</th></tr>";
    while ($game = $gamesResult->fetch_assoc()) {
        echo "<tr><td>{$game['id']}</td><td>{$game['status']}</td><td>{$game['phase']}</td><td>{$game['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No games found</p>";
}

echo "<h3>Players:</h3>";
$playersResult = $mysqli->Query("SELECT * FROM players ORDER BY id DESC LIMIT 10");
if ($playersResult && $playersResult->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Game ID</th><th>Player #</th><th>Name</th><th>Game Master</th></tr>";
    while ($player = $playersResult->fetch_assoc()) {
        echo "<tr><td>{$player['id']}</td><td>{$player['game_id']}</td><td>{$player['player_number']}</td><td>{$player['name']}</td><td>{$player['is_game_master']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No players found</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>