-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 06:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eventmanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `event_place` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `package_id`, `event_place`, `event_date`, `discount`, `confirmed_by`, `status`, `created_at`) VALUES
(1, 3, 1, 'Wedding Venue, New York', '2025-06-15', NULL, NULL, 'pending', '2025-04-24 10:49:55'),
(2, 3, 2, 'Grand Hall, Los Angeles', '2025-07-22', 200.00, NULL, 'confirmed', '2025-04-24 10:49:55'),
(3, 3, 4, 'Corporate Center, Chicago', '2025-05-10', NULL, NULL, 'pending', '2025-04-24 10:49:55'),
(4, 3, 3, 'Luxury Resort, Miami', '2025-08-05', 500.00, NULL, 'confirmed', '2025-04-24 10:49:55'),
(5, 3, 5, 'Community Center, Dallas', '2025-04-30', NULL, NULL, 'confirmed', '2025-04-24 10:49:55'),
(6, 4, 1, 'London', '2025-05-03', 100.00, 1, 'confirmed', '2025-04-24 11:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `rsvp_status` varchar(20) DEFAULT 'pending',
  `last_invited_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `booking_id`, `name`, `email`, `phone`, `rsvp_status`, `last_invited_at`) VALUES
(1, 1, 'John Smith', 'john.smith@example.com', '123-456-7890', 'yes', '2025-04-24 14:19:55'),
(2, 1, 'Emily Johnson', 'emily.johnson@example.com', '234-567-8901', 'yes', '2025-04-24 14:19:55'),
(3, 1, 'Michael Williams', 'michael.williams@example.com', '345-678-9012', 'no', '2025-04-24 14:19:55'),
(4, 1, 'Jessica Brown', 'jessica.brown@example.com', '456-789-0123', 'pending', '2025-04-24 14:19:55'),
(5, 2, 'David Miller', 'david.miller@example.com', '567-890-1234', 'pending', '2025-04-24 14:19:55'),
(6, 3, 'Sarah Davis', 'sarah.davis@example.com', '678-901-2345', 'yes', '2025-04-24 14:19:55'),
(7, 3, 'James Wilson', 'james.wilson@example.com', '789-012-3456', 'yes', '2025-04-24 14:19:55'),
(8, 3, 'Lisa Taylor', 'lisa.taylor@example.com', '890-123-4567', 'no', '2025-04-24 14:19:55'),
(9, 4, 'Robert Anderson', 'robert.anderson@example.com', '901-234-5678', 'pending', '2025-04-24 14:19:55'),
(10, 5, 'Jennifer Thomas', 'jennifer.thomas@example.com', '012-345-6789', 'yes', '2025-04-24 14:19:55'),
(11, 6, 'Karthik', 'mvinoth69@gmail.com', '9629146116', 'yes', '2025-04-25 04:02:31');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `alert_type` varchar(20) DEFAULT 'info',
  `user_id` int(11) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `message`, `alert_type`, `user_id`, `link`, `is_read`, `created_at`) VALUES
(1, 'booking_created', 'You\'ve created a new booking for Basic Wedding Package on May 03, 2025', 'info', 4, '../pages/my-bookings.php?view=6', 0, '2025-04-24 11:21:45'),
(2, 'booking_created', 'Vinoth Kumar has created a new booking for Basic Wedding Package on May 03, 2025', 'info', NULL, '../pages/my-bookings.php?view=6', 0, '2025-04-24 11:21:45'),
(3, 'booking_confirmed', 'Your booking for Basic Wedding Package on May 03, 2025 has been confirmed with a $100.00 discount.', 'info', 4, '../pages/my-bookings.php?view=6', 0, '2025-04-24 11:23:25'),
(4, 'guest_invited', 'You\'ve invited Karthik to your event.', 'info', 4, '../pages/my-guests.php', 0, '2025-04-24 11:29:51'),
(5, 'guest_invited', 'You\'ve sent an invitation to Karthik.', 'info', 4, '../pages/my-guests.php', 0, '2025-04-24 11:29:59'),
(6, 'guest_invited', 'You\'ve sent an invitation to Karthik.', 'info', 4, '../pages/my-guests.php', 0, '2025-04-25 00:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `customized` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `image_url`, `description`, `price`, `customized`, `created_by`, `created_at`) VALUES
(1, 'Basic Wedding Package', '../assets/uploads/680a4f81c0db9_C8D3fLVB.jpeg', 'A simple package for small weddings', 2000.00, 0, NULL, '2025-04-24 10:49:55'),
(2, 'Premium Wedding Package', '../assets/uploads/680a4f8a7c474_GOLD+WEDDING+PACKAGE.jpeg', 'Our most popular wedding package with all essential services', 4000.00, 0, NULL, '2025-04-24 10:49:55'),
(3, 'Destination Wedding Package', '../assets/uploads/680a4f91133d2_Destination-Wedding-e1736780129298.jpg', 'The ultimate wedding experience with premium services in your favourite destination', 6000.00, 0, NULL, '2025-04-24 10:49:55'),
(4, 'Corporate Event Package', '../assets/uploads/680a4f9863a83_What-Is-Included-in-A-Typical-Corporate-Event-Package.jpg', 'Perfect for business meetings and corporate events', 3000.00, 0, NULL, '2025-04-24 10:49:55'),
(5, 'Birthday Celebration Package', '../assets/uploads/680a4f9dd5f83_balloon_decor.jpg', 'Make your birthday special with our celebration package', 1500.00, 0, NULL, '2025-04-24 10:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `package_services`
--

CREATE TABLE `package_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_services`
--

INSERT INTO `package_services` (`id`, `package_id`, `service_id`) VALUES
(40, 1, 1),
(41, 1, 4),
(44, 2, 1),
(42, 2, 2),
(43, 2, 3),
(45, 2, 4),
(48, 3, 1),
(46, 3, 2),
(47, 3, 3),
(50, 3, 4),
(49, 3, 5),
(51, 3, 6),
(53, 4, 1),
(52, 4, 2),
(54, 4, 4),
(55, 5, 2),
(56, 5, 3),
(57, 5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`) VALUES
(1, 'Photography', 'Professional photography service for your event', 500.00),
(2, 'Catering', 'Delicious food and beverages for your guests', 1000.00),
(3, 'DJ Service', 'Music and entertainment for your event', 300.00),
(4, 'Venue Decoration', 'Beautiful decorations for your event space', 800.00),
(5, 'Transportation', 'Luxury transportation for the event', 400.00),
(6, 'Videography', 'Professional video recording of your event', 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `can_give_discount` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `address`, `role`, `can_give_discount`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '123-456-7890', '$2y$10$7DgCA0zw6GbzU4lqgJ5pkOgPTB8Ifr/zjfe.C1Qecqh4QEa.hkkxW', '123 Admin St, Admin City', 'admin', 0, '2025-04-24 10:49:55'),
(2, 'Manager User', 'manager@example.com', '234-567-8901', '$2y$10$qaBhE8IYy5rjlQTaz.NFJuM4/Vmo5DcIddDzUDrvQ5PlAooieLHWm', '456 Manager Ave, Manager Town', 'manager', 0, '2025-04-24 10:49:55'),
(3, 'Client User', 'client@example.com', '345-678-9012', '$2y$10$1Br0TMnqdRhiDl26vLr/2.T8MxgOxZ1OTbdlWd0m6YMSAvKtAYVmy', '789 Client Blvd, Client Village', 'client', 0, '2025-04-24 10:49:55'),
(4, 'Vinoth Kumar', 'mvinoth1602@gmail.com', '9894989233', '$2y$10$060rVhkBIvfXnnnAc5rty.trzSSkrG3Xtkb.tXquNdAw44HtriqCO', '309, Saliyar Street, Muthusamypuram', 'client', 0, '2025-04-24 10:59:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_services`
--
ALTER TABLE `package_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `package_id` (`package_id`,`service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `package_services`
--
ALTER TABLE `package_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
