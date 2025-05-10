-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 10:11 AM
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
-- Database: `iwd`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `event_place` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','confirmed') DEFAULT 'pending',
  `package_type` enum('custom','package') NOT NULL DEFAULT 'package',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_contact` varchar(50) DEFAULT NULL,
  `guest_email` varchar(50) DEFAULT NULL,
  `rsvp_status` int(1) NOT NULL DEFAULT 0 COMMENT '0-pending,1-not attending,2-attending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `id` int(11) NOT NULL,
  `package_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `package`
--

INSERT INTO `package` (`id`, `package_name`, `description`, `price`, `image_url`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'Basic Wedding Package', 'Our essential wedding package covering the basic needs for your special day.', 3000.00, '68036260e974e3.34535284.jpg', 0, '2025-04-19 14:14:25', '2025-04-19 14:14:25'),
(4, 'Premium Wedding Package', 'Comprehensive wedding package with all premium services for an unforgettable celebration', 4000.00, '6803627ff00a86.15046880.jpg', 0, '2025-04-19 14:14:55', '2025-04-19 14:14:55'),
(5, 'Corporate Event Package', 'Complete package for professional corporate events, conferences, and meetings.', 3500.00, '680362970a7d63.61659650.jpg', 0, '2025-04-19 14:15:20', '2025-04-19 14:15:20'),
(6, 'Birthday Celebration Package', 'Make your birthday special with this complete celebration package.', 1500.00, '68036324547b62.88574898.jpg', 0, '2025-04-19 14:17:46', '2025-04-19 14:17:46'),
(7, 'Luxury Event Package', 'Our most exclusive package with all premium services and personalized attention to detail.', 2000.00, '6803635983c7f1.23004531.jpg', 0, '2025-04-19 14:18:34', '2025-04-19 14:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `package_services`
--

CREATE TABLE `package_services` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_services`
--

INSERT INTO `package_services` (`id`, `service_id`, `package_id`, `created_at`) VALUES
(6, 5, 1, '2025-04-18'),
(7, 4, 1, '2025-04-18'),
(8, 6, 1, '2025-04-18'),
(9, 4, 2, '2025-04-19'),
(10, 6, 2, '2025-04-19'),
(11, 7, 3, '2025-04-19'),
(12, 8, 3, '2025-04-19'),
(13, 9, 3, '2025-04-19'),
(14, 12, 4, '2025-04-19'),
(15, 13, 4, '2025-04-19'),
(16, 14, 4, '2025-04-19'),
(17, 15, 4, '2025-04-19'),
(18, 13, 5, '2025-04-19'),
(19, 14, 5, '2025-04-19'),
(20, 15, 5, '2025-04-19'),
(21, 11, 6, '2025-04-19'),
(22, 13, 6, '2025-04-19'),
(23, 15, 6, '2025-04-19'),
(24, 10, 7, '2025-04-19'),
(25, 13, 7, '2025-04-19'),
(26, 15, 7, '2025-04-19'),
(27, 7, 8, '2025-04-19'),
(28, 9, 8, '2025-04-19'),
(29, 13, 8, '2025-04-19'),
(30, 14, 8, '2025-04-19');

-- --------------------------------------------------------

--
-- Table structure for table `page_views`
--

CREATE TABLE `page_views` (
  `id` int(11) NOT NULL,
  `page` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `referer` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `device` varchar(255) NOT NULL,
  `browser` varchar(255) NOT NULL,
  `agent` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id` int(11) NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`id`, `service_name`, `description`, `price`, `created_by`, `created_at`, `updated_at`) VALUES
(7, 'Photography', 'Professional event photography with digital delivery of high-resolution images.', 400.00, 0, '2025-04-19 14:07:36', '2025-04-19 14:11:21'),
(8, 'Catering', 'Full-service catering including appetizers, main course, and desserts for all guests.', 1000.00, 0, '2025-04-19 14:07:56', '2025-04-19 14:11:28'),
(9, 'Venue Decoration', 'Custom decoration services including floral arrangements, lighting, and thematic elements.', 800.00, 0, '2025-04-19 14:08:14', '2025-04-19 14:11:34'),
(10, 'DJ Services', 'Professional DJ with sound equipment, lights, and music selection for the entire event.', 600.00, 0, '2025-04-19 14:08:31', '2025-04-19 14:11:41'),
(11, 'Videography', 'Professional event videography with edited highlight reel and full event coverage', 500.00, 0, '2025-04-19 14:08:45', '2025-04-19 14:11:53'),
(12, 'Transportation', 'Luxury vehicle service for the event, including chauffeur and decorations.', 600.00, 0, '2025-04-19 14:09:00', '2025-04-19 14:11:55'),
(13, 'Live Band', 'Professional live music performance for up to 3 hours during the event.', 750.00, 0, '2025-04-19 14:09:17', '2025-04-19 14:12:01'),
(14, 'Bartending', 'Professional bartenders with custom cocktail menu and full bar service', 500.00, 0, '2025-04-19 14:09:33', '2025-04-19 14:12:07'),
(15, 'Event Planning', 'Comprehensive event planning services including timeline, vendor coordination, and on-site management.', 650.00, 0, '2025-04-19 14:09:51', '2025-04-21 16:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `AuthId` varchar(50) NOT NULL,
  `AuthKey` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `AuthUsername` varchar(255) NOT NULL,
  `ip_addr` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(255) NOT NULL,
  `profile` varchar(255) NOT NULL,
  `discount_permission` int(1) NOT NULL DEFAULT 0,
  `address` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `fullname`, `password`, `role`, `created_at`, `updated_at`, `phone`, `profile`, `discount_permission`, `address`) VALUES
(1, 'admin@ee.com', 'Admin', 'admin123', 'admin', '2022-10-16 02:02:58', '2025-04-24 10:00:05', '6382775774', '', 1, ''),
(2, 'user@gmail.com', 'User', 'user123', 'client', '2022-10-16 02:02:58', '2025-04-24 10:00:15', '9361458213', '', 0, ''),
(3, 'manager@ee.com', 'Manager', 'manager123', 'manager', '2025-04-18 17:31:49', '2025-04-24 10:00:21', '9865321470', '', 1, ''),
(4, 'manager2@ee.com', 'Manager2', 'manager123', 'manager', '2025-04-18 17:46:39', '2025-04-24 10:00:36', '9632587410', '', 0, ''),
(5, 'client@gmail.com', 'Client', 'client123', 'client', '2025-04-18 17:47:23', '2025-04-24 09:59:35', '9874563210', '', 0, ''),
(7, 'manager3@ee.com', 'Manager3', 'manager123', 'manager', '2025-04-19 13:33:29', '2025-04-24 10:00:45', '7896541230', '', 0, '3333333'),
(8, 'testu@gmail.com', 'test u', '123456', 'client', '2025-04-21 17:04:52', '2025-04-24 10:01:01', '9874563210', '', 0, 'ddd'),
(9, 'testu1@gmail.com', 'test u1', '123456', 'client', '2025-04-21 17:06:17', '2025-04-24 10:01:04', '9874563210', '', 0, 'ddd'),
(10, 'testu2@gmail.com', 'test u2', '123456', 'client', '2025-04-21 17:07:11', '2025-04-24 10:01:07', '9874563210', '', 0, 'eee'),
(11, 'testu3@gmail.com', 'test u3', '123456', 'client', '2025-04-21 17:08:47', '2025-04-24 10:01:09', '9632587410', '', 0, 'www');

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
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_services`
--
ALTER TABLE `package_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_views`
--
ALTER TABLE `page_views`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `package_services`
--
ALTER TABLE `package_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `page_views`
--
ALTER TABLE `page_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
