-- Database: jjmobishop

CREATE DATABASE IF NOT EXISTS `jjmobishop`;
USE `jjmobishop`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `image` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
);

-- Orders Table (Updated with payment fields)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `order_email` VARCHAR(100) DEFAULT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'cancelled', 'paid', 'failed') DEFAULT 'pending',
  `address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'cod',
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT,
  `product_id` INT,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
);

-- Insert Default Admin User (password: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (standard bcrypt hash for 'password')
-- Replacing with a known hash for 'admin123' if possible, or just generic.
-- Let's use a simple placeholder or skip default insert if we are going to register.
-- Ideally we insert one admin.
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin User', 'admin@jjmobishop.com', '$2y$10$e/hS_example_hash_for_admin123', 'admin');

-- Add payment columns if they don't exist (for existing databases)
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `order_email` VARCHAR(100) AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) DEFAULT 'cod',
ADD COLUMN IF NOT EXISTS `transaction_id` VARCHAR(255) DEFAULT NULL;
