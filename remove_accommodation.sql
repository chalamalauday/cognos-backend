-- ========================================================
-- COGNOS 2K26 - Remove Accommodation System Migration
-- ========================================================
-- Run this script once in MySQL / phpMyAdmin on your database.

USE `cognos_2k26`;

-- 1. Drop accommodation index if it exists
SET @exist := (
    SELECT COUNT(*) 
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
      AND table_name = 'registrations' 
      AND index_name = 'idx_accommodation'
);
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `registrations` DROP INDEX `idx_accommodation`', 'SELECT "Index idx_accommodation does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Drop gender column if exists
SET @exist := (
    SELECT COUNT(*) 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
      AND table_name = 'registrations' 
      AND column_name = 'gender'
);
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `registrations` DROP COLUMN `gender`', 'SELECT "Column gender does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Drop distance_from_college_km column if exists
SET @exist := (
    SELECT COUNT(*) 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
      AND table_name = 'registrations' 
      AND column_name = 'distance_from_college_km'
);
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `registrations` DROP COLUMN `distance_from_college_km`', 'SELECT "Column distance_from_college_km does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Drop accommodation_required column if exists
SET @exist := (
    SELECT COUNT(*) 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
      AND table_name = 'registrations' 
      AND column_name = 'accommodation_required'
);
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE `registrations` DROP COLUMN `accommodation_required`', 'SELECT "Column accommodation_required does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Done!
SELECT "Accommodation system successfully removed from database" AS `Status`;
