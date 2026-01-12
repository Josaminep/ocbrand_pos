-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 08:00 AM
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
(6, 'A71608', 'admin', 'Test', '', 'Admin', 'Mandaluyong City', '0900000000', '$2y$10$ZuvXM0AKBKHnF6Z7BlZL4OwQF4cDwfkkHCHc.UQR2suW/x62WHjbS', '1765630270_693d613e5b045.jpg', '2025-12-13 12:51:10'),
(7, 'C18933', 'cashier', 'Test', '', 'Cashier', 'Mandaluyong City', '0900000000', '$2y$10$CET.DHHS2Aw0dlPo13jTbOocLieL9T8AWhSyeZBXRywlIO.fqRnpi', '1765693355_693e57ab36b2b.jpg', '2025-12-14 06:22:35');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
