-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 06, 2026 at 12:35 PM
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
-- Database: `mycar`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

DROP TABLE IF EXISTS `cars`;
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'car_default.jpg',
  `price_per_day` decimal(10,2) NOT NULL,
  `seats` int DEFAULT '5',
  `fuel_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Petrol',
  `transmission` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Automatic',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_available` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `brand`, `model`, `year`, `photo`, `price_per_day`, `seats`, `fuel_type`, `transmission`, `description`, `is_available`, `created_at`) VALUES
(1, 'Mercedes-Benz', 'C-Class', '2023', '1.jpg', 85.00, 5, 'Petrol', 'Automatic', 'Luxury sedan with premium interior and smooth ride.', 1, '2026-06-05 14:44:48'),
(2, 'BMW', '5 Series', '2023', '2.jpg', 95.00, 5, 'Petrol', 'Automatic', 'Powerful and elegant business class sedan.', 1, '2026-06-05 14:44:48'),
(3, 'Audi', 'A6', '2022', '3.jpg', 90.00, 5, 'Diesel', 'Automatic', 'Refined German engineering with advanced tech.', 1, '2026-06-05 14:44:48'),
(4, 'Toyota', 'Camry', '2023', '4.jpg', 55.00, 5, 'Hybrid', 'Automatic', 'Reliable and fuel-efficient family sedan.', 1, '2026-06-05 14:44:48'),
(5, 'Hyundai', 'Tucson', '2023', '5.jpg', 60.00, 5, 'Petrol', 'Automatic', 'Modern SUV with spacious cabin.', 1, '2026-06-05 14:44:48'),
(6, 'Ford', 'Mustang', '2022', '6.jpg', 110.00, 4, 'Petrol', 'Manual', 'Iconic American muscle car.', 1, '2026-06-05 14:44:48'),
(7, 'Range Rover', 'Sport', '2023', '7.jpg', 150.00, 5, 'Diesel', 'Automatic', 'Premium SUV for ultimate comfort.', 1, '2026-06-05 14:44:48'),
(8, 'Nissan', 'Altima', '2022', '8.jpg', 50.00, 5, 'Petrol', 'Automatic', 'Comfortable and affordable daily rental.', 1, '2026-06-05 14:44:48');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL,
  `is_used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `user_id`, `code`, `discount_pct`, `is_used`, `created_at`) VALUES
(1, 1, 'MYCAR-CB89EDF9', 10.00, 1, '2026-06-05 21:51:49'),
(2, 1, 'MYCAR-01C0160A', 20.00, 0, '2026-06-05 22:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

DROP TABLE IF EXISTS `rentals`;
CREATE TABLE IF NOT EXISTS `rentals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `car_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `duration_discount` decimal(5,2) DEFAULT '0.00',
  `coupon_discount` decimal(5,2) DEFAULT '0.00',
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','active','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `car_id` (`car_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `total_days`, `price_per_day`, `duration_discount`, `coupon_discount`, `coupon_code`, `total_price`, `delivery_address`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-06-05', '2026-06-15', 10, 85.00, 10.00, 0.00, NULL, 765.00, 'friedricht street 14A doebeln', 'pending', '2026-06-05 21:44:25'),
(2, 1, 2, '2026-06-12', '2026-06-30', 18, 95.00, 10.00, 0.00, NULL, 1539.00, 'street 123 berlin', 'pending', '2026-06-05 21:50:26'),
(3, 1, 6, '2026-06-19', '2026-06-30', 11, 110.00, 10.00, 0.00, NULL, 1089.00, 'frankfurt 123', 'pending', '2026-06-05 21:51:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
  `total_rents` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `avatar`, `total_rents`, `created_at`) VALUES
(1, 'Ibrahim Fayyad', 'ibrahimfayad14@gmail.com', '$2y$10$MsRqZMCwJfzRLo.r.iPFaOWauHaIOg34meY6qvMj0u6uAHVizD9y2', '+4915214208620', 'freidrichstr 14A doebeln germany', 'default.png', 6, '2026-06-05 15:21:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
