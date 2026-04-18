-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 07:32 PM
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
-- Database: `db_webdev`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `status`) VALUES
(2, 1, '2026-04-05 11:25:56', 'Pending'),
(4, 1, '2026-04-07 06:11:50', 'Pending'),
(5, 1, '2026-04-07 06:16:33', 'Pending'),
(6, 1, '2026-04-12 11:33:01', 'Pending'),
(7, 1, '2026-04-12 12:10:36', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_at_purchase` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(3, 2, 1, 2, 150000.00),
(4, 2, 2, 1, 200000.00),
(6, 5, 1, 2, 250000.00),
(7, 6, 1, 1, 250000.00),
(8, 7, 1, 1, 250000.00),
(9, 7, 1, 2, 150000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category`, `image_url`, `created_at`) VALUES
(1, 'Vintage Tweed Blazer', 'Old money aesthetic blazer, premium wool.', 899000.00, 10, 'Outerwear', 'blazer.jpg', '2026-04-04 12:31:06'),
(2, 'Classic White Dress', 'Elegant white dress for formal events', 250000.00, 10, 'Dress', 'white_dress.jpg', '2026-04-05 11:24:44'),
(3, 'Minimalist Black Top', 'Casual black top for daily wear', 150000.00, 20, 'Tops', 'black_top.jpg', '2026-04-05 11:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'cher_lead_dev', 'rahasiaBanget123', 'charity.rn@presuni.ac.id', '2026-04-03 15:53:53'),
(2, 'cher_test', '$2b$10$jkkPTtn9k8kEElkbwbBDuuy9WkuuREa3weq5OKWDDKUResb9ue.Pu', 'cher@test.com', '2026-04-03 17:12:51'),
(4, 'cher_security_test', '$2b$10$F91xbhwGMQF3DIbjI36dIeB3G.FZFE5.hcVLjBKEpwuITZ1znVbh2', 'cher@test.com', '2026-04-03 17:17:31'),
(6, 'cher_apparel_dev', '$2b$10$GSBv0uZXrgdiPwUeSjWxlu3KQR8KK6Ru2PJt3qn0JdHgU6oR2sOqS', 'cher@test.com', '2026-04-03 17:44:02'),
(7, 'cherlovejeff', '$2y$10$43uhv4QwS5IopNzSU.yB7egwRhykH4pVRanA/qs6enOp5dnzh2ljW', 'cherlovejeff@gmail.com', '2026-04-07 16:04:45'),
(8, 'cherr2811', '$2y$10$U27zaJhSRhtsB8gEV4CBo.fDW9BITEjLxr8LyBpxS9W9xg8yJXqla', 'cerl0vejeff@gmail.com', '2026-04-12 11:34:26'),
(9, 'ceriii', '$2y$10$5UBhB10rv.FiQpirRIvPKemDG75050WZR6YRoe2I6A1JQvLgLpp5y', 'cerlovejeff11@gmail.com', '2026-04-12 12:08:43'),
(10, 'ceri123', '$2y$10$ckWyiMdxgAR7SZiFO6OOXeFmF4Omv/lrrEywI3.0JyHtjb.XL5ZVi', 'ceriipacarkenma@gmail.com', '2026-04-12 12:17:49'),
(11, 'barbie1109', '$2y$10$bFUIH4EBKnqilZoXyAFOx.ziDl03JCFN73ENUeZlSI5wUWt4r7e/S', 'tinkerbell@test.com', '2026-04-12 13:02:36'),
(12, 'testkenma', '$2y$10$AsyRthdkVFjWhCyyN8X9e.oz7JFJasgoxbtpoRGeCVl7mBjQGBUJS', 'kenmakozume@gmail.com', '2026-04-12 13:06:48'),
(13, 'cerianagrande', '$2y$10$r1LgZ4GDOVpLGmYxPep0EO0PtOY6p4OKnAZX6d52WIIiBjXjCUzlW', 'cerigrande@gmail.com', '2026-04-12 13:38:50'),
(14, 'test1111', '$2y$10$qekm69pwElkP.Pm7eSkL0eNEVvQA8YwBKmX4/UOKKXKm9M6y3BZ/u', 'testmulu@gmail.com', '2026-04-12 14:01:48'),
(15, 'test123', '$2y$10$Gh3cpnugpXVLtVTvvh9KqesG7tNtHh0yMj1Q7miZbXf3ngduK3VvW', 'testlagii@gmail.com', '2026-04-12 14:04:18'),
(16, 'kenmakozume', '$2y$10$KSkc0KEj2rpSCwRu5AZa/Otp/ae23BXaQ654HhrblZaAWp04C5sZq', 'test123@gmail.com', '2026-04-12 14:05:24'),
(17, 'phonetest11', '$2y$10$OHq3ZUsA7yU8Va2tAz.i4OmkJO16U6YClxSgL1hVQGvAcXCe20h5K', 'phonetest11@gmail.com', '2026-04-12 14:40:03'),
(18, 'cerisukaciklat', '$2y$10$3DleC7o0.5Xv2HF0aQQ7JO7oLD.FYiYGE9yIDHdTjImgNr2bzIedm', 'cerisukacoklat@gmail.com', '2026-04-13 06:02:37'),
(19, 'ceritestlagi', '$2y$10$VWEkpKJbFw/M.xzvHZSpzeAgLgUFmU58gJ1B4VuL3qEBjhu.0Khy6', 'ceritestlagi@gmail.com', '2026-04-13 06:39:00'),
(20, 'test14april', '$2y$10$PdOEUZxAph1uOglEfGCSHeFFp03Ko360rYP5hS8rRRzLLn34ivT7e', 'test14april@gmail.com', '2026-04-14 07:55:45'),
(21, 'araaimut', '$2y$10$Mt9er6/sWRaTGbFmfKNuX.qsRrzirySaUKIaZV7JXMaxK4dxWKLxu', 'ara23@gmail.com', '2026-04-17 17:09:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
