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

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class GameAPI {
    private $mysqli;
    private $dbHelper;
    
    public function __construct() {
        // Database connection
        require_once('includes/includes.php');
        $this->mysqli = new mysqli($host, $user, $password, $database);
        
        if ($this->mysqli->connect_error) {
            throw new RuntimeException("Database connection failed: " . $this->mysqli->connect_error);
        }
        
        $this->mysqli->set_charset("utf8");
        $this->dbHelper = new DatabaseHelper($this->mysqli);
    }
    
    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Remove /api prefix if present
            $path = preg_replace('/^\/api/', '', $path);
            
            switch ($path) {
                case '/create-game':
                    if ($method === 'POST') {
                        return $this->createGame();
                    }
                    break;
                    
                case '/join-game':
                    if ($method === 'POST') {
                        return $this->joinGame();
                    }
                    break;
                    
                case preg_match('/^\/game\/(\d+)$/', $path, $matches) ? $path : null:
                    $gameId = $matches[1];
                    if ($method === 'GET') {
                        return $this->getGameState($gameId);
                    } elseif ($method === 'POST') {
                        return $this->updateGameState($gameId);
                    }
                    break;
                    
                case '/games':
                    if ($method === 'GET') {
                        return $this->listGames();
                    }
                    break;
                    
                default:
                    $this->sendError(404, 'Endpoint not found');
                    return;
            }
            
            $this->sendError(405, 'Method not allowed');
            
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            $this->sendError(500, 'Internal server error');
        }
    }
    
    private function createGame() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $playerName = SecurityHelper::validatePlayerName($input['playerName'] ?? 'Anonymous');
            $gameMode = in_array($input['gameMode'] ?? 'classic', ['classic', 'extended']) ? $input['gameMode'] : 'classic';
            $maxPlayers = min(max(intval($input['maxPlayers'] ?? 10), 4), _MAXPLAYERS);
            
            // Generate unique game ID
            $gameId = $this->generateGameId();
            
            // Create database tables for the game
            $this->createGameTables($gameId);
            
            // Initialize game
            $this->initializeGame($gameId, $playerName, $gameMode, $maxPlayers);
            
            // Set player cookie (for compatibility with existing system)
            setcookie('SpielID', $gameId, time() + 86400, '/');
            setcookie('SpielerID', 1, time() + 86400, '/');
            setcookie('Name', $playerName, time() + 86400, '/');
            
            $this->sendSuccess([
                'id' => $gameId,
                'playerName' => $playerName,
                'gameMode' => $gameMode,
                'maxPlayers' => $maxPlayers,
                'status' => 'created',
                'phase' => PHASESETUP,
                'url' => "/game/{$gameId}"
            ]);
            
        } catch (Exception $e) {
            error_log("Create game error: " . $e->getMessage());
            $this->sendError(400, $e->getMessage());
        }
    }
    
    private function joinGame() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $gameId = SecurityHelper::validateGameID($input['gameId'] ?? 0);
            $playerName = SecurityHelper::validatePlayerName($input['playerName'] ?? 'Anonymous');
            
            if (!$this->dbHelper->gameExists($gameId)) {
                $this->sendError(404, 'Game not found');
                return;
            }
            
            $gameData = $this->dbHelper->getGameData($gameId);
            if (!$gameData) {
                $this->sendError(404, 'Game data not found');
                return;
            }
            
            // Check if game is joinable
            if ($gameData['spielphase'] > PHASESPIELSETUP) {
                $this->sendError(400, 'Game already started');
                return;
            }
            
            // Get current players
            $players = $this->dbHelper->getPlayerData($gameId);
            $playerCount = count($players);
            
            if ($playerCount >= _MAXPLAYERS) {
                $this->sendError(400, 'Game is full');
                return;
            }
            
            // Add player to game
            $playerId = $this->addPlayerToGame($gameId, $playerName);
            
            // Set player cookies
            setcookie('SpielID', $gameId, time() + 86400, '/');
            setcookie('SpielerID', $playerId, time() + 86400, '/');
            setcookie('Name', $playerName, time() + 86400, '/');
            
            $this->sendSuccess([
                'id' => $gameId,
                'playerId' => $playerId,
                'playerName' => $playerName,
                'status' => 'joined',
                'playerCount' => $playerCount + 1,
                'phase' => intval($gameData['spielphase']),
                'url' => "/game/{$gameId}"
            ]);
            
        } catch (Exception $e) {
            error_log("Join game error: " . $e->getMessage());
            $this->sendError(400, $e->getMessage());
        }
    }
    
    private function getGameState($gameId) {
        try {
            $gameId = SecurityHelper::validateGameID($gameId);
            
            if (!$this->dbHelper->gameExists($gameId)) {
                $this->sendError(404, 'Game not found');
                return;
            }
            
            $gameData = $this->dbHelper->getGameData($gameId);
            $players = $this->dbHelper->getPlayerData($gameId);
            
            // Format response
            $response = [
                'id' => $gameId,
                'phase' => intval($gameData['spielphase']),
                'playerCount' => count($players),
                'maxPlayers' => _MAXPLAYERS,
                'status' => $this->getGameStatus($gameData['spielphase']),
                'players' => array_map(function($player) {
                    return [
                        'id' => intval($player['id']),
                        'name' => $player['name'],
                        'alive' => intval($player['lebt']) === 1,
                        'ready' => intval($player['bereit']) === 1,
                        'character' => $this->getCharacterName(intval($player['nachtIdentitaet']))
                    ];
                }, $players),
                'settings' => [
                    'werwolfCount' => intval($gameData['werwolfzahl']),
                    'characterReveal' => intval($gameData['charaktereAufdecken']) === 1,
                    'mayorHandover' => intval($gameData['buergermeisterWeitergeben']) === 1
                ]
            ];
            
            $this->sendSuccess($response);
            
        } catch (Exception $e) {
            error_log("Get game state error: " . $e->getMessage());
            $this->sendError(400, $e->getMessage());
        }
    }
    
    private function listGames() {
        try {
            $games = [];
            
            // Check games in range
            for ($i = 10000; $i <= 99999; $i++) {
                if ($this->dbHelper->gameExists($i)) {
                    $gameData = $this->dbHelper->getGameData($i);
                    if ($gameData && $gameData['spielphase'] <= PHASESPIELSETUP) {
                        $players = $this->dbHelper->getPlayerData($i);
                        
                        $games[] = [
                            'id' => $i,
                            'playerCount' => count($players),
                            'maxPlayers' => _MAXPLAYERS,
                            'phase' => intval($gameData['spielphase']),
                            'status' => $this->getGameStatus($gameData['spielphase']),
                            'joinable' => count($players) < _MAXPLAYERS
                        ];
                    }
                }
            }
            
            $this->sendSuccess(['games' => $games]);
            
        } catch (Exception $e) {
            error_log("List games error: " . $e->getMessage());
            $this->sendError(500, 'Failed to list games');
        }
    }
    
    private function generateGameId() {
        $maxAttempts = 100;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $gameId = rand(10000, 99999);
            if (!$this->dbHelper->gameExists($gameId)) {
                return $gameId;
            }
        }
        throw new RuntimeException("Could not generate unique game ID");
    }
    
    private function createGameTables($gameId) {
        $gameTable = $gameId . "_game";
        $playerTable = $gameId . "_spieler";
        
        // Create game table
        $sql = "CREATE TABLE `{$gameTable}` (
            `spielphase` int(11) NOT NULL DEFAULT 0,
            `letzterAufruf` int(11) NOT NULL,
            `buergermeisterWeitergeben` int(11) NOT NULL DEFAULT 1,
            `charaktereAufdecken` int(11) NOT NULL DEFAULT 1,
            `seherSiehtIdentitaet` int(11) NOT NULL DEFAULT 1,
            `werwolfzahl` int(11) NOT NULL DEFAULT 2,
            `hexenzahl` int(11) NOT NULL DEFAULT 1,
            `jaegerzahl` int(11) NOT NULL DEFAULT 1,
            `seherzahl` int(11) NOT NULL DEFAULT 1,
            `amorzahl` int(11) NOT NULL DEFAULT 1,
            `beschuetzerzahl` int(11) NOT NULL DEFAULT 1,
            `parErmZahl` int(11) NOT NULL DEFAULT 0,
            `lykantrophenzahl` int(11) NOT NULL DEFAULT 0,
            `spionezahl` int(11) NOT NULL DEFAULT 0,
            `idiotenzahl` int(11) NOT NULL DEFAULT 0,
            `pazifistenzahl` int(11) NOT NULL DEFAULT 0,
            `altenzahl` int(11) NOT NULL DEFAULT 0,
            `urwolfzahl` int(11) NOT NULL DEFAULT 0,
            `zufaelligeAuswahl` int(11) NOT NULL DEFAULT 0,
            `zufaelligeAuswahlBonus` int(11) NOT NULL DEFAULT 2,
            `werwolftimer1` int(11) NOT NULL DEFAULT 60,
            `werwolfzusatz1` int(11) NOT NULL DEFAULT 4,
            `werwolftimer2` int(11) NOT NULL DEFAULT 50,
            `werwolfzusatz2` int(11) NOT NULL DEFAULT 3,
            `dorftimer` int(11) NOT NULL DEFAULT 550,
            `dorfzusatz` int(11) NOT NULL DEFAULT 10,
            `dorfstichwahltimer` int(11) NOT NULL DEFAULT 200,
            `dorfstichwahlzusatz` int(11) NOT NULL DEFAULT 5,
            `inaktivzeit` int(11) NOT NULL DEFAULT 600,
            `inaktivzeitzusatz` int(11) NOT NULL DEFAULT 60
        )";
        
        if (!$this->mysqli->query($sql)) {
            throw new RuntimeException("Failed to create game table: " . $this->mysqli->error);
        }
        
        // Insert initial game data
        $insertSql = "INSERT INTO `{$gameTable}` (`letzterAufruf`) VALUES (?)";
        $stmt = $this->mysqli->prepare($insertSql);
        $currentTime = time();
        $stmt->bind_param("i", $currentTime);
        
        if (!$stmt->execute()) {
            throw new RuntimeException("Failed to initialize game: " . $stmt->error);
        }
        $stmt->close();
        
        // Create player table
        $playerSql = "CREATE TABLE `{$playerTable}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `lebt` int(11) NOT NULL DEFAULT 1,
            `nachtIdentitaet` int(11) NOT NULL DEFAULT 0,
            `bereit` int(11) NOT NULL DEFAULT 0,
            `reload` int(11) NOT NULL DEFAULT 1,
            `playerlog` text,
            `letzterAufruf` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        )";
        
        if (!$this->mysqli->query($playerSql)) {
            throw new RuntimeException("Failed to create player table: " . $this->mysqli->error);
        }
    }
    
    private function initializeGame($gameId, $hostName, $gameMode, $maxPlayers) {
        // Add host as first player
        $this->addPlayerToGame($gameId, $hostName);
    }
    
    private function addPlayerToGame($gameId, $playerName) {
        $playerTable = $gameId . "_spieler";
        
        $sql = "INSERT INTO `{$playerTable}` (`name`, `letzterAufruf`) VALUES (?, ?)";
        $stmt = $this->mysqli->prepare($sql);
        $currentTime = time();
        $stmt->bind_param("si", $playerName, $currentTime);
        
        if (!$stmt->execute()) {
            throw new RuntimeException("Failed to add player: " . $stmt->error);
        }
        
        $playerId = $this->mysqli->insert_id;
        $stmt->close();
        
        return $playerId;
    }
    
    private function getGameStatus($phase) {
        $phase = intval($phase);
        switch ($phase) {
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
    
    private function getCharacterName($charId) {
        switch ($charId) {
            case CHARKEIN: return 'none';
            case CHARDORFBEWOHNER: return 'villager';
            case CHARWERWOLF: return 'werewolf';
            case CHARSEHER: return 'seer';
            case CHARHEXE: return 'witch';
            case CHARJAEGER: return 'hunter';
            case CHARAMOR: return 'cupid';
            case CHARBESCHUETZER: return 'bodyguard';
            case CHARPARERM: return 'paranormal_investigator';
            case CHARLYKANTROPH: return 'lycan';
            case CHARSPION: return 'spy';
            case CHARMORDLUSTIGER: return 'fool';
            case CHARPAZIFIST: return 'pacifist';
            case CHARALTERMANN: return 'elder';
            case CHARURWOLF: return 'alpha_wolf';
            default: return 'unknown';
        }
    }
    
    private function sendSuccess($data) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }
    
    private function sendError($code, $message) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}

// Handle the request
try {
    $api = new GameAPI();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}

?>