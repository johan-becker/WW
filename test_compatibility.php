<?php
/*
 * COMPREHENSIVE COMPATIBILITY TEST
 * Tests all critical functions to ensure they work with new database
 */

require_once('includes/includes.php');
require_once('includes/constants.php');
require_once('includes/functions.php');

// Test configuration
$TEST_GAME_ID = 99999;
$TEST_PLAYERS = [
    ['id' => 0, 'name' => 'GameMaster', 'spielleiter' => 1],
    ['id' => 1, 'name' => 'Alice', 'spielleiter' => 0],
    ['id' => 2, 'name' => 'Bob', 'spielleiter' => 0],
    ['id' => 3, 'name' => 'Charlie', 'spielleiter' => 0]
];

function runAllTests() {
    global $mysqli, $TEST_GAME_ID, $TEST_PLAYERS;
    
    echo "<html><body><h1>🧪 Werewolf Database Compatibility Test</h1>\n";
    echo "<style>
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .test { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .summary { background: #f0f0f0; padding: 15px; margin: 20px 0; }
    </style>\n";
    
    $totalTests = 0;
    $passedTests = 0;
    
    // Cleanup any existing test data
    echo "<div class='test'><h3>🧹 Cleanup Previous Test Data</h3>\n";
    cleanupTestData();
    echo "✅ Cleanup completed</div>\n";
    
    // Test 1: Database Migration Status
    echo "<div class='test'><h3>Test 1: Database Migration Status</h3>\n";
    list($passed, $total) = testMigrationStatus();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 2: Game Creation
    echo "<div class='test'><h3>Test 2: Game Table Creation</h3>\n";
    list($passed, $total) = testGameCreation();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 3: Player Management
    echo "<div class='test'><h3>Test 3: Player Management</h3>\n";
    list($passed, $total) = testPlayerManagement();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 4: Old Query Compatibility
    echo "<div class='test'><h3>Test 4: Old Query Compatibility</h3>\n";
    list($passed, $total) = testOldQueries();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 5: Game Functions
    echo "<div class='test'><h3>Test 5: Game Functions</h3>\n";
    list($passed, $total) = testGameFunctions();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 6: Logging Functions
    echo "<div class='test'><h3>Test 6: Logging Functions</h3>\n";
    list($passed, $total) = testLoggingFunctions();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Test 7: Critical Game Mechanics
    echo "<div class='test'><h3>Test 7: Critical Game Mechanics</h3>\n";
    list($passed, $total) = testGameMechanics();
    $passedTests += $passed; $totalTests += $total;
    echo "</div>\n";
    
    // Cleanup test data
    echo "<div class='test'><h3>🧹 Final Cleanup</h3>\n";
    cleanupTestData();
    echo "✅ Test data cleaned up</div>\n";
    
    // Summary
    $percentage = round(($passedTests / $totalTests) * 100, 1);
    $summaryClass = $percentage >= 90 ? 'pass' : 'fail';
    
    echo "<div class='summary'>";
    echo "<h2>📊 Test Summary</h2>";
    echo "<p class='$summaryClass'>Passed: $passedTests / $totalTests tests ($percentage%)</p>";
    
    if ($percentage >= 95) {
        echo "<h3 class='pass'>🎉 EXCELLENT! Migration is fully compatible!</h3>";
        echo "<p>Your werewolf game will work perfectly with the new database.</p>";
    } elseif ($percentage >= 80) {
        echo "<h3 style='color: orange'>⚠️ GOOD! Minor issues detected.</h3>";
        echo "<p>Most functions work, but check the failed tests above.</p>";
    } else {
        echo "<h3 class='fail'>❌ CRITICAL ISSUES! Do not migrate yet.</h3>";
        echo "<p>Several core functions are broken. Review the code before migration.</p>";
    }
    echo "</div>";
    
    echo "</body></html>";
}

function testResult($testName, $passed, $details = '') {
    $class = $passed ? 'pass' : 'fail';
    $symbol = $passed ? '✅' : '❌';
    echo "<p class='$class'>$symbol $testName</p>";
    if ($details) {
        echo "<p style='margin-left: 20px; font-size: 0.9em;'>$details</p>";
    }
    return $passed ? 1 : 0;
}

function testMigrationStatus() {
    global $mysqli;
    $passed = 0;
    $total = 0;
    
    // Check if new tables exist
    $total++;
    $result = $mysqli->query("SHOW TABLES LIKE 'games'");
    $passed += testResult("New 'games' table exists", $result && $result->num_rows > 0);
    
    $total++;
    $result = $mysqli->query("SHOW TABLES LIKE 'players'");
    $passed += testResult("New 'players' table exists", $result && $result->num_rows > 0);
    
    $total++;
    $passed += testResult("Compatibility layer loaded", isUsingNewDatabase());
    
    return [$passed, $total];
}

function testGameCreation() {
    global $mysqli, $TEST_GAME_ID;
    $passed = 0;
    $total = 0;
    
    // Test old-style table creation query
    $total++;
    $createQuery = "CREATE TABLE `{$TEST_GAME_ID}_game` (spielphase INT DEFAULT 0)";
    $result = $mysqli->Query($createQuery);
    $passed += testResult("Old game table creation query", $result !== false);
    
    $total++;
    $createQuery = "CREATE TABLE `{$TEST_GAME_ID}_spieler` (id INT, name VARCHAR(150))";
    $result = $mysqli->Query($createQuery);
    $passed += testResult("Old player table creation query", $result !== false);
    
    // Test if game actually exists in new structure
    $total++;
    $game = getGame($mysqli, $TEST_GAME_ID);
    $passed += testResult("Game exists in new structure", $game !== null);
    
    return [$passed, $total];
}

function testPlayerManagement() {
    global $mysqli, $TEST_GAME_ID, $TEST_PLAYERS;
    $passed = 0;
    $total = 0;
    
    // Add players using old-style INSERT
    foreach ($TEST_PLAYERS as $player) {
        $total++;
        $insertQuery = "INSERT INTO `{$TEST_GAME_ID}_spieler` (id, name, spielleiter, lebt, verifizierungsnr) 
                       VALUES ({$player['id']}, '{$player['name']}', {$player['spielleiter']}, 1, 12345)";
        $result = $mysqli->Query($insertQuery);
        $passed += testResult("Insert player '{$player['name']}'", $result !== false);
    }
    
    // Test old-style SELECT
    $total++;
    $selectQuery = "SELECT * FROM `{$TEST_GAME_ID}_spieler` WHERE lebt = 1";
    $result = $mysqli->Query($selectQuery);
    $passed += testResult("Select living players", $result && $result->num_rows == count($TEST_PLAYERS));
    
    // Test getName function
    $total++;
    $name = getName($mysqli, 1, $TEST_GAME_ID);
    $passed += testResult("getName function", $name === 'Alice', "Expected: Alice, Got: $name");
    
    return [$passed, $total];
}

function testOldQueries() {
    global $mysqli, $TEST_GAME_ID;
    $passed = 0;
    $total = 0;
    
    // Test various old query patterns
    $queries = [
        "SELECT * FROM `{$TEST_GAME_ID}_game`",
        "SELECT * FROM `{$TEST_GAME_ID}_spieler` WHERE id = 1",
        "UPDATE `{$TEST_GAME_ID}_spieler` SET bereit = 1 WHERE id = 1",
        "SELECT COUNT(*) as count FROM `{$TEST_GAME_ID}_spieler` WHERE lebt = 1"
    ];
    
    foreach ($queries as $query) {
        $total++;
        $result = $mysqli->Query($query);
        $passed += testResult("Query: " . substr($query, 0, 50) . "...", $result !== false);
    }
    
    return [$passed, $total];
}

function testGameFunctions() {
    global $mysqli, $TEST_GAME_ID;
    $passed = 0;
    $total = 0;
    
    // Test game phase updates
    $total++;
    $updateQuery = "UPDATE `{$TEST_GAME_ID}_game` SET spielphase = 2";
    $result = $mysqli->Query($updateQuery);
    $passed += testResult("Update game phase", $result !== false);
    
    // Verify update worked
    $total++;
    $game = getGame($mysqli, $TEST_GAME_ID);
    $passed += testResult("Game phase updated correctly", $game && $game['phase'] == 2);
    
    // Test player voting
    $total++;
    $voteQuery = "UPDATE `{$TEST_GAME_ID}_spieler` SET wahlAuf = 2 WHERE id = 1";
    $result = $mysqli->Query($voteQuery);
    $passed += testResult("Player voting", $result !== false);
    
    return [$passed, $total];
}

function testLoggingFunctions() {
    global $mysqli, $TEST_GAME_ID;
    $passed = 0;
    $total = 0;
    
    // Test game logging
    $total++;
    $logResult = toGameLog($mysqli, "Test game log message", $TEST_GAME_ID);
    $passed += testResult("Game logging", $logResult !== false);
    
    // Test player logging
    $total++;
    $logResult = toPlayerLog($mysqli, "Test player log message", 1, $TEST_GAME_ID);
    $passed += testResult("Player logging", $logResult !== false);
    
    // Test all player logging
    $total++;
    $logResult = toAllPlayerLog($mysqli, "Test message to all players", $TEST_GAME_ID);
    $passed += testResult("All player logging", $logResult !== false);
    
    return [$passed, $total];
}

function testGameMechanics() {
    global $mysqli, $TEST_GAME_ID;
    $passed = 0;
    $total = 0;
    
    // Test player verification
    $total++;
    $playerQuery = "SELECT * FROM `{$TEST_GAME_ID}_spieler` WHERE id = 1 AND verifizierungsnr = 12345";
    $result = $mysqli->Query($playerQuery);
    $passed += testResult("Player verification", $result && $result->num_rows > 0);
    
    // Test role assignment
    $total++;
    $roleQuery = "UPDATE `{$TEST_GAME_ID}_spieler` SET nachtIdentitaet = " . CHARWERWOLF . " WHERE id = 1";
    $result = $mysqli->Query($roleQuery);
    $passed += testResult("Role assignment", $result !== false);
    
    // Test role-based queries
    $total++;
    $werewolfQuery = "SELECT * FROM `{$TEST_GAME_ID}_spieler` WHERE nachtIdentitaet = " . CHARWERWOLF;
    $result = $mysqli->Query($werewolfQuery);
    $passed += testResult("Role-based queries", $result && $result->num_rows > 0);
    
    // Test mayor functionality
    $total++;
    $mayorQuery = "UPDATE `{$TEST_GAME_ID}_spieler` SET buergermeister = 1 WHERE id = 0";
    $result = $mysqli->Query($mayorQuery);
    $passed += testResult("Mayor assignment", $result !== false);
    
    return [$passed, $total];
}

function cleanupTestData() {
    global $mysqli, $TEST_GAME_ID;
    
    // Delete test game (cascades to all related data)
    deleteGame($mysqli, $TEST_GAME_ID);
    
    // Also try to drop old tables if they somehow got created
    $mysqli->query("DROP TABLE IF EXISTS `{$TEST_GAME_ID}_game`");
    $mysqli->query("DROP TABLE IF EXISTS `{$TEST_GAME_ID}_spieler`");
    
    echo "Test game $TEST_GAME_ID cleaned up<br>";
}

// Run tests if accessed directly
if (php_sapi_name() !== 'cli') {
    runAllTests();
} else {
    echo "Run this test in a web browser: http://yoursite.com/test_compatibility.php\n";
}
?>