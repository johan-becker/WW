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

require_once('includes/functions.php');
require_once('includes/security.php');

// Initialize error handling
ErrorHandler::init();

try {
    // Get game ID from URL parameter
    $gameId = SecurityHelper::validateGameID($_GET['id'] ?? 0);
    
    // Check if user has access to this game (through cookies)
    $userGameId = isset($_COOKIE['SpielID']) ? SecurityHelper::validateGameID($_COOKIE['SpielID']) : null;
    $userPlayerId = isset($_COOKIE['SpielerID']) ? SecurityHelper::validatePlayerID($_COOKIE['SpielerID']) : null;
    $userName = isset($_COOKIE['Name']) ? SecurityHelper::validatePlayerName($_COOKIE['Name']) : null;
    
    // Connect to database
    require_once('includes/includes.php');
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        throw new RuntimeException("Database connection failed");
    }
    
    $mysqli->set_charset("utf8");
    $dbHelper = new DatabaseHelper($mysqli);
    
    // Check if game exists
    if (!$dbHelper->gameExists($gameId)) {
        throw new RuntimeException("Game not found");
    }
    
    // Get game data
    $gameData = $dbHelper->getGameData($gameId);
    $players = $dbHelper->getPlayerData($gameId);
    
    // Determine if user is in this game
    $isInGame = false;
    $currentPlayer = null;
    
    if ($userGameId === $gameId && $userPlayerId) {
        foreach ($players as $player) {
            if (intval($player['id']) === $userPlayerId) {
                $isInGame = true;
                $currentPlayer = $player;
                break;
            }
        }
    }
    
    // Clean up old games
    loescheAlteSpiele($mysqli);
    
} catch (Exception $e) {
    error_log("Game page error: " . $e->getMessage());
    // Redirect to home page on error
    header('Location: /');
    exit();
}

?>
<!DOCTYPE html>
<html lang="de" data-theme="dark">
<head>
    <title>🐺 Werwölfe - Spiel <?= htmlspecialchars($gameId) ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Expires" content="Sat, 01 Dec 2001 00:00:00 GMT">
    <meta name="description" content="Werwolf Online - Spiel <?= htmlspecialchars($gameId) ?>">
    
    <link rel="stylesheet" type="text/css" href="/style.css">
    <link rel="stylesheet" type="text/css" href="/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Cinzel:wght@400;600&family=Crimson+Text:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .game-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .game-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .game-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .status-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
        }
        
        .phase-indicator {
            font-size: 1.2em;
            font-weight: 600;
            color: #4ecdc4;
        }
        
        .player-count {
            color: #ffd93d;
        }
        
        .game-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .game-content {
                grid-template-columns: 1fr;
            }
        }
        
        .main-game-area {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-height: 500px;
        }
        
        .sidebar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .player-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .player-item {
            background: rgba(255, 255, 255, 0.1);
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .player-name {
            font-weight: 500;
            color: white;
        }
        
        .player-status {
            display: flex;
            gap: 5px;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .status-alive {
            background: #4caf50;
            color: white;
        }
        
        .status-dead {
            background: #f44336;
            color: white;
        }
        
        .status-ready {
            background: #2196f3;
            color: white;
        }
        
        .join-game-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .join-game-form input {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
        }
        
        .join-game-form input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }
        
        .error-message {
            background: rgba(244, 67, 54, 0.2);
            color: #ff5252;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #4ecdc4;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1 style="margin: 0; color: white; font-family: 'Cinzel', serif;">
                🐺 Werwölfe Online - Spiel #<?= htmlspecialchars($gameId) ?>
            </h1>
            <div class="game-status">
                <div class="status-item phase-indicator">
                    Phase: <?= ucfirst(str_replace('_', ' ', getGameStatusText($gameData['spielphase']))) ?>
                </div>
                <div class="status-item player-count">
                    Spieler: <?= count($players) ?>/<?= _MAXPLAYERS ?>
                </div>
                <div class="status-item">
                    ID: #<?= htmlspecialchars($gameId) ?>
                </div>
            </div>
        </div>
        
        <?php if (!$isInGame): ?>
            <!-- User is not in the game - show join form -->
            <div class="join-game-form">
                <h2 style="color: white; margin-bottom: 20px;">Spiel beitreten</h2>
                <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 30px;">
                    Tritt diesem Werwolf-Spiel bei und erlebe spannende Nächte!
                </p>
                
                <form id="joinGameForm">
                    <input type="hidden" id="gameId" value="<?= htmlspecialchars($gameId) ?>">
                    <input type="text" id="playerName" placeholder="Dein Name" required maxlength="50">
                    <br>
                    <button type="submit" class="btn">Spiel beitreten</button>
                </form>
                
                <div id="joinMessage"></div>
                
                <div style="margin-top: 30px;">
                    <a href="/" class="btn" style="background: rgba(255, 255, 255, 0.2);">
                        ← Zurück zur Startseite
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- User is in the game - show game interface -->
            <div class="game-content">
                <div class="main-game-area">
                    <div id="gameInterface">
                        <div class="loading">
                            <div class="spinner"></div>
                            <p>Spiel wird geladen...</p>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar">
                    <h3 style="color: white; margin-top: 0;">Spieler</h3>
                    <ul class="player-list">
                        <?php foreach ($players as $player): ?>
                            <li class="player-item <?= intval($player['id']) === $userPlayerId ? 'current-player' : '' ?>">
                                <span class="player-name">
                                    <?= htmlspecialchars($player['name']) ?>
                                    <?= intval($player['id']) === $userPlayerId ? ' (Du)' : '' ?>
                                </span>
                                <div class="player-status">
                                    <span class="status-badge <?= intval($player['lebt']) === 1 ? 'status-alive' : 'status-dead' ?>">
                                        <?= intval($player['lebt']) === 1 ? 'Lebendig' : 'Tot' ?>
                                    </span>
                                    <?php if (intval($player['bereit']) === 1): ?>
                                        <span class="status-badge status-ready">Bereit</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div style="margin-top: 20px;">
                        <button id="toggleLegacyMode" class="btn" style="width: 100%;">
                            🔄 Legacy-Modus
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Game configuration
        const gameConfig = {
            gameId: <?= json_encode($gameId) ?>,
            isInGame: <?= json_encode($isInGame) ?>,
            currentPlayer: <?= json_encode($currentPlayer) ?>,
            gameData: <?= json_encode($gameData) ?>,
            players: <?= json_encode($players) ?>
        };
        
        // Join game functionality
        if (!gameConfig.isInGame) {
            document.getElementById('joinGameForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const playerName = document.getElementById('playerName').value.trim();
                const messageDiv = document.getElementById('joinMessage');
                
                if (!playerName) {
                    messageDiv.innerHTML = '<div class="error-message">Bitte gib deinen Namen ein.</div>';
                    return;
                }
                
                try {
                    messageDiv.innerHTML = '<div class="loading"><div class="spinner"></div><p>Trete Spiel bei...</p></div>';
                    
                    const response = await fetch('/api/join-game', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            gameId: gameConfig.gameId,
                            playerName: playerName
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        messageDiv.innerHTML = '<div class="success-message">Erfolgreich beigetreten! Lade Spiel neu...</div>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        messageDiv.innerHTML = `<div class="error-message">${result.error}</div>`;
                    }
                } catch (error) {
                    console.error('Join game error:', error);
                    messageDiv.innerHTML = '<div class="error-message">Fehler beim Beitreten. Bitte versuche es erneut.</div>';
                }
            });
        }
        
        // Game interface functionality
        if (gameConfig.isInGame) {
            let gameState = null;
            let updateInterval = null;
            
            // Load game interface
            async function loadGameInterface() {
                try {
                    const response = await fetch(`/api/game/${gameConfig.gameId}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        gameState = result.data;
                        updateGameInterface();
                    } else {
                        document.getElementById('gameInterface').innerHTML = 
                            `<div class="error-message">Fehler beim Laden: ${result.error}</div>`;
                    }
                } catch (error) {
                    console.error('Load game error:', error);
                    document.getElementById('gameInterface').innerHTML = 
                        '<div class="error-message">Fehler beim Laden des Spiels.</div>';
                }
            }
            
            // Update game interface based on current state
            function updateGameInterface() {
                const gameInterface = document.getElementById('gameInterface');
                
                if (!gameState) return;
                
                let html = `
                    <div style="margin-bottom: 20px;">
                        <h3 style="color: white;">Aktuelle Spielphase: ${getPhaseDisplayName(gameState.status)}</h3>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Spieler: ${gameState.playerCount}/${gameState.maxPlayers}
                        </p>
                    </div>
                `;
                
                // Add phase-specific content
                switch (gameState.status) {
                    case 'setup':
                    case 'game_setup':
                        html += getSetupPhaseHTML();
                        break;
                    case 'night':
                        html += getNightPhaseHTML();
                        break;
                    case 'discussion':
                    case 'voting':
                        html += getDayPhaseHTML();
                        break;
                    case 'victory':
                        html += getVictoryPhaseHTML();
                        break;
                    default:
                        html += getDefaultPhaseHTML();
                }
                
                gameInterface.innerHTML = html;
            }
            
            // Phase-specific HTML generators
            function getSetupPhaseHTML() {
                return `
                    <div class="phase-content">
                        <h4 style="color: #4ecdc4;">Spiel-Setup</h4>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Das Spiel wird vorbereitet. Warten auf weitere Spieler...
                        </p>
                        <div style="margin-top: 20px;">
                            <button class="btn" onclick="setReady()">Bereit</button>
                        </div>
                    </div>
                `;
            }
            
            function getNightPhaseHTML() {
                return `
                    <div class="phase-content">
                        <h4 style="color: #2c3e50;">🌙 Nachtphase</h4>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Die Nacht bricht herein. Die Werwölfe erwachen...
                        </p>
                        <div class="night-actions">
                            <!-- Night actions will be loaded here based on player role -->
                        </div>
                    </div>
                `;
            }
            
            function getDayPhaseHTML() {
                return `
                    <div class="phase-content">
                        <h4 style="color: #f39c12;">☀️ Tagphase</h4>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Die Sonne geht auf. Zeit für Diskussionen und Abstimmungen.
                        </p>
                        <div class="day-actions">
                            <!-- Day actions will be loaded here -->
                        </div>
                    </div>
                `;
            }
            
            function getVictoryPhaseHTML() {
                return `
                    <div class="phase-content">
                        <h4 style="color: #27ae60;">🎉 Spiel beendet</h4>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Das Spiel ist zu Ende!
                        </p>
                    </div>
                `;
            }
            
            function getDefaultPhaseHTML() {
                return `
                    <div class="phase-content">
                        <h4 style="color: white;">Spielphase</h4>
                        <p style="color: rgba(255, 255, 255, 0.8);">
                            Das Spiel läuft...
                        </p>
                    </div>
                `;
            }
            
            function getPhaseDisplayName(status) {
                const phaseNames = {
                    'setup': 'Vorbereitung',
                    'game_setup': 'Spiel-Setup',
                    'night_start': 'Nacht beginnt',
                    'night': 'Nachtphase',
                    'night_end': 'Nacht endet',
                    'announce_dead': 'Tote werden bekannt gegeben',
                    'mayor_election': 'Bürgermeisterwahl',
                    'discussion': 'Diskussion',
                    'accusations': 'Anschuldigungen',
                    'voting': 'Abstimmung',
                    'runoff': 'Stichwahl',
                    'post_voting': 'Nach der Abstimmung',
                    'victory': 'Siegesfeier'
                };
                return phaseNames[status] || status;
            }
            
            // Actions
            function setReady() {
                // Implementation for setting player ready status
                console.log('Setting player ready...');
            }
            
            // Initialize game interface
            loadGameInterface();
            
            // Set up periodic updates
            updateInterval = setInterval(loadGameInterface, 3000);
        }
        
        // Legacy mode toggle
        document.getElementById('toggleLegacyMode')?.addEventListener('click', function() {
            window.location.href = '/Werwolf.php';
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>
</body>
</html>

<?php

function getGameStatusText($phase) {
    switch (intval($phase)) {
        case PHASESETUP: return 'setup';
        case PHASESPIELSETUP: return 'game_setup';
        case PHASENACHTBEGINN: return 'night_start';
        case PHASENACHT1:
        case PHASENACHT2:
        case PHASENACHT3:
        case PHASENACHT4:
        case PHASENACHT5: return 'night';
        case PHASENACHTENDE: return 'night_end';
        case PHASETOTEBEKANNTGEBEN: return 'announce_dead';
        case PHASEBUERGERMEISTERWAHL: return 'mayor_election';
        case PHASEDISKUSSION: return 'discussion';
        case PHASEANKLAGEN: return 'accusations';
        case PHASEABSTIMMUNG: return 'voting';
        case PHASESTICHWAHL: return 'runoff';
        case PHASENACHABSTIMMUNG: return 'post_voting';
        case PHASESIEGEREHRUNG: return 'victory';
        default: return 'unknown';
    }
}

?>