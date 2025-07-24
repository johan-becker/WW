<?php
/*
 * Simple debug to test game creation
 */

echo "<h1>🐺 Simple Game Debug</h1>";

// Test if we can create a basic game
require_once('includes/includes.php');

// Create test game
$gameId = 12345;
$playerName = "TestPlayer";

echo "<h2>Creating test game...</h2>";

// Clear any existing test game
$mysqli->Query("DELETE FROM games WHERE id = $gameId");
$mysqli->Query("DELETE FROM players WHERE game_id = $gameId");

// Create game
$result1 = $mysqli->Query("INSERT INTO games (id, status, phase, spielphase, letzter_aufruf) VALUES ($gameId, 'setup', 0, 0, " . time() . ")");
echo "<p>Game creation: " . ($result1 ? "✅ Success" : "❌ Failed") . "</p>";

// Create player  
$result2 = $mysqli->Query("INSERT INTO players (game_id, player_number, name, is_game_master, is_alive, verification_number) VALUES ($gameId, 0, '$playerName', 1, 1, 12345)");
echo "<p>Player creation: " . ($result2 ? "✅ Success" : "❌ Failed") . "</p>";

// Test retrieval
$gameCheck = $mysqli->Query("SELECT * FROM games WHERE id = $gameId");
$game = $gameCheck ? $gameCheck->fetch_assoc() : null;
echo "<p>Game retrieval: " . ($game ? "✅ Found game " . $game['id'] : "❌ No game found") . "</p>";

$playerCheck = $mysqli->Query("SELECT * FROM players WHERE game_id = $gameId");  
$player = $playerCheck ? $playerCheck->fetch_assoc() : null;
echo "<p>Player retrieval: " . ($player ? "✅ Found player " . $player['name'] : "❌ No player found") . "</p>";

// Test old-style queries
echo "<h2>Testing old-style queries...</h2>";

$oldGameQuery = $mysqli->Query("SELECT * FROM {$gameId}_game");
$oldGame = $oldGameQuery ? $oldGameQuery->fetch_assoc() : null;
echo "<p>Old game query: " . ($oldGame ? "✅ Works - phase " . $oldGame['spielphase'] : "❌ Failed") . "</p>";

$oldPlayerQuery = $mysqli->Query("SELECT * FROM {$gameId}_spieler WHERE id = 0");
$oldPlayer = $oldPlayerQuery ? $oldPlayerQuery->fetch_assoc() : null; 
echo "<p>Old player query: " . ($oldPlayer ? "✅ Works - name " . $oldPlayer['name'] : "❌ Failed") . "</p>";

// Test what happens with cookies
echo "<h2>Testing Cookie Behavior...</h2>";
echo "<p>Setting test cookies...</p>";
setcookie("SpielID", $gameId, time()+3600);
setcookie("eigeneID", 0, time()+3600); 
setcookie("verifizierungsnr", 12345, time()+3600);

echo "<p>Current cookies:</p>";
echo "<ul>";
foreach ($_COOKIE as $key => $value) {
    echo "<li>$key = $value</li>";
}
echo "</ul>";

// Now test accessing the game
echo "<h2>🎮 Test Game Access:</h2>";
echo "<p><a href='Werwolf.php?game=$gameId&id=0' target='_blank'>Test Game Link</a></p>";
echo "<p>If this link shows a blank page, the issue is in Werwolf.php game display logic.</p>";

echo "<h2>Clean up</h2>";
$mysqli->Query("DELETE FROM games WHERE id = $gameId");
$mysqli->Query("DELETE FROM players WHERE game_id = $gameId");
echo "<p>✅ Test data cleaned up</p>";
?>