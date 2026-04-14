-- ============================================================
--  SWD 414 – Backend Development
--  File   : database.sql
--  Purpose: Create database and students table
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS swd414_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE swd414_db;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    matric_num  VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
