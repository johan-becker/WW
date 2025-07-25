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

// Entry point for werewolf game
// Route to appropriate interface based on user agent or preferences

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$modernBrowser = strpos($userAgent, 'Chrome') !== false || 
                 strpos($userAgent, 'Firefox') !== false || 
                 strpos($userAgent, 'Safari') !== false ||
                 strpos($userAgent, 'Edge') !== false;

// Check if user specifically requested legacy mode
$forceLegacy = isset($_GET['legacy']) && $_GET['legacy'] === '1';

// Check if user has a game cookie (returning player)
$hasGameCookie = isset($_COOKIE['SpielID']) && !empty($_COOKIE['SpielID']);

if ($forceLegacy || !$modernBrowser) {
    // Use legacy interface
    header('Location: Werwolf.php');
    exit;
} elseif ($hasGameCookie) {
    // Redirect to their existing game
    $gameId = $_COOKIE['SpielID'];
    header("Location: /game/{$gameId}");
    exit;
} else {
    // Show modern lobby for new users
    header('Location: modern-lobby.html');
    exit;
}

?>