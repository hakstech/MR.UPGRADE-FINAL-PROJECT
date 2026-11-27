-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Nov 24, 2026 at 04:13 AM
-- Server version: 8.0.18
-- PHP Version: 7.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mrupgrade`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `payment_type` enum('full','installment') DEFAULT 'full',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'iPhone 16 Series', 'iphone-16'),
(2, 'iPhone 15 Series', 'iphone-15'),
(3, 'iPhone 14 Series', 'iphone-14'),
(4, 'iPhone 13 Series', 'iphone-13'),
(5, 'iPhone 12 Series', 'iphone-12'),
(6, 'iPhone 11 Series', 'iphone-11'),
(7, 'iPhone XR', 'iphone-xr'),
(8, 'Google Pixel', 'google-pixel'),
(9, 'Accessories', 'accessories'),
(10, 'Samsung', 'samsung'),
(11, 'MacBook', 'macbook'),
(12, 'HP Laptop', 'hp-laptop'),
(13, 'Dell Laptop', 'dell-laptop'),
(14, 'Lenovo Laptop', 'lenovo-laptop');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'hafeez', 'hakshafeez@gmail.com', '0707665207', '', 'wqejueyrtwrrhetjryewwerhtrweqrwtrey', 1, '2026-11-07 17:06:54'),
(2, 'hafeez', 'hakshafeez@gmail.com', '0707665207', '', 'wqejueyrtwrrhetjryewwerhtrweqrwtqWDWEHTREADrey', 1, '2026-11-07 17:07:05'),
(3, 'Hafeez kasozi', 'sulaimanabdulhafidh@gmail.com', '2345678', 'Product Enquiry', 'feawrstjykligfda rytuyfsaC  465768IFGBV SRJKKJHNBGFVCD EHJAKIKJH FGHJKLKJHGFDSS ERSUIUJGHXFG sretyuyhgf rtjhgf', 1, '2026-11-21 19:36:15'),
(4, 'kasozi', 'customer@gmail.com', '2345678', 'Product Enquiry', 'zsxdcfvgb rtdfygh eydtfrgyukhij dthfjgkh xdhgjkl hfjk hvgcxc jdfghjk cjk,v', 0, '2026-11-23 15:33:37');

-- --------------------------------------------------------

--
-- Table structure for table `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installment_plans`
--

DROP TABLE IF EXISTS `installment_plans`;
CREATE TABLE IF NOT EXISTS `installment_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `deposit_amount` decimal(12,2) NOT NULL,
  `balance` decimal(12,2) NOT NULL,
  `weeks` int(11) NOT NULL,
  `weekly_payment` decimal(12,2) NOT NULL,
  `weeks_paid` int(11) DEFAULT '0',
  `status` enum('active','completed','defaulted') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(180) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_email` (`email`),
  KEY `idx_time` (`attempted_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `fulfillment` enum('pickup','delivery') DEFAULT 'pickup',
  `country` varchar(80) DEFAULT 'Uganda',
  `city` varchar(80) DEFAULT NULL,
  `payment_method` enum('mtn_momo','airtel_money','cash_on_delivery') NOT NULL,
  `momo_number` varchar(20) DEFAULT NULL,
  `payment_type` enum('full','installment') DEFAULT 'full',
  `subtotal` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `deposit_paid` decimal(12,2) DEFAULT '0.00',
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','deposit_paid','paid') DEFAULT 'unpaid',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pesapal_tracking_id` varchar(100) DEFAULT NULL,
  `pesapal_redirect_url` text,
  `payment_reference` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `full_name`, `email`, `phone`, `address`, `fulfillment`, `country`, `city`, `payment_method`, `momo_number`, `payment_type`, `subtotal`, `total_amount`, `deposit_paid`, `status`, `payment_status`, `notes`, `created_at`, `pesapal_tracking_id`, `pesapal_redirect_url`, `payment_reference`) VALUES
(1, 1, 'MRU-20261107-7EED2', 'Mr. Upgrade Admin', NULL, '0707665207', 'kampala', 'pickup', 'Uganda', NULL, 'airtel_money', '0707665207', 'full', '3500000.00', '3500000.00', '0.00', 'processing', 'deposit_paid', '', '2026-11-07 16:59:46', NULL, NULL, NULL),
(2, 1, 'MRU-20261110-0EC15', 'ertf', NULL, '788999008', '678', 'pickup', 'Uganda', NULL, 'cash_on_delivery', '', 'full', '6250000.00', '6250000.00', '0.00', 'delivered', 'paid', '', '2026-11-10 05:15:18', NULL, NULL, NULL),
(3, 1, 'MRU-20261113-40073', 'Mr. Upgrade Admin', NULL, '4323456765', 'tredff', 'pickup', 'Uganda', NULL, 'airtel_money', '87654346', 'full', '2750000.00', '2750000.00', '0.00', 'delivered', 'paid', '', '2026-11-13 12:10:29', NULL, NULL, NULL),
(4, 1, 'MRU-20261117-A076E', 'Mr. Upgrade Admin', 'admin@mrupgrade.ug', '4323456765', 'sss', 'delivery', 'United States', 'sa', 'airtel_money', '3456789', 'full', '4500000.00', '4500000.00', '0.00', 'pending', 'unpaid', '', '2026-11-17 12:28:13', NULL, NULL, NULL),
(5, 1, 'MRU-20261117-3D115', 'Mr. Upgrade Admin', 'admin@mrupgrade.ug', '4567', 'sedrfgh', 'delivery', 'Uganda', 'werty', 'cash_on_delivery', '', 'full', '5800000.00', '5800000.00', '0.00', 'pending', 'unpaid', '', '2026-11-17 12:29:52', NULL, NULL, NULL),
(6, 3, 'MRU-20261122-89C4F', 'Hafeez kasozi', 'customer@gmail.com', '707665207', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'mtn_momo', '784041256', 'full', '7000000.00', '7000000.00', '0.00', 'pending', 'unpaid', '', '2026-11-22 15:38:47', NULL, NULL, NULL),
(7, 3, 'MRU-20261122-ACB2E', 'Hafeez kasozi', 'customer@gmail.com', '784041256', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'mtn_momo', '784041256', 'full', '5800000.00', '5800000.00', '0.00', 'pending', 'unpaid', '', '2026-11-22 16:49:52', '89718a47-38a4-4942-a6dd-d9a2aed0a517', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=89718a47-38a4-4942-a6dd-d9a2aed0a517', NULL),
(8, 3, 'MRU-20261122-118A9', 'Hafeez kasozi', 'customer@gmail.com', '784041256', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'airtel_money', '707665207', 'full', '3100000.00', '3100000.00', '0.00', 'pending', 'unpaid', '', '2026-11-22 16:57:42', '98e62f2d-47b7-4c75-bff8-d9a25a3d713b', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=98e62f2d-47b7-4c75-bff8-d9a25a3d713b', NULL),
(9, 3, 'MRU-20261122-BC653', 'Hafeez kasozi', 'customer@gmail.com', '5432', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'airtel_money', '784041256', 'full', '1500000.00', '1500000.00', '0.00', 'pending', 'unpaid', '', '2026-11-22 17:01:06', '3312a6d8-ec09-4486-ab1d-d9a2a723730d', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=3312a6d8-ec09-4486-ab1d-d9a2a723730d', NULL),
(10, 3, 'MRU-20261122-11196', 'Hafeez kasozi', 'customer@gmail.com', '784041256', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'cash_on_delivery', '', 'full', '3700000.00', '3700000.00', '0.00', 'pending', 'unpaid', '', '2026-11-22 17:29:59', NULL, NULL, NULL),
(11, 3, 'MRU-20261122-158C6', 'Hafeez kasozi', 'customer@gmail.com', '4323456765', 'Kampala', 'delivery', 'Uganda', 'Kampala', 'airtel_money', '', 'full', '3200000.00', '3200000.00', '0.00', 'confirmed', 'paid', '', '2026-11-22 17:58:05', 'f506d871-10c2-423f-8711-d9a258c7887e', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=f506d871-10c2-423f-8711-d9a258c7887e', NULL),
(12, 3, 'MRU-20261122-546C9', 'Hafeez kasozi', 'customer@gmail.com', '4323456765', 'Kampala', 'delivery', 'Uganda', 'Kampala', 'cash_on_delivery', '', 'full', '1500000.00', '1500000.00', '0.00', 'shipped', 'paid', '', '2026-11-22 19:41:26', NULL, NULL, NULL),
(13, 3, 'MRU-20261123-27DD4', 'Hafeez kasozi', 'customer@gmail.com', '74567899', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'airtel_money', '', 'full', '1000.00', '1000.00', '0.00', 'pending', 'unpaid', '', '2026-11-23 07:35:10', '8532131d-995e-4021-b55b-d9a16a7a9ede', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=8532131d-995e-4021-b55b-d9a16a7a9ede', NULL),
(14, 3, 'MRU-20261123-71935', 'Hafeez kasozi', 'customer@gmail.com', '0707665207', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'airtel_money', '', 'full', '1000.00', '1000.00', '0.00', 'pending', 'unpaid', '', '2026-11-23 08:50:01', '75f24fe3-5670-441f-8506-d9a1e863ef6c', 'https://cybqa.pesapal.com/pesapaliframe/PesapalIframe3/Index?OrderTrackingId=75f24fe3-5670-441f-8506-d9a1e863ef6c', NULL),
(15, 3, 'MRU-20261123-ACE61', 'Hafeez kasozi', 'customer@gmail.com', '2345678', 'Pick Up at Store — Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'cash_on_delivery', '', 'full', '3100000.00', '3100000.00', '0.00', 'pending', 'unpaid', '', '2026-11-23 11:07:55', NULL, NULL, NULL),
(16, 3, 'MRU-20261123-AA29D', 'Hafeez kasozi', 'customer@gmail.com', '2345678', 'Pick Up at Store � Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'cash_on_delivery', '', 'full', '2100000.00', '2100000.00', '0.00', 'pending', 'unpaid', '', '2026-11-23 19:58:00', NULL, NULL, NULL),
(17, 3, 'MRU-20261123-EF5BB', 'Hafeez kasozi', 'customer@gmail.com', '5432', 'Pick Up at Store � Pioneer Mall, Room PG52, Kampala', 'pickup', 'Uganda', '', 'cash_on_delivery', '', 'full', '3100000.00', '3100000.00', '0.00', 'pending', 'unpaid', '', '2026-11-23 20:05:47', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `storage` varchar(30) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `storage`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 2, 'iPhone 16 Pro Max 256GB', '256GB', 1, '3500000.00', '3500000.00'),
(2, 2, 2, 'iPhone 16 Pro Max 256GB', '256GB', 1, '3500000.00', '3500000.00'),
(3, 2, 5, 'iPhone 15 Pro Max 256GB (UK)', '256GB', 1, '2750000.00', '2750000.00'),
(4, 3, 5, 'iPhone 15 Pro Max 256GB (UK)', '256GB', 1, '2750000.00', '2750000.00'),
(5, 4, 42, 'Samsung Galaxy Z Fold 6', '256GB', 1, '4500000.00', '4500000.00'),
(6, 5, 46, 'MacBook Pro 14\" M3 Pro 512GB', '512GB SSD', 1, '5800000.00', '5800000.00'),
(7, 6, 2, 'iPhone 16 Pro Max 256GB', '256GB', 2, '3500000.00', '7000000.00'),
(8, 7, 46, 'MacBook Pro 14\" M3 Pro 512GB', '512GB SSD', 1, '5800000.00', '5800000.00'),
(9, 8, 43, 'Google Pixel 6', '256GB', 1, '3100000.00', '3100000.00'),
(10, 9, 42, 'Samsung Galaxy Z Fold 6', '256GB', 1, '1500000.00', '1500000.00'),
(11, 10, 10, 'iPhone 14 Pro Max 256GB', '256GB', 1, '2200000.00', '2200000.00'),
(12, 10, 42, 'Samsung Galaxy Z Fold 6', '256GB', 1, '1500000.00', '1500000.00'),
(13, 11, 38, 'Samsung Galaxy S24 Ultra 256GB', '256GB', 1, '3200000.00', '3200000.00'),
(14, 12, 42, 'Samsung Galaxy Z Fold 6', '256GB', 1, '1500000.00', '1500000.00'),
(15, 13, 1, 'iPhone 16 Pro Max 512GB', '512GB', 1, '1000.00', '1000.00'),
(16, 14, 1, 'iPhone 16 Pro Max 512GB', '512GB', 1, '1000.00', '1000.00'),
(17, 15, 43, 'Google Pixel 6', '256GB', 1, '3100000.00', '3100000.00'),
(18, 16, 11, 'iPhone 14 Pro Max 128GB', '128GB', 1, '2100000.00', '2100000.00'),
(19, 17, 43, 'Google Pixel 6', '256GB', 1, '3100000.00', '3100000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pesapal_transactions`
--

DROP TABLE IF EXISTS `pesapal_transactions`;
CREATE TABLE IF NOT EXISTS `pesapal_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `pesapal_tracking_id` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(5) DEFAULT 'UGX',
  `status` varchar(50) DEFAULT 'PENDING',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesapal_transactions`
--

INSERT INTO `pesapal_transactions` (`id`, `order_id`, `order_number`, `pesapal_tracking_id`, `amount`, `currency`, `status`, `payment_method`, `created_at`) VALUES
(1, 7, 'MRU-20261122-ACB2E', '89718a47-38a4-4942-a6dd-d9a2aed0a517', '5800000.00', 'UGX', 'PENDING', NULL, '2026-11-22 16:49:58'),
(2, 8, 'MRU-20261122-118A9', '98e62f2d-47b7-4c75-bff8-d9a25a3d713b', '3100000.00', 'UGX', 'PENDING', NULL, '2026-11-22 16:57:47'),
(3, 9, 'MRU-20261122-BC653', '3312a6d8-ec09-4486-ab1d-d9a2a723730d', '1500000.00', 'UGX', 'PENDING', NULL, '2026-11-22 17:01:12'),
(4, 11, 'MRU-20261122-158C6', 'f506d871-10c2-423f-8711-d9a258c7887e', '3200000.00', 'UGX', 'PENDING', NULL, '2026-11-22 17:58:10'),
(5, 13, 'MRU-20261123-27DD4', '8532131d-995e-4021-b55b-d9a16a7a9ede', '1000.00', 'UGX', 'PENDING', NULL, '2026-11-23 07:35:15'),
(6, 14, 'MRU-20261123-71935', '75f24fe3-5670-441f-8506-d9a1e863ef6c', '1000.00', 'UGX', 'PENDING', NULL, '2026-11-23 08:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `storage` varchar(30) DEFAULT NULL,
  `color` varchar(60) DEFAULT NULL,
  `condition_type` enum('New','UK Used','Refurbished') DEFAULT 'New',
  `price` decimal(12,2) NOT NULL,
  `stock_qty` int(11) DEFAULT '0',
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `model`, `storage`, `color`, `condition_type`, `price`, `stock_qty`, `description`, `image`, `is_featured`, `is_active`, `created_at`) VALUES
(1, 1, 'iPhone 16 Pro Max 512GB', 'iphone-16-pro-max-512', 'iPhone 16 Pro Max', '512GB', '', 'New', '1000.00', 1, '0', 'uploads/products/product_1795257118_307.webp', 1, 1, '2026-11-07 14:58:09'),
(2, 1, 'iPhone 16 Pro Max 256GB', 'iphone-16-pro-max-256', 'iPhone 16 Pro Max', '256GB', '', 'New', '3500000.00', 1, '0', 'uploads/products/product_1795243010_808.jpg', 1, 1, '2026-11-07 14:58:09'),
(3, 1, 'iPhone 16 Pro 256GB', 'iphone-16-pro-256', 'iPhone 16 Pro', '256GB', '', 'New', '3300000.00', 4, '0', 'uploads/products/product_1795243895_533.jpeg', 0, 1, '2026-11-07 14:58:09'),
(4, 1, 'iPhone 16 128GB', 'iphone-16-128', 'iPhone 16', '128GB', '', 'New', '2500000.00', 6, '0', 'uploads/products/product_1795243972_619.jpeg', 0, 1, '2026-11-07 14:58:09'),
(5, 2, 'iPhone 15 Pro Max (UK) 256GB', 'iphone-15-pro-max-256-uk', 'iPhone 15 Pro Max', '256GB', '', 'UK Used', '2750000.00', 3, '0', 'uploads/products/product_1795244018_824.jpeg', 1, 1, '2026-11-07 14:58:09'),
(6, 2, 'iPhone 15 Pro 256GB', 'iphone-15-pro-256', 'iPhone 15 Pro', '256GB', '', 'New', '2650000.00', 3, '0', 'uploads/products/product_1795256950_331.jpeg', 0, 1, '2026-11-07 14:58:09'),
(7, 2, 'iPhone 15 Pro 128GB', 'iphone-15-pro-128', 'iPhone 15 Pro', '128GB', '', 'New', '2450000.00', 4, '0', 'uploads/products/product_1795256983_814.jpeg', 0, 1, '2026-11-07 14:58:09'),
(8, 2, 'iPhone 15 256GB', 'iphone-15-256', 'iPhone 15', '256GB', '', 'New', '1950000.00', 6, '0', 'uploads/products/product_1795257038_861.jpg', 0, 1, '2026-11-07 14:58:09'),
(9, 2, 'iPhone 15 128GB', 'iphone-15-128', 'iPhone 15', '128GB', '', 'New', '1850000.00', 8, '0', 'uploads/products/product_1795257059_796.jpeg', 0, 1, '2026-11-07 14:58:09'),
(10, 3, 'iPhone 14 Pro Max 256GB', 'iphone-14-pro-max-256', 'iPhone 14 Pro Max', '256GB', '', 'New', '2200000.00', 3, '0', 'uploads/products/product_1795244294_268.jpg', 1, 1, '2026-11-07 14:58:09'),
(11, 3, 'iPhone 14 Pro Max 128GB', 'iphone-14-pro-max-128', 'iPhone 14 Pro Max', '128GB', '', 'New', '2100000.00', 4, '0', 'uploads/products/product_1795257190_365.jpeg', 1, 1, '2026-11-07 14:58:09'),
(12, 8, 'Google Pixel 7', 'iphone-14-pro-256', 'Google pixel 7', '256GB', '', 'New', '1278000.00', 4, '0', 'uploads/products/product_1795257301_421.jpg', 0, 1, '2026-11-07 14:58:09'),
(13, 8, 'Google Pixel 8', 'iphone-14-pro-128', 'Google pixel 8', '128GB', '', 'New', '1900000.00', 5, '0', 'uploads/products/product_1795257399_569.jpg', 1, 1, '2026-11-07 14:58:09'),
(14, 8, 'Google Pixel 8', 'iphone-14-256', 'Google pixel 8', '256GB', '', 'New', '1450000.00', 6, '0', 'uploads/products/product_1795257436_997.jpg', 0, 1, '2026-11-07 14:58:09'),
(15, 3, 'iPhone 14 128GB', 'iphone-14-128', 'iPhone 14', '128GB', '', 'New', '1300000.00', 8, '0', 'uploads/products/product_1795257083_511.jpeg', 0, 1, '2026-11-07 14:58:09'),
(16, 4, 'iPhone 13 Pro Max 256GB', 'iphone-13-pro-max-256', 'iPhone 13 Pro Max', '256GB', '', 'UK Used', '1900000.00', 5, '0', 'uploads/products/product_1795244320_232.jpg', 1, 1, '2026-11-07 14:58:09'),
(17, 4, 'iPhone 13 Pro Max 128GB', 'iphone-13-pro-max-128', 'iPhone 13 Pro Max', '128GB', '', 'UK Used', '1700000.00', 6, '0', 'uploads/products/product_1795257537_237.jpeg', 0, 1, '2026-11-07 14:58:09'),
(18, 4, 'iPhone 13 Pro 256GB', 'iphone-13-pro-256', 'iPhone 13 Pro', '256GB', '', 'UK Used', '1650000.00', 4, '0', 'uploads/products/product_1795261275_582.jpeg', 0, 1, '2026-11-07 14:58:09'),
(19, 4, 'iPhone 13 Pro 128GB', 'iphone-13-pro-128', 'iPhone 13 Pro', '128GB', '', 'UK Used', '1500000.00', 5, '0', 'uploads/products/product_1795258382_601.jpeg', 0, 1, '2026-11-07 14:58:09'),
(20, 4, 'iPhone 13 256GB', 'iphone-13-256', 'iPhone 13', '256GB', '', 'UK Used', '1300000.00', 8, '0', 'uploads/products/product_1795261330_205.jpg', 0, 1, '2026-11-07 14:58:09'),
(21, 4, 'iPhone 13 128GB', 'iphone-13-128', 'iPhone 13', '128GB', '', 'UK Used', '1200000.00', 10, '0', 'uploads/products/product_1795261346_245.jpg', 0, 1, '2026-11-07 14:58:09'),
(22, 5, 'iPhone 12 Pro Max 256GB', 'iphone-12-pro-max-256', 'iPhone 12 Pro Max', '256GB', '', 'UK Used', '1450000.00', 4, '0', 'uploads/products/product_1795271406_122.jpeg', 0, 1, '2026-11-07 14:58:09'),
(23, 5, 'iPhone 12 Pro Max 128GB', 'iphone-12-pro-max-128', 'iPhone 12 Pro Max', '128GB', '', 'UK Used', '1350000.00', 5, '0', 'uploads/products/product_1795334278_602.jpg', 0, 1, '2026-11-07 14:58:09'),
(24, 5, 'iPhone 12 Pro 128GB', 'iphone-12-pro-128', 'iPhone 12 Pro', '128GB', '', 'UK Used', '1150000.00', 4, '0', 'uploads/products/product_1795334264_323.jpeg', 0, 1, '2026-11-07 14:58:09'),
(25, 5, 'iPhone 12 Pro 256GB', 'iphone-12-pro-256', 'iPhone 12 Pro', '256GB', '', 'UK Used', '1200000.00', 3, '0', 'uploads/products/product_1795334302_275.jpeg', 0, 1, '2026-11-07 14:58:09'),
(26, 5, 'iPhone 12 128GB', 'iphone-12-128', 'iPhone 12', '128GB', '', 'UK Used', '930000.00', 8, '0', 'uploads/products/product_1795334323_413.jpeg', 0, 1, '2026-11-07 14:58:09'),
(27, 5, 'iPhone 12 64GB', 'iphone-12-64', 'iPhone 12', '64GB', '', 'UK Used', '800000.00', 6, '0', 'uploads/products/product_1795271996_645.jpeg', 0, 1, '2026-11-07 14:58:09'),
(28, 6, 'iPhone 11 Pro Max 256GB', 'iphone-11-pro-max-256', 'iPhone 11 Pro Max', '256GB', '', 'UK Used', '1000000.00', 5, '0', 'uploads/products/product_1795334343_840.jpeg', 0, 1, '2026-11-07 14:58:09'),
(29, 6, 'iPhone 11 Pro Max 64GB', 'iphone-11-pro-max-64', 'iPhone 11 Pro Max', '64GB', '', 'UK Used', '930000.00', 4, '0', 'uploads/products/product_1795334363_384.jpg', 0, 1, '2026-11-07 14:58:09'),
(30, 6, 'iPhone 11 Pro 256GB', 'iphone-11-pro-256', 'iPhone 11 Pro', '256GB', '', 'UK Used', '950000.00', 4, '0', 'uploads/products/product_1795334381_963.jpeg', 0, 1, '2026-11-07 14:58:09'),
(31, 6, 'iPhone 11 Pro 64GB', 'iphone-11-pro-64', 'iPhone 11 Pro', '64GB', '', 'UK Used', '800000.00', 5, '0', 'uploads/products/product_1795334411_674.jpeg', 0, 1, '2026-11-07 14:58:09'),
(32, 6, 'iPhone 11 64GB', 'iphone-11-64', 'iPhone 11', '64GB', '', 'UK Used', '700000.00', 8, '0', 'uploads/products/product_1795334432_659.jpeg', 0, 1, '2026-11-07 14:58:09'),
(33, 6, 'iPhone 11 128GB', 'iphone-11-128', 'iPhone 11', '128GB', '', 'UK Used', '830000.00', 6, '0', 'uploads/products/product_1795334455_931.jpg', 0, 1, '2026-11-07 14:58:09'),
(34, 6, 'iPhone 11 256GB', 'iphone-11-256', 'iPhone 11', '256GB', '', 'UK Used', '850000.00', 4, '0', 'uploads/products/product_1795334473_947.jpeg', 0, 1, '2026-11-07 14:58:09'),
(35, 7, 'iPhone XR 64GB', 'iphone-xr-64', 'iPhone XR', '64GB', '', 'UK Used', '570000.00', 10, '0', 'uploads/products/product_1795334240_968.jpg', 0, 1, '2026-11-07 14:58:09'),
(36, 7, 'iPhone XR 128GB', 'iphone-xr-128', 'iPhone XR', '128GB', '', 'UK Used', '670000.00', 8, '0', 'uploads/products/product_1795271457_109.jpeg', 0, 1, '2026-11-07 14:58:09'),
(37, 8, 'Google Pixel 9 Pro 1TB', 'pixel-9-pro-1tb', 'Google Pixel 9 Pro', '1TB', NULL, 'New', '0.00', 1, 'Google Pixel 9 Pro 1TB. Latest Pixel flagship. 50MP camera, Google AI features, Tensor G4 chip. Contact us for pricing.', 'WhatsApp Image 2026-11-07 at 15.35.54.jpeg', 1, 0, '2026-11-07 14:58:09'),
(38, 10, 'Samsung Galaxy S24 Ultra 256GB', 'samsung-s24-ultra-256', 'Galaxy S24 Ultra', '256GB', 'Titanium Black', 'New', '3200000.00', 3, '0', 'uploads/products/product_1795244349_350.jpg', 1, 1, '2026-11-16 17:37:42'),
(39, 10, 'Samsung Galaxy S22 Ultra 512GB', 'samsung-s24-ultra-512', 'Galaxy S22 Ultra', '512GB', 'Titanium Gray', 'New', '3600000.00', 3, '0', 'uploads/products/product_1795257348_104.jpg', 0, 1, '2026-11-16 17:37:42'),
(40, 10, 'Samsung Galaxy S24+ 256GB', 'samsung-s24-plus-256', 'Galaxy S24+', '256GB', 'Cobalt Violet', 'New', '2400000.00', 5, '0', 'uploads/products/product_1795257680_229.jpg', 0, 1, '2026-11-16 17:37:42'),
(41, 10, 'Samsung Galaxy S24 128GB', 'samsung-s24-128', 'Galaxy S24', '128GB', 'Marble Gray', 'New', '1900000.00', 6, '0', 'uploads/products/product_1795259266_942.jpg', 0, 1, '2026-11-16 17:37:42'),
(42, 10, 'Samsung Galaxy Z Fold 6', 'samsung-z-fold-6', 'Galaxy Z Fold 6', '256GB', 'Black', 'New', '1500000.00', 10, '0', 'uploads/products/product_1795244248_311.jpg', 1, 1, '2026-11-16 17:37:42'),
(43, 8, 'Google Pixel 6', 'samsung-z-flip-6', 'Google pixel 6', '256GB', 'Black', 'New', '3100000.00', 7, '0', 'uploads/products/product_1795257608_398.jpg', 1, 1, '2026-11-16 17:37:42'),
(44, 10, 'Samsung Galaxy S23 Ultra 256GB', 'samsung-s23-ultra-256', 'Galaxy S23 Ultra', '256GB', 'Phantom Black', 'UK Used', '2200000.00', 5, 'Samsung Galaxy S23 Ultra 256GB. 200MP camera, built-in S Pen. UK sourced, fully tested.', 'images (23).jpg', 0, 0, '2026-11-16 17:37:42'),
(45, 10, 'Samsung Galaxy S22 Ultra 256GB', 'samsung-s22-ultra-256', 'Galaxy S22 Ultra', '256GB', 'Phantom Black', 'UK Used', '1600000.00', 4, '0', 'uploads/products/product_1795259202_643.jpg', 0, 1, '2026-11-16 17:37:42'),
(46, 11, 'MacBook Pro 14\" M3 Pro 512GB', 'macbook-pro-14-m3-512', 'MacBook Pro 14\"', '512GB SSD', 'Space Black', 'New', '5800000.00', 1, '0', 'uploads/products/product_1795244382_495.jpg', 1, 1, '2026-11-16 17:37:42'),
(47, 11, 'MacBook Pro 14\" M3 256GB', 'macbook-pro-14-m3-256', 'MacBook Pro 14\"', '256GB SSD', 'Silver', 'New', '4800000.00', 4, '0', 'uploads/products/product_1795258133_520.jpg', 0, 1, '2026-11-16 17:37:42'),
(48, 11, 'MacBook Air 15\" M3 256GB', 'macbook-air-15-m3-256', 'MacBook Air 15\"', '256GB SSD', 'Midnight', 'New', '4200000.00', 5, '0', 'uploads/products/product_1795258152_741.jpg', 0, 1, '2026-11-16 17:37:42'),
(49, 11, 'MacBook Air 13\" M3 256GB', 'macbook-air-13-m3-256', 'MacBook Air 13\"', '256GB SSD', 'Starlight', 'New', '3600000.00', 6, '0', 'uploads/products/product_1795258174_913.jpg', 0, 1, '2026-11-16 17:37:42'),
(50, 11, 'MacBook Air 13\" M2 256GB (UK)', 'macbook-air-13-m2-256', 'MacBook Air 13\"', '256GB SSD', 'Midnight', 'UK Used', '2800000.00', 5, '0', 'uploads/products/product_1795258205_548.jpg', 0, 1, '2026-11-16 17:37:42'),
(51, 11, 'MacBook Pro 13\" M2 512GB (UK)', 'macbook-pro-13-m2-512', 'MacBook Pro 13\"', '512GB SSD', 'Silver', 'UK Used', '3200000.00', 3, '0', 'uploads/products/product_1795258229_360.jpg', 0, 1, '2026-11-16 17:37:42'),
(52, 12, 'HP EliteBook 840 G9 Core i7', 'hp-elitebook-840-g9-i7', 'EliteBook 840 G9', '512GB SSD', 'Silver', 'New', '3200000.00', 4, '0', 'uploads/products/product_1795243936_877.webp', 1, 1, '2026-11-16 17:37:42'),
(53, 12, 'HP EliteBook 840 G8 Core i5', 'hp-elitebook-840-g8-i5', 'EliteBook 840 G8', '256GB SSD', 'Silver', 'UK Used', '1800000.00', 5, '0', 'uploads/products/product_1795258036_555.webp', 0, 1, '2026-11-16 17:37:42'),
(54, 12, 'HP Envy 14 Core i7', 'hp-envy-14-i7', 'HP Envy 14', '512GB SSD', 'Silver', 'New', '2800000.00', 3, '0', 'uploads/products/product_1795258099_565.jpg', 0, 1, '2026-11-16 17:37:42'),
(55, 12, 'HP 15s Core i5 (UK)', 'hp-15s-i5-uk', 'HP 15s', '256GB SSD', 'Natural Silver', 'UK Used', '1200000.00', 8, '0', 'uploads/products/product_1795258078_861.jpg', 0, 1, '2026-11-16 17:37:42'),
(56, 12, 'HP ProBook 450 G9 Core i5', 'hp-probook-450-g9-i5', 'ProBook 450 G9', '512GB SSD', 'Silver', 'New', '2200000.00', 4, '0', 'uploads/products/product_1795258058_602.jpg', 0, 1, '2026-11-16 17:37:42'),
(57, 12, 'HP EliteBook x360 1040 G9', 'hp-elitebook-x360-1040', 'EliteBook x360 1040', '512GB SSD', 'Silver', 'New', '3800000.00', 2, '0', 'uploads/products/product_1795258017_395.jpg', 0, 1, '2026-11-16 17:37:42'),
(58, 13, 'Dell XPS 15 Core i7 512GB', 'dell-xps-15-i7-512', 'Dell XPS 15', '512GB SSD', 'Platinum Silver', 'New', '4500000.00', 3, '0', 'uploads/products/product_1795244427_421.jpg', 1, 1, '2026-11-16 17:37:42'),
(59, 13, 'Dell XPS 13 Core i7 256GB', 'dell-xps-13-i7-256', 'Dell XPS 13', '256GB SSD', 'Platinum Silver', 'New', '3200000.00', 4, '0', 'uploads/products/product_1795257900_911.jpg', 0, 1, '2026-11-16 17:37:42'),
(60, 13, 'Dell Inspiron 15 Core i5 (UK)', 'dell-inspiron-15-i5-uk', 'Inspiron 15', '512GB SSD', 'Carbon Black', 'UK Used', '1200000.00', 6, '0', 'uploads/products/product_1795257926_259.jpg', 0, 1, '2026-11-16 17:37:42'),
(61, 13, 'Dell Latitude 5540 Core i5', 'dell-latitude-5540-i5', 'Latitude 5540', '256GB SSD', 'Titan Gray', 'New', '2600000.00', 4, '0', 'uploads/products/product_1795257983_826.jpg', 0, 1, '2026-11-16 17:37:42'),
(62, 13, 'Dell Vostro 15 Core i5 (UK)', 'dell-vostro-15-i5-uk', 'Vostro 3520', '256GB SSD', 'Carbon Black', 'UK Used', '1000000.00', 7, '0', 'uploads/products/product_1795257963_863.jpg', 0, 1, '2026-11-16 17:37:42'),
(63, 14, 'Lenovo IdeaPad 5 Pro Core i7', 'lenovo-ideapad-5-pro-i7', 'IdeaPad 5 Pro', '512GB SSD', 'Cloud Grey', 'New', '2800000.00', 4, '0', 'uploads/products/product_1795257804_567.jpg', 1, 1, '2026-11-16 17:37:42'),
(64, 14, 'Lenovo ThinkPad X1 Carbon', 'lenovo-thinkpad-x1-carbon', 'ThinkPad X1 Carbon', '512GB SSD', 'Black', 'New', '4200000.00', 3, '0', 'uploads/products/product_1795257876_106.jpg', 0, 1, '2026-11-16 17:37:42'),
(65, 14, 'Lenovo IdeaPad 3 Core i5 (UK)', 'lenovo-ideapad-3-i5-uk', 'IdeaPad 3', '256GB SSD', 'Arctic Grey', 'UK Used', '1100000.00', 6, '0', 'uploads/products/product_1795257855_864.jpg', 0, 1, '2026-11-16 17:37:42'),
(66, 14, 'Lenovo IdeaPad Flex 5', 'lenovo-ideapad-flex-5', 'IdeaPad Flex 5', '256GB SSD', 'Abyss Blue', 'New', '2200000.00', 5, '0', 'uploads/products/product_1795257769_907.jpg', 0, 1, '2026-11-16 17:37:42'),
(67, 14, 'Lenovo Slim 5i Core i7', 'lenovo-slim-5i-i7', 'Slim 5i', '512GB SSD', 'Cloud Grey', 'New', '3100000.00', 3, '0', 'uploads/products/product_1795257739_273.jpg', 0, 1, '2026-11-16 17:37:42'),
(68, 14, 'Lenovo IdeaPad 330 (UK)', 'lenovo-ideapad-330-uk', 'IdeaPad 330', '1TB HDD', 'Onyx Black', 'UK Used', '850000.00', 8, '0', 'uploads/products/product_1795257710_780.jpg', 0, 1, '2026-11-16 17:37:42');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) DEFAULT NULL,
  `sent_to` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed') DEFAULT 'sent',
  `response` text,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `contact_id`, `sent_to`, `message`, `status`, `response`, `sent_at`) VALUES
(1, 2, '0784041256', 'Hi hafeez, thank you for contacting Mr. Upgrade. We will get back to you shortly. - Mr. Upgrade', 'failed', '[]', '2026-11-22 08:47:00'),
(6, 3, '+256784041256', 'Hi Hafeez kasozi, thank you for contacting Mr. Upgrade. We will get back to you shortly. - Mr. Upgrade', 'failed', '[]', '2026-11-22 19:53:06'),
(7, 3, '+256784041256', 'Hi Hafeez kasozi, thank you for contacting Mr. Upgrade. We will get back to you shortly. - Mr. Upgrade', 'failed', '[]', '2026-11-23 05:18:50'),
(8, 2, '+256707665207', 'Hi hafeez, thank you for contacting Mr. Upgrade. We will get back to you shortly. - Mr. Upgrade', 'failed', '[]', '2026-11-23 06:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `role` enum('customer','admin') DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `avatar`, `google_id`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$NUZ2R74P8s8xANj6PPR.bONxe2DPU/H52zBe2OnjuM2UkKfTo6Iva', '', NULL, 'admin', NULL, NULL, '2026-11-07 14:58:08'),
(2, 'hafeez', 'hakshafeez@gmail.com', '$2y$10$EVHUvWm2Fe01lUDPAtaWlufnJZGizok7cS8RdVpC1wtylAl73XEgW', '0707665207', NULL, 'customer', NULL, NULL, '2026-11-07 17:05:31'),
(3, 'Hafeez kasozi', 'customer@gmail.com', '$2y$10$uYn6zLrdUecWthrGX8/.hOzfZKn780nqrCdX9G7vaIap0zFW7Euoa', '', NULL, 'customer', NULL, NULL, '2026-11-21 05:03:41');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
