-- COGNOS 2K26 Vishleshana participation upgrade
-- Run this once in phpMyAdmin after selecting the cognos_2k26 database.
-- Use this file when accommodation_migration.sql was already run.

ALTER TABLE `registrations`
  ADD COLUMN `primary_vishleshana` TINYINT(1) NOT NULL DEFAULT 0 AFTER `accommodation_required`,
  ADD COLUMN `teammate_vishleshana` TINYINT(1) NOT NULL DEFAULT 0 AFTER `primary_vishleshana`,
  ADD COLUMN `teammate_email` VARCHAR(150) NULL AFTER `teammate_name`;

CREATE TABLE IF NOT EXISTS `registration_participants` (
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
