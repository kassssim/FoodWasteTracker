-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 05, 2026 at 01:13 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_waste_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
CREATE TABLE IF NOT EXISTS `ingredients` (
  `ingredient_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ingredient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`, `unit`, `cost_per_unit`, `created_at`) VALUES
(29, 'Chicken Breast', 'kg', 18.50, '2026-07-05 12:52:42'),
(30, 'Fresh Salmon', 'kg', 68.00, '2026-07-05 12:52:53'),
(31, 'Beef Sirloin', 'kg', 42.00, '2026-07-05 12:53:06'),
(32, 'Romaine Lettuce', 'kg', 6.50, '2026-07-05 12:53:32'),
(33, 'Basmati Rice', 'kg', 8.00, '2026-07-05 12:53:45'),
(34, 'Fresh Tomatoes', 'kg', 5.00, '2026-07-05 12:53:57'),
(35, 'Cooking Oil', 'liter', 12.00, '2026-07-05 12:54:08'),
(36, 'Fresh Prawns', 'kg', 55.00, '2026-07-05 12:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('staff','manager') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Muzh', 'muzh@gmail.com', '$2y$10$IDPXP5lOcE8WKaD4N.555.3WXTGWOPi9sZy9rf6y6NnGkHWpCPF7O', 'manager', '2026-07-05 13:07:41'),
(2, 'Hakim', 'hakim@gmail.com', '$2y$10$EDdeR4mcZ9r5mfnwAMbFP.2lq9f6h5uf/1ZDY/OuU0eGtOcn4B1fq', 'staff', '2026-07-05 13:07:21');

-- --------------------------------------------------------

--
-- Table structure for table `waste_logs`
--

DROP TABLE IF EXISTS `waste_logs`;
CREATE TABLE IF NOT EXISTS `waste_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reason` enum('Expired','Overproduction','Spoilage','Prep Mistake','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `financial_loss` decimal(10,2) NOT NULL,
  `logged_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `ingredient_id` (`ingredient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waste_logs`
--

INSERT INTO `waste_logs` (`log_id`, `user_id`, `ingredient_id`, `quantity`, `reason`, `financial_loss`, `logged_at`) VALUES
(20, 1, 29, 5.00, 'Prep Mistake', 92.50, '2026-07-05 12:54:54'),
(21, 1, 30, 3.00, 'Spoilage', 204.00, '2026-07-05 12:55:03'),
(22, 1, 31, 2.00, 'Overproduction', 84.00, '2026-07-05 12:55:11'),
(23, 1, 32, 8.00, 'Expired', 52.00, '2026-07-05 12:55:25'),
(24, 1, 33, 4.00, 'Overproduction', 32.00, '2026-07-05 12:55:30'),
(25, 2, 34, 6.00, 'Spoilage', 30.00, '2026-06-28 12:56:26'),
(26, 2, 35, 2.00, 'Other', 24.00, '2026-06-28 12:56:26'),
(27, 2, 36, 1.50, 'Expired', 82.50, '2026-06-28 12:56:26'),
(28, 2, 29, 3.00, 'Overproduction', 55.50, '2026-06-28 12:56:26'),
(29, 2, 30, 2.00, 'Prep Mistake', 136.00, '2026-06-28 12:56:26');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
