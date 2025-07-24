<?php 
/*

werwolfonline, a php web game - SQLite version
    Copyright (C) 2023

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

*/

// SQLite database connection as an alternative to MySQL
try {
    $pdo = new PDO('sqlite:werwolf.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create a basic games table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "SQLite database connected successfully!";
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>