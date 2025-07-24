-- SQL script to create the werwolf database and basic structure
-- Run this manually in MySQL: mysql -u root -p < create_database.sql

CREATE DATABASE IF NOT EXISTS werwolf;
USE werwolf;

-- The PHP application will create the game tables automatically
-- This is just to create the basic database structure

SELECT 'Database "werwolf" created successfully!' as Status;