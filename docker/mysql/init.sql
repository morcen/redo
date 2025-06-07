-- Initialize re:do database
-- This script runs when the MySQL container starts for the first time

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `redo` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if it doesn't exist
CREATE USER IF NOT EXISTS 'redo_user'@'%' IDENTIFIED BY 'redo_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON `redo`.* TO 'redo_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the database
USE `redo`;

-- Set timezone
SET time_zone = '+00:00';

-- Optimize MySQL settings for Laravel
SET GLOBAL innodb_file_format = 'Barracuda';
SET GLOBAL innodb_file_per_table = 1;
SET GLOBAL innodb_large_prefix = 1;
