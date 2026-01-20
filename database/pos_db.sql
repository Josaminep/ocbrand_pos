-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2026 at 08:46 AM
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
(8, 'A76494', 'admin', 'John', '', 'Doe', 'Mandaluyong City', '0900000000', '$2y$10$OgFpc6mDKIBK3TSHNUE9LuAQINHFx1tEQukYl16AkPOiYEcspOgze', '1768263393_69658ee19780c.jpg', '2025-12-15 06:17:02');

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

INSERT INTO `products` (`id`, `brand`, `name`, `category`, `srp`, `price`, `quantity`, `image`, `created_at`) VALUES
(9, 'OC Brand', 'Sauvage Eau de Parfum', 'Perfumes', 180.00, 120.00, 16, 'uploads/products/1765780165_2272645.jpg', '2025-12-15 06:29:25'),
(10, 'OC Brand', 'Bleu de Chanel', 'Perfumes', 200.00, 135.00, 14, 'uploads/products/1765780233_17089180205.jpg', '2025-12-15 06:30:33'),
(11, 'OC Brand', 'Dri-FIT Cotton Tee', 'Shirts', 300.00, 250.00, 34, 'uploads/products/1765780545_black....jpg', '2025-12-15 06:35:45'),
(14, 'OC Brand', '9FORTY Adjustable Cap', 'Caps', 400.00, 350.00, 49, 'uploads/products/1765780852_new-era-boston-celtics-pipe-pop-black-9forty-cap.jpg', '2025-12-15 06:40:52'),
(15, 'OC Brand', 'Heritage 86 Cap', 'Caps', 450.00, 420.00, 5, 'uploads/products/1765780901_images (3).jpg', '2025-12-15 06:41:41'),
(16, 'OC Brand', 'Mesh Short - RED', 'Shorts', 850.00, 500.00, 13, 'uploads/products/1768265856_mesh_short-red.jpg', '2026-01-13 00:57:36'),
(17, 'OC Brand', 'Mesh Short - RED TWO TONE', 'Shorts', 850.00, 500.00, 13, 'uploads/products/1768265899_mesh_short_red-tone.jpg', '2026-01-13 00:58:19'),
(18, 'OC Brand', 'Mesh Short - GRAY', 'Shorts', 850.00, 500.00, 14, 'uploads/products/1768265939_mesh_short_gray.jpg', '2026-01-13 00:58:59'),
(19, 'OC Brand', 'Mesh Short - BROWN', 'Shorts', 850.00, 500.00, 15, 'uploads/products/1768266019_mesh_short_brown.jpg', '2026-01-13 01:00:19'),
(20, 'OC Brand', 'Mesh Short - TWO TONE', 'Shorts', 850.00, 500.00, 15, 'uploads/products/1768266783_mesh_short_two-tone.jpg', '2026-01-13 01:13:03'),
(21, 'OC Brand', 'Mesh Short', 'Shorts', 850.00, 500.00, 20, 'uploads/products/1768266832_mesh_short.jpg', '2026-01-13 01:13:52'),
(22, 'OC Brand', 'Fleece Pants', 'Pants', 950.00, 600.00, 9, 'uploads/products/1768267012_fleece_pants.jpg', '2026-01-13 01:16:52');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `vat` decimal(10,2) DEFAULT NULL,
  `cash` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_tin` varchar(50) DEFAULT NULL,
  `admin` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `total`, `vat`, `cash`, `change_amount`, `customer_name`, `customer_tin`, `admin`, `created_at`) VALUES
(14, 'INV-20251219023451', 784.00, 84.00, 900.00, 116.00, 'jhg', '09876', '6', '2025-12-19 01:34:51'),
(15, 'INV-20251219025430', 448.00, 48.00, 500.00, 52.00, 'dfg', '2345', '6', '2025-12-19 01:54:30'),
(16, 'INV-20251219025554', 448.00, 48.00, 500.00, 52.00, 'dfg', '2345', '6', '2025-12-19 01:55:54'),
(17, 'INV-20251219025614', 448.00, 48.00, 450.00, 2.00, 'test', '98700', '6', '2025-12-19 01:56:14'),
(18, 'INV-20251219025655', 1008.00, 108.00, 1100.00, 92.00, 'test', '13245', '6', '2025-12-19 01:56:55'),
(19, 'INV-20251219073223', 403.20, 43.20, 403.00, 0.00, 'asd', '123', '6', '2025-12-19 06:32:23'),
(20, 'INV-20260107013426', 403.20, 43.20, 403.00, 0.00, '--', 'None', '6', '2026-01-07 00:34:26'),
(21, 'INV-20260107013604', 403.20, 43.20, 403.00, 0.00, '--', 'None', '6', '2026-01-07 00:36:04'),
(22, 'INV-20260107013712', 403.20, 43.20, 403.00, 0.00, '--', 'None', '6', '2026-01-07 00:37:12'),
(23, 'INV-20260107013815', 403.20, 43.20, 403.00, 0.00, '--', 'None', '6', '2026-01-07 00:38:15'),
(24, 'INV-20260107014418', 403.20, 43.20, 403.00, 0.00, '--', 'None', '6', '2026-01-07 00:44:18'),
(25, 'INV-20260107014550', 3740.80, 400.80, 4000.00, 259.00, 'John Doe', '789-001-1101', '6', '2026-01-07 00:45:50'),
(26, 'INV-20260107015017', 2923.20, 313.20, 3000.00, 77.00, '--', 'None', '6', '2026-01-07 00:50:17'),
(27, 'INV-20260112013044', 604.80, 64.80, 620.00, 15.00, '--', 'None', '7', '2026-01-12 00:30:44'),
(28, 'INV-20260112013510', 448.00, 48.00, 500.00, 52.00, '--', 'None', '7', '2026-01-12 00:35:10'),
(29, 'INV-20260112013627', 448.00, 48.00, 500.00, 52.00, '--', 'None', '7', '2026-01-12 00:36:27'),
(30, 'INV-20260112013720', 448.00, 48.00, 500.00, 52.00, '--', 'None', '7', '2026-01-12 00:37:20'),
(31, 'INV-20260113032907', 952.00, 102.00, 1000.00, 48.00, 'John', '987-123-43', '8', '2026-01-13 02:29:07'),
(32, 'INV-20260113034043', 1904.00, 204.00, 2000.00, 96.00, 'Anna', '000-00-00', '8', '2026-01-13 02:40:43'),
(33, 'INV-20260113040322', 952.00, 102.00, 1000.00, 48.00, '--', 'None', '6', '2026-01-13 03:03:22'),
(34, 'INV-20260113043746', 3953.60, 423.60, 3954.00, 0.00, '--', 'None', '7', '2026-01-13 03:37:46');

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `srp` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `profit` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `product_id`, `product_name`, `srp`, `price`, `quantity`, `subtotal`, `profit`, `discount`) VALUES
(16, 14, 12, 'Active Training Shorts', 350.00, 350.00, 2, 700.00, 0.00, NULL),
(17, 15, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(18, 16, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(19, 17, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(20, 18, 15, 'Heritage 86 Cap', 450.00, 420.00, 2, 900.00, 60.00, 0.00),
(21, 19, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(22, 20, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(23, 21, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(24, 22, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(25, 23, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(26, 24, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 2, 360.00, 120.00, 0.00),
(27, 25, 11, 'Dri-FIT Cotton Tee', 300.00, 250.00, 4, 1200.00, 200.00, 0.00),
(28, 25, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 1, 180.00, 60.00, 0.00),
(29, 25, 10, 'Bleu de Chanel', 200.00, 135.00, 1, 200.00, 65.00, 0.00),
(30, 25, 12, 'Active Training Shorts', 350.00, 280.00, 1, 350.00, 70.00, 0.00),
(31, 25, 13, 'HeatGear Sports Shorts', 280.00, 230.00, 2, 560.00, 100.00, 0.00),
(32, 25, 14, '9FORTY Adjustable Cap', 400.00, 350.00, 1, 400.00, 50.00, 0.00),
(33, 25, 15, 'Heritage 86 Cap', 450.00, 420.00, 1, 450.00, 30.00, 0.00),
(34, 26, 14, '9FORTY Adjustable Cap', 400.00, 350.00, 1, 400.00, 50.00, 0.00),
(35, 26, 15, 'Heritage 86 Cap', 450.00, 420.00, 2, 900.00, 60.00, 0.00),
(36, 26, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 1, 180.00, 60.00, 0.00),
(37, 26, 10, 'Bleu de Chanel', 200.00, 135.00, 1, 200.00, 65.00, 0.00),
(38, 26, 11, 'Dri-FIT Cotton Tee', 300.00, 250.00, 1, 300.00, 50.00, 0.00),
(39, 26, 12, 'Active Training Shorts', 350.00, 280.00, 1, 350.00, 70.00, 0.00),
(40, 26, 13, 'HeatGear Sports Shorts', 280.00, 230.00, 1, 280.00, 50.00, 0.00),
(41, 27, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 3, 540.00, 180.00, 0.00),
(42, 28, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(43, 29, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(44, 30, 10, 'Bleu de Chanel', 200.00, 135.00, 2, 400.00, 130.00, 0.00),
(45, 31, 16, 'Mesh Short - RED', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(46, 32, 18, 'Mesh Short - GRAY', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(47, 32, 21, 'Mesh Short', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(48, 33, 17, 'Mesh Short - RED TWO TONE', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(49, 34, 9, 'Sauvage Eau de Parfum', 180.00, 120.00, 1, 180.00, 60.00, 0.00),
(50, 34, 11, 'Dri-FIT Cotton Tee', 300.00, 250.00, 1, 300.00, 50.00, 0.00),
(51, 34, 14, '9FORTY Adjustable Cap', 400.00, 350.00, 1, 400.00, 50.00, 0.00),
(52, 34, 16, 'Mesh Short - RED', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(53, 34, 17, 'Mesh Short - RED TWO TONE', 850.00, 500.00, 1, 850.00, 350.00, 0.00),
(54, 34, 22, 'Fleece Pants', 950.00, 600.00, 1, 950.00, 350.00, 0.00);

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
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `status`) VALUES
(2, '1234 Corper', 'John Doe', 'asd@mail.com', '0900000012', 'Active'),
(3, 'Heritage 86 Caper', 'Asda', 'asd@mail.com', '0900000012', 'Active');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
