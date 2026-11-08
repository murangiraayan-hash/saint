-- ========================================================
-- Saint Michael Technical High School
-- Database Schema for Student Registration & Fee Payments
-- Compatible with MySQL 5.7+, MySQL 8.0, and MariaDB
-- ========================================================

-- 1. Create Database
CREATE DATABASE IF NOT EXISTS `smths_school_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smths_school_db`;

-- 2. Table: Programs / Technical Trades
CREATE TABLE IF NOT EXISTS `programs` (
  `id` VARCHAR(10) PRIMARY KEY,
  `name_en` VARCHAR(150) NOT NULL,
  `name_rw` VARCHAR(150) NOT NULL,
  `duration_years` INT DEFAULT 3,
  `award` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed Programs
INSERT IGNORE INTO `programs` (`id`, `name_en`, `name_rw`, `award`) VALUES
('p1', 'Software Development', 'Gukora Porogaramu za Mudasobwa', 'National TVET Diploma Level 5'),
('p2', 'Food and Beverage Operations', 'Ibiribwa n\'Ibinyobwa (Culinary)', 'National TVET Diploma Level 5'),
('p3', 'Computer Systems & Networks', 'Sisitemu za Mudasobwa', 'National TVET Diploma Level 5'),
('p4', 'Architecture & Structural Design', 'Ubwubatsi n\'Igishushanyo', 'National TVET Diploma Level 5'),
('p5', 'Multimedia Production', 'Multimedia n\'Ubugeni bw\'Amashusho', 'National TVET Diploma Level 5');

-- 3. Table: Student Registrations
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_code` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `dob` DATE NOT NULL,
  `district` VARCHAR(100) NOT NULL,
  `previous_school` VARCHAR(150) DEFAULT NULL,
  `program_id` VARCHAR(10) NOT NULL,
  `parent_name` VARCHAR(150) NOT NULL,
  `parent_phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'verified', 'admitted') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_program` (`program_id`),
  INDEX `idx_phone` (`parent_phone`),
  FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Table: Fee Payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `txn_ref` VARCHAR(50) NOT NULL UNIQUE,
  `reg_code` VARCHAR(30) DEFAULT NULL,
  `student_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `program_id` VARCHAR(10) NOT NULL,
  `payment_method` ENUM('momo', 'bk', 'bpr', 'equity') NOT NULL,
  `amount_rwf` DECIMAL(10, 2) NOT NULL,
  `item_type` VARCHAR(50) NOT NULL,
  `status` ENUM('completed', 'pending_verification') DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_txn` (`txn_ref`),
  FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Sample Seed Data
INSERT IGNORE INTO `registrations` (`reg_code`, `full_name`, `dob`, `district`, `previous_school`, `program_id`, `parent_name`, `parent_phone`, `status`) VALUES
('SMTHS-2025-0101', 'Mugisha Emmanuel', '2007-04-12', 'Gasabo, Kigali', 'GS Remera Protestant', 'p1', 'Habimana Jean Bosco', '0788123456', 'admitted'),
('SMTHS-2025-0102', 'Keza Diane', '2008-01-24', 'Kicukiro, Kigali', 'Kagarama Secondary', 'p2', 'Mukamana Claudine', '0788654321', 'verified'),
('SMTHS-2025-0103', 'Uwase Aline', '2007-09-18', 'Nyarugenge, Kigali', 'Camp Kigali School', 'p5', 'Karasira Patrick', '0788998877', 'pending');

INSERT IGNORE INTO `payments` (`txn_ref`, `reg_code`, `student_name`, `phone`, `program_id`, `payment_method`, `amount_rwf`, `item_type`) VALUES
('TXN-MOMO-94821', 'SMTHS-2025-0101', 'Mugisha Emmanuel', '0788123456', 'p1', 'momo', 20000.00, 'registration'),
('TXN-BK-110492', 'SMTHS-2025-0102', 'Keza Diane', '0788654321', 'p2', 'bk', 120000.00, 'term_tuition');
