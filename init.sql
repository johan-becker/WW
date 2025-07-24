-- MySQL initialization script for Werwolf game
-- This script will be executed when the MySQL container starts

CREATE DATABASE IF NOT EXISTS werwolf;
USE werwolf;

-- The PHP application will create the game-specific tables automatically
-- This script just ensures the database exists and is ready

-- Optional: Create a basic test table to verify everything works
CREATE TABLE IF NOT EXISTS test_connection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) DEFAULT 'Werwolf database is ready!',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO test_connection (message) VALUES ('Docker MySQL setup successful!');

-- Grant privileges to ensure proper access
GRANT ALL PRIVILEGES ON werwolf.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON werwolf.* TO 'werwolf_user'@'%';
FLUSH PRIVILEGES;