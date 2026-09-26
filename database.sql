-- ========================================================
-- COGNOS 2K26 Database Schema
-- Organized by Departments of Computer Science and Engineering(Data Science) & AI&DS
-- Tagline: "LET THE DATA SPEAK"
-- ========================================================

CREATE DATABASE IF NOT EXISTS `cognos_2k26` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `cognos_2k26`;

-- Drop existing tables if re-importing
DROP TABLE IF EXISTS `registration_events`;
DROP TABLE IF EXISTS `registration_participants`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `admin_users`;

-- 1. Main Registrations Table
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_code` VARCHAR(30) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL,
  `student_name` VARCHAR(150) NOT NULL,
  `roll_no` VARCHAR(60) NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `college_name` VARCHAR(255) NOT NULL,
  `primary_vishleshana` TINYINT(1) NOT NULL DEFAULT 0,
  `teammate_vishleshana` TINYINT(1) NOT NULL DEFAULT 0,
  `id_card_path` VARCHAR(255) DEFAULT NULL,
  `has_teammate` TINYINT(1) NOT NULL DEFAULT 0,
  `teammate_name` VARCHAR(150) DEFAULT NULL,
  `teammate_email` VARCHAR(150) DEFAULT NULL,
  `teammate_roll_no` VARCHAR(60) DEFAULT NULL,
  `teammate_branch` VARCHAR(100) DEFAULT NULL,
  `teammate_college` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_roll_no` (`roll_no`),
  INDEX `idx_reg_code` (`reg_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Individual participant records used for certificates and Vishleshana
CREATE TABLE `registration_participants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `registration_id` INT NOT NULL,
  `participant_type` ENUM('primary', 'teammate') NOT NULL,
  `participant_name` VARCHAR(150) NOT NULL,
  `participant_email` VARCHAR(150) NOT NULL,
  `roll_no` VARCHAR(60) NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `college_name` VARCHAR(255) NOT NULL,
  `participates_vishleshana` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_registration_participant` (`registration_id`, `participant_type`),
  INDEX `idx_participant_email` (`participant_email`),
  INDEX `idx_vishleshana_participants` (`participates_vishleshana`),
  CONSTRAINT `fk_participant_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Registration Events Junction Table
CREATE TABLE `registration_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `registration_id` INT NOT NULL,
  `event_name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`registration_id`) REFERENCES `registrations`(`id`) ON DELETE CASCADE,
  INDEX `idx_event_name` (`event_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Admin Users Table
CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (Username: admin, Password: admin@cognos2026)
INSERT INTO `admin_users` (`username`, `password_hash`) 
VALUES ('admin', '$2y$10$s6z41iHTpaO9b10yiOzyO.UMJxDGlLxREyZ6ZMY07Rm601.hsPHg.');
