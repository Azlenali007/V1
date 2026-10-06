-- ===================================================
-- SMM Panel Database Schema (MariaDB / MySQL 5.7+ / 8.0+)
-- Character set: utf8mb4, Collation: utf8mb4_unicode_ci
-- ===================================================

CREATE DATABASE IF NOT EXISTS `smm_panel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smm_panel`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_email` (`email`),
  INDEX `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'instagram',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Services Table
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `price_per_k` DECIMAL(10, 2) NOT NULL,
  `min_quantity` INT UNSIGNED NOT NULL DEFAULT 100,
  `max_quantity` INT UNSIGNED NOT NULL DEFAULT 1000000,
  `badge` VARCHAR(100) NOT NULL DEFAULT 'High quality followers | Instant Start | No Drop',
  `speed` VARCHAR(100) NOT NULL DEFAULT 'Fast Delivery',
  `description` TEXT NULL,
  `status` ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_service_cat` (`category_id`),
  CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Orders Table
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `service_id` INT UNSIGNED NOT NULL,
  `link` VARCHAR(500) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_order_user` (`user_id`),
  INDEX `idx_order_status` (`status`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Transactions Table
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `txn_code` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('deposit', 'order', 'refund') NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `method` VARCHAR(50) NOT NULL DEFAULT 'Razorpay',
  `status` ENUM('Completed', 'Pending', 'Failed') NOT NULL DEFAULT 'Completed',
  `reference_id` VARCHAR(100) NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_txn_user` (`user_id`),
  INDEX `idx_txn_type` (`type`),
  CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Support Tickets Table
DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_code` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `status` ENUM('Open', 'In Progress', 'Closed') NOT NULL DEFAULT 'Open',
  `priority` ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ticket_user` (`user_id`),
  INDEX `idx_ticket_status` (`status`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ticket Messages Table
DROP TABLE IF EXISTS `ticket_messages`;
CREATE TABLE `ticket_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT UNSIGNED NOT NULL,
  `sender_type` ENUM('user', 'admin') NOT NULL,
  `sender_id` INT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Payment Gateways Table
DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE `payment_gateways` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `key_id` VARCHAR(255) NULL,
  `key_secret` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `min_deposit` DECIMAL(10, 2) NOT NULL DEFAULT 100.00,
  `max_deposit` DECIMAL(10, 2) NOT NULL DEFAULT 50000.00,
  `instructions` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Site Settings Table
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ===================================================
-- Initial Default Configurations and Seed Structure
-- ===================================================

-- Initial Site Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'SMM Panel'),
('site_tagline', 'Grow Your Social Media'),
('site_currency', '₹'),
('currency_code', 'INR'),
('admin_email', 'admin@smmpanel.local'),
('support_email', 'support@smmpanel.local'),
('min_deposit', '100'),
('announcement', 'Welcome to SMM Panel! Best rates and instant delivery on all social media services.');

-- Payment Gateway Setup
INSERT INTO `payment_gateways` (`name`, `slug`, `key_id`, `key_secret`, `is_active`, `min_deposit`, `max_deposit`, `instructions`) VALUES
('Razorpay', 'razorpay', 'rzp_test_samplekey123', 'rzp_sample_secret456', 1, 100.00, 50000.00, 'Instant automated credit via UPI, Cards, NetBanking');

-- Default Categories (Matching UI Design)
INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`, `status`) VALUES
('Instagram', 'instagram', 'instagram', 1, 'active'),
('YouTube', 'youtube', 'youtube', 2, 'active'),
('Telegram', 'telegram', 'telegram', 3, 'active'),
('Facebook', 'facebook', 'facebook', 4, 'active'),
('TikTok', 'tiktok', 'tiktok', 5, 'active'),
('Twitter (X)', 'twitter', 'twitter', 6, 'active');

-- Default Services (Matching Exact Screenshot Prices & Details)
INSERT INTO `services` (`category_id`, `name`, `price_per_k`, `min_quantity`, `max_quantity`, `badge`, `speed`, `description`) VALUES
(1, 'Instagram Followers', 35.00, 1000, 1010000, 'High quality followers | Instant Start | No Drop', 'Fast Delivery', 'Real looking high-retention Instagram followers with 30-day refill guarantee.'),
(1, 'Instagram Likes', 20.00, 100, 500000, 'HQ Real Likes | Instant Start', 'Fast Delivery', 'Instant delivery likes for posts, reels, and carousels.'),
(1, 'Instagram Views', 15.00, 500, 2000000, 'Video & Reel Views | Super Fast', 'Ultra Fast', 'High speed view delivery for reels and video posts.'),
(1, 'Instagram Comments', 50.00, 50, 10000, 'Custom Positive Comments | Active Profiles', 'Moderate Speed', 'High quality relevant comments tailored to engagement algorithms.'),
(2, 'YouTube Views', 24.00, 1000, 5000000, 'High Retention Views | Monetizable Safe', 'Fast Delivery', 'High retention organic view promotion for YouTube videos.'),
(2, 'YouTube Subscribers', 150.00, 100, 50000, 'Non-Drop Real Subscribers', 'Gradual Delivery', 'Safe channel subscriber growth with organic profile appearance.'),
(3, 'Telegram Members', 45.00, 500, 200000, '0% Drop Global Channel Members', 'Fast Delivery', 'Active channel and group member boosts with high stickiness.'),
(4, 'Facebook Page Likes & Followers', 40.00, 500, 100000, 'Real Global Profiles', 'Fast Delivery', 'Boost public brand pages with real engagement metrics.'),
(5, 'TikTok Followers', 38.00, 100, 500000, 'High Quality Accounts', 'Fast Delivery', 'Accelerate algorithm reach with real profile following.'),
(6, 'Twitter (X) Followers', 55.00, 100, 100000, 'Global Active Profiles', 'Fast Delivery', 'Clean and organic looking X followers for creators and brands.');

-- Default Admin User (Password is 'Admin@123' hashed with PASSWORD_BCRYPT)
-- Default User (Aaris Ali, User ID 1024, balance ₹850.50 matching reference image)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `balance`, `role`, `status`) VALUES
(1, 'Administrator', 'admin@smmpanel.local', '+91 99999 99999', '$2y$10$wE97M/GvB8bZkWlqYkC8/O1r80e0W0n2tP8qU1kFv9N6/K8r6jK1y', 0.00, 'admin', 'active'),
(1024, 'Aaris Ali', 'aarisali@gmail.com', '+91 98765 43210', '$2y$10$wE97M/GvB8bZkWlqYkC8/O1r80e0W0n2tP8qU1kFv9N6/K8r6jK1y', 850.50, 'user', 'active');

-- Orders for User #1024 matching reference image
INSERT INTO `orders` (`order_code`, `user_id`, `service_id`, `link`, `quantity`, `price`, `status`, `created_at`) VALUES
('#10254', 1024, 1, 'https://instagram.com/aarisali', 1000, 35.00, 'Processing', '2025-05-12 16:32:00'),
('#10253', 1024, 5, 'https://youtube.com/watch?v=sample123', 5000, 120.00, 'Completed', '2025-05-11 18:10:00'),
('#10252', 1024, 7, 'https://t.me/techchannel', 2000, 90.00, 'Processing', '2025-05-10 13:45:00'),
('#10251', 1024, 2, 'https://instagram.com/p/Cxyz123', 1000, 20.00, 'Completed', '2025-05-09 19:20:00');

-- Transactions for User #1024 matching reference image
INSERT INTO `transactions` (`txn_code`, `user_id`, `type`, `amount`, `method`, `status`, `description`, `created_at`) VALUES
('TXN-1004', 1024, 'deposit', 500.00, 'Razorpay', 'Completed', 'Add Funds via Razorpay', '2025-05-12 16:12:00'),
('TXN-1003', 1024, 'order', 35.00, 'Razorpay', 'Completed', 'Order Payment - Instagram Followers', '2025-05-12 16:32:00'),
('TXN-1002', 1024, 'deposit', 200.00, 'Razorpay', 'Completed', 'Add Funds via Razorpay', '2025-05-10 11:20:00'),
('TXN-1001', 1024, 'order', 120.00, 'Razorpay', 'Completed', 'Order Payment - YouTube Views', '2025-05-10 18:15:00');

-- Support Tickets for User #1024 matching reference image
INSERT INTO `tickets` (`ticket_code`, `user_id`, `subject`, `status`, `priority`, `created_at`) VALUES
('#T1024', 1024, 'Order not started yet', 'Open', 'High', '2025-05-12 11:20:00'),
('#T1023', 1024, 'Payment issue', 'In Progress', 'Medium', '2025-05-10 18:15:00'),
('#T1022', 1024, 'Service delay', 'Closed', 'Low', '2025-05-08 15:40:00'),
('#T1021', 1024, 'Wrong quantity', 'Closed', 'Low', '2025-05-06 13:10:00');

INSERT INTO `ticket_messages` (`ticket_id`, `sender_type`, `sender_id`, `message`, `created_at`) VALUES
(1, 'user', 1024, 'Hello, my order #10254 has been pending for over 2 hours. Could you please check?', '2025-05-12 11:20:00'),
(2, 'user', 1024, 'I added ₹200 via UPI and it took 5 minutes to reflect in balance.', '2025-05-10 18:15:00'),
(2, 'admin', 1, 'We verified your Razorpay payment reference and reconciled your balance. Thank you.', '2025-05-10 18:30:00');
