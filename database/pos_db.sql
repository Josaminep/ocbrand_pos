-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 09:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `account_id` varchar(10) NOT NULL,
  `role` enum('cashier','admin') NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_id`, `role`, `fname`, `mname`, `lname`, `address`, `contact`, `password`, `photo`, `created_at`) VALUES
(7, 'C18933', 'cashier', 'Maria', '', 'Cruz', 'Mandaluyong City', '0900000000', '$2y$10$G5.oqkoBMQe87OSrFJQ96O6JpoCVCD4SOB.ivRHuD5QJR1zCrTvvW', '1765693355_693e57ab36b2b.jpg', '2025-12-14 06:22:35'),
(8, 'A76494', 'admin', 'John', '', 'Doe', 'Mandaluyong City', '0900000000', '$2y$10$OgFpc6mDKIBK3TSHNUE9LuAQINHFx1tEQukYl16AkPOiYEcspOgze', '1768263393_69658ee19780c.jpg', '2025-12-15 06:17:02'),
(10, 'C66196', 'cashier', 'Test', '', 'Cashier', 'Sa tabi lang', '0900000000', '$2y$10$Rrrx539dK1ODBnkb41HzcORdzhssNXNgiNPDJsuWqP/.hUcwkGH9C', '1770276040_698444c85a8ad.jpg', '2026-02-05 07:20:40');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(4, 'Caps'),
(7, 'Hoodie'),
(6, 'Pants'),
(1, 'Perfumes'),
(2, 'Shirts'),
(3, 'Shorts');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `srp` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `brand`, `name`, `category`, `srp`, `price`, `quantity`, `image`, `created_at`) VALUES
(1, 'SHO001', 'OC Brand', 'MESH SHORT SKY BLUE', 'Shorts', 500.00, 450.00, 17, 'uploads/products/1770268773_mesh_short_sky-blue.jpg', '2026-02-05 05:19:33'),
(2, 'SHO002', 'OC Brand', 'MESH SHORT RED', 'Shorts', 500.00, 450.00, 10, 'uploads/products/1770268827_mesh_short-red.jpg', '2026-02-05 05:20:27'),
(3, 'SHO003', 'OC Brand', 'MESH SHORT GRAY', 'Shorts', 500.00, 450.00, 14, 'uploads/products/1770268861_mesh_short_gray.jpg', '2026-02-05 05:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `vatable` decimal(10,2) NOT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `cash` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_address` varchar(255) NOT NULL,
  `customer_tin` varchar(50) DEFAULT NULL,
  `user` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `qty`, `total`, `vatable`, `vat`, `cash`, `change_amount`, `payment_method`, `paid_amount`, `customer_name`, `customer_address`, `customer_tin`, `user`, `created_at`) VALUES
(1, 'INV-20260224012222', 2, 1000.00, 892.86, 107.14, 1000.00, 0.00, 'Cash', 0.00, 'Asd', 'Ncr', '123-455-555', '8', '2026-02-24 00:22:22'),
(2, 'INV-20260302010605', 2, 1000.00, 892.86, 107.14, 1000.00, 0.00, 'Cash', 0.00, '--', '--', 'None', '8', '2026-03-02 00:06:05'),
(3, 'INV-20260302031544', 2, 1000.00, 892.86, 107.14, 1000.00, 0.00, 'GCash', 1000.00, 'Test', 'Makati City', '123-456-789', '8', '2026-03-02 02:15:44'),
(4, 'INV-20260302031659', 3, 1500.00, 1339.29, 160.71, 1500.00, 0.00, 'GCash', 1500.00, 'Test', 'Manila City', '123-456-789', '8', '2026-03-02 02:16:59'),
(5, 'INV-20260302032427', 3, 1500.00, 1339.29, 160.71, 1500.00, 0.00, 'GCash', 1500.00, 'Test', 'Manila City', '123-456-789', '0', '2026-03-02 02:24:27');

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(255) NOT NULL DEFAULT '',
  `product_id` int(11) DEFAULT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `srp` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `vatable` double DEFAULT NULL,
  `vat` double DEFAULT 0,
  `profit` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `invoice_no`, `product_id`, `product_code`, `product_name`, `srp`, `price`, `quantity`, `subtotal`, `vatable`, `vat`, `profit`, `discount`) VALUES
(1, 1, 'INV-20260224012222', 2, 'SHO002', 'MESH SHORT RED', 500.00, 450.00, 2, 1000.00, 892.86, 107.14, 100.00, 0.00),
(2, 2, 'INV-20260302010605', 1, 'SHO001', 'MESH SHORT SKY BLUE', 500.00, 450.00, 2, 1000.00, 892.86, 107.14, 100.00, 0.00),
(3, 3, 'INV-20260302031544', 3, 'SHO003', 'MESH SHORT GRAY', 500.00, 450.00, 2, 1000.00, 892.86, 107.14, 100.00, 0.00),
(4, 4, 'INV-20260302031659', 1, 'SHO001', 'MESH SHORT SKY BLUE', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00),
(5, 4, 'INV-20260302031659', 2, 'SHO002', 'MESH SHORT RED', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00),
(6, 4, 'INV-20260302031659', 3, 'SHO003', 'MESH SHORT GRAY', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00),
(7, 5, 'INV-20260302032427', 1, 'SHO001', 'MESH SHORT SKY BLUE', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00),
(8, 5, 'INV-20260302032427', 2, 'SHO002', 'MESH SHORT RED', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00),
(9, 5, 'INV-20260302032427', 3, 'SHO003', 'MESH SHORT GRAY', 500.00, 450.00, 1, 500.00, 446.43, 53.57, 50.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `product` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `product`, `status`) VALUES
(2, '1234 Corper', 'John Doe', 'asd@mail.com', '0900000012', 'Caps', 'Active'),
(3, 'Heritage 86 Caper', 'Asda', 'asd@mail.com', '0900000012', 'Shorts', 'Active'),
(4, '679', 'Juan De Jesus', 'example@gmail.com', '090000000', 'Pants', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `account_id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `session_seconds` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`log_id`, `user_id`, `account_id`, `name`, `login_at`, `logout_at`, `session_seconds`, `status`, `ip_address`, `user_agent`) VALUES
(1, 8, 'A76494', 'John Doe', '2026-03-02 10:03:39', '2026-03-02 10:18:57', 918, 'Successful log in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36'),
(2, 8, 'A76494', 'John Doe', '2026-03-02 10:19:12', '2026-03-02 10:19:17', 5, 'Successful log in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_id` (`account_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `login_at` (`login_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
