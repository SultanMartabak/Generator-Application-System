-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for gas
CREATE DATABASE IF NOT EXISTS `gas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `gas`;

-- Dumping structure for table gas.kendaraan_data_kendaraan
CREATE TABLE IF NOT EXISTS `kendaraan_data_kendaraan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.kendaraan_data_kendaraan: ~1 rows (approximately)
INSERT INTO `kendaraan_data_kendaraan` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(3, 'terios', '2025-06-26 11:18:36', '2025-06-26 11:18:36', NULL),
	(4, 'toyota - avanza', '2025-06-26 11:47:25', '2025-06-26 11:48:03', '2025-06-26 11:48:03'),
	(5, 'xenia', '2025-06-26 11:48:12', '2025-06-26 11:48:12', NULL),
	(6, 'Role Setting', '2025-06-26 11:49:26', '2025-06-26 12:51:11', '2025-06-26 12:51:11'),
	(7, 'suzuki - ertiga', '2025-06-26 12:51:26', '2025-06-26 12:51:26', NULL);

-- Dumping structure for table gas.menu
CREATE TABLE IF NOT EXISTS `menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `is_visible` tinyint(1) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_menu_menu` (`parent_id`),
  CONSTRAINT `FK_menu_menu` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.menu: ~9 rows (approximately)
INSERT INTO `menu` (`id`, `name`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `is_visible`, `deleted_at`) VALUES
	(3, 'dashboard', 'dashboard', 'bi bi-house', NULL, 0, 1, 1, NULL),
	(5, 'Settings', '', 'bi bi-gear', NULL, 2, 1, 1, NULL),
	(6, 'Menu', 'setting/menu', 'bi bi-list', 5, 1, 1, 1, NULL),
	(10, 'Role Setting', 'setting/roles', 'bi bi-list', 5, 2, 1, 1, NULL),
	(12, 'Users', 'setting/users', 'bi bi-list', 5, 0, 1, 1, NULL),
	(13, 'Change Password', 'password/change', 'bi bi-key', 5, 3, 1, 1, NULL),
	(48, 'Kendaraan', '', 'bi bi-car-front-fill', NULL, 1, 1, 1, '2025-06-26 12:53:36');

-- Dumping structure for table gas.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.roles: ~0 rows (approximately)
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 'superadmin', 'All Akses', '2025-06-19 10:08:55', '2025-06-19 10:08:55', NULL),
	(2, 'User', 'edit data', '2025-06-19 10:10:42', '2025-06-19 10:10:42', NULL),
	(4, 'admin', 'Config data', NULL, NULL, NULL);

-- Dumping structure for table gas.role_menus
CREATE TABLE IF NOT EXISTS `role_menus` (
  `role_id` int unsigned NOT NULL,
  `menu_id` int unsigned NOT NULL,
  `can_view` tinyint(1) DEFAULT '1',
  `can_create` tinyint(1) DEFAULT '0',
  `can_update` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0',
  `can_reset_password` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`role_id`,`menu_id`),
  KEY `FK_role_menu_menu` (`menu_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.role_menus: ~22 rows (approximately)
INSERT INTO `role_menus` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_update`, `can_delete`, `can_reset_password`) VALUES
	(1, 3, 1, 1, 1, 1, 0),
	(1, 5, 1, 1, 1, 1, 0),
	(1, 6, 1, 1, 1, 1, 0),
	(1, 10, 1, 1, 1, 1, 0),
	(1, 12, 1, 1, 1, 1, 0),
	(1, 13, 1, 0, 0, 0, 0),
	(1, 35, 1, 0, 0, 0, 0),
	(1, 40, 1, 0, 0, 0, 0),
	(1, 41, 1, 0, 0, 0, 0),
	(1, 42, 1, 0, 0, 0, 0),
	(1, 43, 1, 0, 0, 0, 0),
	(1, 44, 1, 0, 0, 0, 0),
	(1, 45, 1, 0, 0, 0, 0),
	(1, 46, 1, 0, 0, 0, 0),
	(1, 47, 1, 0, 0, 0, 0),
	(1, 48, 1, 0, 0, 0, 0),
	(1, 49, 1, 0, 0, 0, 0),
	(2, 3, 1, 1, 0, 0, 0),
	(2, 5, 1, 0, 0, 0, 0),
	(2, 13, 1, 1, 0, 0, 0),
	(2, 35, 1, 0, 0, 0, 0),
	(2, 40, 1, 0, 0, 0, 0),
	(4, 3, 1, 1, 1, 1, 0),
	(4, 5, 1, 0, 0, 0, 0),
	(4, 6, 1, 1, 1, 0, 0),
	(4, 10, 1, 1, 1, 0, 0),
	(4, 12, 1, 1, 1, 0, 0),
	(4, 13, 1, 1, 1, 1, 0),
	(4, 47, 1, 1, 0, 0, 0),
	(4, 48, 1, 0, 0, 0, 0);

-- Dumping structure for table gas.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT 'Username for login',
  `email` varchar(255) NOT NULL COMMENT 'Email address, also usable for login/recovery',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hashed password using password_hash()',
  `name` varchar(255) NOT NULL COMMENT 'Full name of the user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = inactive, 1 = active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when the user was created',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp when the user was last updated',
  `last_login_at` datetime DEFAULT NULL COMMENT 'Timestamp of the last successful login',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.users: ~11 rows (approximately)
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `name`, `is_active`, `created_at`, `updated_at`, `last_login_at`, `deleted_at`) VALUES
	(1, 'admin', 'ahmadmubaroq@gmail.com', '$2y$10$qZASWSwd.rW5ijxfTsen4urjeQhN40cswZSTziBsz1wGVkWPabZ0W', 'Ahmad Mubaroq', 1, '2025-06-22 17:13:10', '2025-06-26 14:28:30', '2025-06-26 14:28:30', NULL),
	(2, 'boy', 'boe@gmail.com', '$2y$10$BSEJuboFIcY9fwmj/unLZe4xDKE9evFocWHCvxeMPD/cS.c8h.BLG', 'boy', 1, '2025-06-22 01:37:52', '2025-06-26 11:30:47', '2025-06-26 11:30:47', NULL),
	(4, 'tataq', 'tataq@gmail.com', '$2y$10$5fzyg8TA4FhAMyUGqgqbF.Asb8VBUSNiDbqM4Qn63NU57YNgcLB.K', 'tataq', 1, '2025-06-24 21:59:32', '2025-06-24 23:47:38', '2025-06-24 23:32:42', NULL),
	(5, 'joni', 'joni@gmail.com', '$2y$10$GijuGCyTPORVzAz8TTIQM.eKjd9fiGznEitnTVQmyI0Bdlc0sSeKq', 'joni', 0, '2025-06-24 23:48:58', '2025-06-25 16:13:42', '2025-06-24 23:50:13', NULL),
	(6, 'john', 'john@gmail.com', '', 'john', 1, '2025-06-25 14:24:18', '2025-06-25 14:24:18', NULL, NULL),
	(7, 'bob', 'bobi@gmail.com', '', 'bob', 1, '2025-06-25 15:49:15', '2025-06-25 15:49:15', NULL, NULL),
	(8, '20180134', 'Menu@gmail.com', '', 'Menu', 1, '2025-06-25 15:49:50', '2025-06-25 17:28:23', NULL, NULL),
	(9, 'fsclplg', 'Change@gmail.com', '', 'Change Password', 1, '2025-06-25 15:50:14', '2025-06-25 15:50:14', NULL, NULL),
	(10, '20180164', 'Data@gmail.com', '', 'Data Kendaraan', 1, '2025-06-25 15:51:11', '2025-06-25 17:28:13', NULL, NULL),
	(11, 'tataqa', 'tataqa@gmail.com', '', 'Change Password', 1, '2025-06-25 15:51:31', '2025-06-25 16:13:32', NULL, NULL),
	(12, '20180134a', '20180134a@gmail.com', '', 'boy', 1, '2025-06-25 15:51:45', '2025-06-25 15:51:45', NULL, NULL);

-- Dumping structure for table gas.user_roles
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `FK_user_role1_roles` (`role_id`),
  CONSTRAINT `FK_user_role1_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_user_role1_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table gas.user_roles: ~5 rows (approximately)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
	(1, 1),
	(2, 4),
	(4, 2),
	(6, 4),
	(8, 2),
	(10, 2);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
