-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 01:41 PM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
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
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `fullname`, `password`, `role`, `created_at`, `updated_at`, `phone`, `profile`, `discount_permission`, `address`) VALUES
(1, 'admin@iwd.com', 'IWD', 'admin123', 'admin', '2022-10-16 02:02:58', '2025-04-21 14:03:44', '6382775774', '', 1, ''),
(2, 'user@iwd.com', 'User IWD', 'user123', 'client', '2022-10-16 02:02:58', '2025-04-19 12:49:05', '9361458213', '', 0, ''),
(3, 'manager@iwd.com', 'manager', 'manager123', 'manager', '2025-04-18 17:31:49', '2025-04-21 14:03:39', '9865321470', '', 1, ''),
(4, 'testmanager@gmail.com', 'test manager', 'manager123', 'manager', '2025-04-18 17:46:39', '2025-04-19 13:33:01', '9632587410', '', 0, ''),
(5, 'client@gmail.com', 'client', 'client123', 'client', '2025-04-18 17:47:23', '2025-04-18 17:47:23', '9874563210', '', 0, ''),
(6, 'testclient@gmail.com', 'test client1', 'client123', 'client', '2025-04-18 17:48:58', '2025-04-19 12:37:04', '8794561230', '', 0, 'test 123'),
(7, 'manager3@iwd.com', 'manager 3', 'manager123', 'manager', '2025-04-19 13:33:29', '2025-04-19 13:33:42', '7896541230', '', 0, '3333333'),
(8, 'testu@iwd.com', '', '123456', 'client', '2025-04-21 17:04:52', '2025-04-21 17:04:52', '9874563210', '', 0, 'ddd'),
(9, 'testu1@iwd.com', 'test u1', '123456', 'client', '2025-04-21 17:06:17', '2025-04-21 17:06:17', '9874563210', '', 0, 'ddd'),
(10, 'testu2@iwd.com', 'test u2', '123456', 'client', '2025-04-21 17:07:11', '2025-04-21 17:07:11', '9874563210', '', 0, 'eee'),
(11, 'testu3@iwd.com', 'test u3', '123456', 'client', '2025-04-21 17:08:47', '2025-04-21 17:08:47', '9632587410', '', 0, 'www');

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

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `package_id`, `event_date`, `event_place`, `user_id`, `status`, `package_type`, `discount_amount`, `created_at`, `updated_at`) VALUES
(1, 8, '2025-04-19', 'dgl', 2, 'pending', 'custom', 0.00, '2025-04-19 11:33:40', '2025-04-19 11:35:49'),
(2, 6, '2025-04-20', 'vdr', 2, 'confirmed', 'package', 100.00, '2025-04-19 11:36:24', '2025-04-21 10:51:42'),
(3, 5, '2025-04-23', 'ddd', 2, 'pending', 'package', 0.00, '2025-04-21 10:56:13', '2025-04-21 10:56:13'),
(4, 4, '2025-04-24', 'aaaa', 11, 'pending', 'package', 0.00, '2025-04-21 11:39:20', '2025-04-21 11:39:20');

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
  `rsvp_status` text NOT NULL DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `booking_id`, `guest_name`, `guest_contact`, `guest_email`, `rsvp_status`, `created_at`) VALUES
(3, 2, 'tttt', '567567567567', 'ttt@hh.com', '', '2025-04-21 07:38:05'),
(4, 2, 'sssss', '87878787878', 'sss@dfgdf.com', '', '2025-04-21 07:43:56'),
(5, 3, 'laxu', '9874563210', 'laxu@gg.com', '', '2025-04-21 10:56:43');

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
(1, 'test pack', 'dsfsd dfssdf', 350.00, '68021446249d80.21435596.jpg', 0, '2025-04-18 14:01:22', '2025-04-18 14:28:49'),
(2, 'package 2', '', 250.00, '680354a71cc558.43348559.jpg', 0, '2025-04-19 13:15:45', '2025-04-19 13:15:45'),
(3, 'Basic Wedding Package', 'Our essential wedding package covering the basic needs for your special day.', 3000.00, '68036260e974e3.34535284.jpg', 0, '2025-04-19 14:14:25', '2025-04-19 14:14:25'),
(4, 'Premium Wedding Package', 'Comprehensive wedding package with all premium services for an unforgettable celebration', 4000.00, '6803627ff00a86.15046880.jpg', 0, '2025-04-19 14:14:55', '2025-04-19 14:14:55'),
(5, 'Corporate Event Package', 'Complete package for professional corporate events, conferences, and meetings.', 3500.00, '680362970a7d63.61659650.jpg', 0, '2025-04-19 14:15:20', '2025-04-19 14:15:20'),
(6, 'Birthday Celebration Package', 'Make your birthday special with this complete celebration package.', 1500.00, '68036324547b62.88574898.jpg', 0, '2025-04-19 14:17:46', '2025-04-19 14:17:46'),
(7, 'Luxury Event Package', 'Our most exclusive package with all premium services and personalized attention to detail.', 2000.00, '6803635983c7f1.23004531.jpg', 0, '2025-04-19 14:18:34', '2025-04-19 14:18:34'),
(8, 'custom package', 'rrr', 2450.00, 'undefined', 2, '2025-04-19 17:03:40', '2025-04-19 17:03:40');

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

--
-- Dumping data for table `page_views`
--

INSERT INTO `page_views` (`id`, `page`, `ip`, `date`, `time`, `referer`, `session_id`, `device`, `browser`, `agent`) VALUES
(1, 'index.php', '117.252.135.167', '2024-12-23', '18:47:45', '', 'uqao5s96ad5pgfkip2tr4q0qqr', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(2, 'index.php', '117.252.135.167', '2024-12-23', '18:47:47', '', 'uqao5s96ad5pgfkip2tr4q0qqr', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(3, 'index.php', '61.0.126.136', '2024-12-24', '14:16:20', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(4, 'index.php', '61.0.126.136', '2024-12-24', '14:18:00', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(5, 'index.php', '61.0.126.136', '2024-12-24', '14:18:01', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(6, 'index.php', '120.60.205.111', '2024-12-24', '17:40:52', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(7, 'index.php', '120.60.205.111', '2024-12-24', '17:40:59', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(8, 'index.php', '120.60.205.111', '2024-12-24', '17:41:00', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(9, 'index.php', '120.60.205.111', '2024-12-24', '17:42:33', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(10, 'index.php', '120.60.205.111', '2024-12-24', '17:42:36', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(11, 'index.php', '120.60.205.111', '2024-12-24', '17:42:50', '', '3s5giphb0a1ooj320k0k97c9i4', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
(12, 'index.php', '120.60.205.111', '2024-12-24', '17:43:04', '', '3s5giphb0a1ooj320k0k97c9i4', 'Android', 'Google Chrome', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36'),
(13, 'index.php', '120.60.205.111', '2024-12-24', '17:43:09', '', '3s5giphb0a1ooj320k0k97c9i4', 'Android', 'Google Chrome', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36'),
(14, 'index.php', '120.60.205.111', '2024-12-24', '17:43:26', '', '3s5giphb0a1ooj320k0k97c9i4', 'Android', 'Google Chrome', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36'),
(15, 'index.php', '120.60.205.111', '2024-12-24', '17:43:31', '', '3s5giphb0a1ooj320k0k97c9i4', 'Android', 'Google Chrome', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36'),
(16, 'index.php', '::1', '2024-12-25', '12:33:57', '', 'dgj2pc727kibbh6ibecr4mov7e', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'),
(17, 'index.php', '::1', '2024-12-25', '13:11:50', 'http://localhost/justdial/admin/enquirymessages', 'dgj2pc727kibbh6ibecr4mov7e', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'),
(18, 'index.php', '::1', '2024-12-25', '17:34:03', '', 'dgj2pc727kibbh6ibecr4mov7e', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'),
(19, 'index.php', '::1', '2024-12-25', '17:35:17', '', 'dgj2pc727kibbh6ibecr4mov7e', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'),
(20, 'index.php', '::1', '2024-12-25', '17:35:19', '', 'dgj2pc727kibbh6ibecr4mov7e', 'Windows', 'Google Chrome', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36');

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
(5, 'service3', 'service3 test', 150.00, 0, '2025-04-18 12:59:53', '2025-04-18 12:59:53'),
(4, 'service2', 'test', 100.00, 0, '0000-00-00 00:00:00', '2025-04-18 12:59:18'),
(6, 'service4', 'test bset', 250.00, 0, '2025-04-18 13:57:23', '2025-04-18 13:57:23'),
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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `AuthId`, `AuthKey`, `created_at`, `AuthUsername`, `ip_addr`) VALUES
(111, '2', 'd9304ecea41a10796df409520d6ec60f', '2025-04-21 11:41:46', 'user@iwd.com', '::1'),
(124, '11', '6013e2b47409162bdd4f1aa696e7424d', '2025-04-21 17:10:27', 'testu3@iwd.com', '::1'),
(121, '1', '565a032e34289b0927fa9095b86168a8', '2025-04-21 16:26:54', 'admin@iwd.com', '::1'),
(123, '11', '5f3e38c85ff12fcd02b7a45226277517', '2025-04-21 17:09:05', 'testu3@iwd.com', '::1'),
(120, '2', '710ffb8502e54d322aa5a398391669e9', '2025-04-21 16:25:29', 'user@iwd.com', '::1'),
(112, '1', '9a0057fe866f32c6fe48ce139a1c2eae', '2025-04-21 13:32:03', 'admin@iwd.com', '::1'),
(118, '2', '04ca7271bda03253497d4131a419aa46', '2025-04-21 16:21:50', 'user@iwd.com', '::1'),
(119, '1', 'e6f4deaec9284b870483e0a3d0cdb1b5', '2025-04-21 16:22:52', 'admin@iwd.com', '::1'),
(116, '7', 'e549aaa57b1ff9e6914ff51fd4c3a856', '2025-04-21 16:20:37', 'manager3@iwd.com', '::1'),
(117, '3', 'd0a292981e4a210ab08ce56490af582b', '2025-04-21 16:21:28', 'manager@iwd.com', '::1'),
(115, '1', '61dd799b96a5481d1c970c0b84624c76', '2025-04-21 16:19:12', 'admin@iwd.com', '::1'),
(113, '1', 'e4d24c941f14b856884f617821366e49', '2025-04-21 16:14:31', 'admin@iwd.com', '::1'),
(114, '2', 'f701d2627e1f94cb0f311c17f5d17f4c', '2025-04-21 16:15:10', 'user@iwd.com', '::1'),
(122, '1', 'd2aae96effef8b3521bc1891b67e27f3', '2025-04-21 16:51:09', 'admin@iwd.com', '::1'),
(110, '2', 'f9ff42f448c3b18936ec9080c2692f13', '2025-04-19 16:48:18', 'user@iwd.com', '::1');

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

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Sri Velmurugan Transport', '0000-00-00 00:00:00', '2024-04-08 15:09:13'),
(2, 'phone', '9626824999', '0000-00-00 00:00:00', '2024-04-13 07:06:05'),
(3, 'email', 'contactus@sriveltransport.com', '0000-00-00 00:00:00', '2024-04-08 15:36:06'),
(4, 'address', 'Arappalayam, Madurai', '0000-00-00 00:00:00', '2024-04-08 15:31:59'),
(5, 'city', 'undefined', '0000-00-00 00:00:00', '2024-04-08 15:18:40'),
(6, 'latitude', '9.930815', '0000-00-00 00:00:00', '2024-04-08 15:16:40'),
(7, 'longitude', '78.105562', '0000-00-00 00:00:00', '2024-04-08 15:16:40'),
(8, 'instagram', 'https://www.instagram.com/sri_velmurgan_transport_/', '0000-00-00 00:00:00', '2024-04-09 06:03:44'),
(9, 'facebook', 'https://www.facebook.com/profile.php?id=61558660821608', '0000-00-00 00:00:00', '2024-04-15 06:19:19'),
(10, 'twitter', '#', '0000-00-00 00:00:00', '2024-04-08 15:27:21'),
(11, 'linkedin', '#', '0000-00-00 00:00:00', '2024-12-14 07:11:33'),
(12, 'youtube', '#', '0000-00-00 00:00:00', '2024-04-08 15:27:21'),
(13, 'whatsapp', '9626824999', '0000-00-00 00:00:00', '2024-04-15 06:19:26'),
(14, 'short_des', '', '0000-00-00 00:00:00', '2024-04-06 10:44:11'),
(15, 'keywords', 'Sri Velmurugan Transport, Sri Velmurugan Transport online booking, Online bus tickets, Online Sleeper bus tickets, Sri Velmurugan Transport online bus ticket booking', '0000-00-00 00:00:00', '2024-04-14 07:13:49'),
(16, 'long_des', 'Book you tour packages with Sri Velmurugan Transport today and make your trip memorable. Easy rescheduling, cancellation & live tracking features are available.', '0000-00-00 00:00:00', '2024-04-15 06:37:15'),
(17, 'appbar_logo', '66193f22dd4ee9.16966507.png', '0000-00-00 00:00:00', '2024-04-12 14:03:18'),
(18, 'sidebar_logo', '66195fb5791f61.63300440.png', '0000-00-00 00:00:00', '2024-04-12 16:22:17'),
(19, 'footer_logo', '66195f969ad7e7.94616553.png', '0000-00-00 00:00:00', '2024-04-12 16:21:46'),
(20, 'banner_titles', 'Your Travel Partner Sri Velmurugan Transport\r\nA Golden Jubilee in Transportation Excellence', '0000-00-00 00:00:00', '2024-04-16 12:53:54'),
(21, 'banner_subtitles', 'Your wheels to the world\r\n50 Years of Services', '0000-00-00 00:00:00', '2024-04-16 12:53:54'),
(22, 'banner_images', '661641c2d683f1.37316966.jpg,66164166816b84.66839680.jpg,', '0000-00-00 00:00:00', '2024-04-10 07:40:28'),
(23, 'opening_timing', 'Mon to Sat: 8.00 am - 9.00 pm', '0000-00-00 00:00:00', '2024-04-08 15:26:55'),
(24, 'testimonial_usernames', 'Josephine Freeda\r\nJames Paulraj\r\nRaj Kumar', '0000-00-00 00:00:00', '2024-04-17 07:48:37'),
(25, 'testimonial_messages', 'Exceptional service and top-notch quality! From start to finish, my experience was nothing short of outstanding. The attention to detail and dedication of the staff truly stood out, making my journey a memorable one. I highly recommend this service to anyone seeking reliability and excellence. Keep up the fantastic work\r\nI\'m impressed by the convenient private bus services connecting Madurai to Theni, Bodi, and Dindigul. With numerous options available, travelers have hassle-free access to these destinations. Kudos to the operators for their commitment to reliability and efficiency. Highly recommended for anyone traveling in the region\r\nThe journey itself was smooth and timely, and despite some minor inconveniences, the conductors were helpful in addressing any concerns. I appreciate the efforts of the transportation staff in ensuring passenger safety and timely travel', '0000-00-00 00:00:00', '2024-04-17 07:48:37'),
(26, 'testimonial_images', '661f7ebcc3cd73.32481004.jpg,6618e6a21f6388.15077516.png,6618e61d61b657.93072515.png,', '0000-00-00 00:00:00', '2024-04-17 07:48:37'),
(27, 'loader_logo', '66195ece2ad6e9.14630981.png', '0000-00-00 00:00:00', '2024-04-12 16:18:28'),
(28, 'gallery_images', '6616590fdc8202.35012758.jpg,6616590ff08db4.36053154.jpg,6616590ff2c635.00413805.jpg,6616590fd63793.12673228.jpg,6616590fc570c8.16021659.jpg,6616590fd7a773.36864087.jpg,66165910033840.86204597.jpg,66165992e18cb3.77194999.jpg,66165992e43a44.50797842.jpg,', '0000-00-00 00:00:00', '2024-04-23 08:02:37'),
(29, 'faq_questions', '1)	 Is there a discount for group bookings or frequent travelers?\r\n2)	How do I contact customer support for assistance or inquiries?\r\n3)	What payment methods do you accept for booking bus tickets online?\r\n4)   How to book a bus ?', '0000-00-00 00:00:00', '2024-04-17 07:59:40'),
(30, 'faq_answers', 'Yes, we offer discounts for group bookings and frequent travelers. For group bookings, discounts are available for groups of a certain size traveling on the same bus and route. For frequent travelers, we have a loyalty program that rewards our regular customers with discounts, special offers, and exclusive benefits. To avail of these discounts, please contact our customer service team or check the \'Deals\' or \'Promotions\' section on our website for more information.\r\nIf you need assistance or have any inquiries regarding your booking, bus schedules, payments, or any other concerns, you can contact our customer support team through various channels. You can reach us via phone, email, or live chat, the contact details of which are provided on our website. Our dedicated customer support team is available to assist you with any questions or issues you may have, ensuring a smooth and enjoyable travel experience with us.\r\nWe accept various payment methods for booking bus tickets online, including credit/debit cards (Visa, MasterCard, American Express), online banking, and mobile payment options (G-pay, UPI, phonepay). Please ensure that your chosen payment method is valid and has sufficient funds to complete the transaction. All online payments are processed securely to protect your financial information.\r\nGo to the booking page and submit the form with your travel details, including the departure and arrival locations, travel dates, number of passengers, and any other preferences or requirements you may have. Once you have submitted the form, you will be presented with a list of available bus services and schedules for your chosen route. Select the desired bus service, seat type, and fare, and proceed to the payment page to complete your booking. You will receive a confirmation email with your e-ticket and booking details once the payment is successful.', '0000-00-00 00:00:00', '2024-04-17 07:59:40'),
(31, 'booking_youarea', 'Student\r\nTeacher/Professor\r\nCorporate\r\nSelf Employed', '0000-00-00 00:00:00', '2024-04-17 07:57:46'),
(32, 'booking_bustypes', 'A/C (54 seat)\r\nNon A/C (58 seat)\r\nA/C (25 seat)\r\nNon A/C (25 seat)', '0000-00-00 00:00:00', '2024-04-17 07:57:46'),
(33, 'feature_names', 'Air Conditioning\r\nFree Wi-Fi\r\nReclining Leather Seats\r\nExtra Legroom\r\nIndividual Power Outlets\r\nWater Bottle\r\nAudio Systems\r\nEco Friendly Engines\r\nUnder Bus Storage\r\nOverhead Storage\r\nOutstanding Safety\r\nAmbient Lighting\r\nReading Lamp', '0000-00-00 00:00:00', '2024-04-10 14:37:46'),
(34, 'feature_images', '6616a4733c5145.18570952.png,66169fa1ab6ec7.78997248.png,6616a11aaadac0.35467894.png,66169e62268223.18288272.png,66169e6229fde6.90863654.png,66169e62364061.98971607.png,6616a402943810.05527511.png,66169e6231bbb6.41180246.png,66169e62358914.39419116.png,66169e62313d18.55333340.png,66169e623a1874.20425203.png,6616a402890829.85739933.png,6616a59b99aaa5.18852575.png,', '0000-00-00 00:00:00', '2024-04-10 14:43:44'),
(35, 'full_address', 'Sri Velmurugan Transport, West Ponnagaram 8th Street, Pethaniapuram 2, Arappalayam, Madurai, Tamil Nadu 625016', '0000-00-00 00:00:00', '2024-04-16 10:24:16'),
(36, 'team_usernames', 'Lakshmanan\r\nKumar\r\nGowtham', '0000-00-00 00:00:00', '2024-04-09 06:05:59'),
(37, 'team_positions', 'Driver\r\nDriver\r\nDriver', '0000-00-00 00:00:00', '2024-04-09 06:05:59'),
(38, 'team_images', '', '0000-00-00 00:00:00', '2024-04-10 14:32:19'),
(39, 'feature_2_names', 'Fast Booking\r\nStress Free\r\nSpend Less', '0000-00-00 00:00:00', '2024-04-10 14:22:06'),
(40, 'feature_2_description', 'Enjoy buying your bus tickets online from home or on the go, with our mobile friendly site.\r\nThe simplest and stress free way to book your bus travel all in one purchase.\r\nWe have the affordable prices and the best carriers all in one place.', '0000-00-00 00:00:00', '2024-04-10 14:22:06'),
(41, 'feature_2_images', '6616a8aba36746.06848904.png,6616a8aba3a332.72308269.png,6616a8aba771a7.76009360.png,', '0000-00-00 00:00:00', '2024-04-10 14:56:47'),
(42, 'feature_2_descriptions', 'Enjoy buying tour packages online from home or on the go, with our mobile friendly site.\r\nThe simplest and stress free way to book your bus travel all in one purchase.\r\nWe have the affordable prices and the best carriers all in one place.', '0000-00-00 00:00:00', '2024-04-15 06:37:02'),
(43, 'welcome_message', 'Hi there. How can I help you today?', '0000-00-00 00:00:00', '2024-04-11 11:25:35'),
(44, 'collect_phone', 'Type your phone number so we can contact you later.', '0000-00-00 00:00:00', '2024-04-11 11:25:35'),
(45, 'collect_email', 'Type your email address so we can contact you later.', '0000-00-00 00:00:00', '2024-04-11 11:25:35'),
(46, 'collect_name', 'Type your name so we can contact you later.', '0000-00-00 00:00:00', '2024-04-11 11:25:35'),
(47, 'chatbot_logo', '66196ba012e138.14091857.png', '0000-00-00 00:00:00', '2024-04-12 17:13:07'),
(48, 'no_saved_reply', 'Sorry, I am not able to understand your message.', '0000-00-00 00:00:00', '2024-04-12 08:46:07'),
(49, 'old_title', 'From the start', '2024-04-12 09:40:48', '2024-04-12 09:40:48'),
(50, 'new_title', 'To Today', '2024-04-12 09:40:48', '2024-04-12 09:40:48'),
(51, 'old_des', 'For over three decades, our transport company has been dedicated to serving our community with the fastest and most reliable transportation services. Over the years, we have continually adapted and innovated to meet the evolving needs of our community.', '2024-04-12 09:40:48', '2024-04-12 09:40:48'),
(52, 'new_des', 'Today, we remain as committed as ever to providing exceptional service and fostering strong connections within our community.', '2024-04-12 09:40:48', '2024-04-12 09:40:48'),
(53, 'jubliee_old_title', 'From the start', '2024-04-12 09:41:45', '2024-04-12 09:41:45'),
(54, 'jubliee_new_title', 'To Today', '2024-04-12 09:41:45', '2024-04-12 09:41:45'),
(55, 'jubliee_old_des', 'For over three decades, our transport company has been dedicated to serving our community with the fastest and most reliable transportation services. Over the years, we have continually adapted and innovated to meet the evolving needs of our community.', '2024-04-12 09:41:45', '2024-04-12 09:41:45'),
(56, 'jubliee_new_des', 'Today, we remain as committed as ever to providing exceptional service and fostering strong connections within our community.', '2024-04-12 09:41:45', '2024-04-12 09:41:45'),
(57, 'about_header_image', '66191554ef11b5.72710530.jpg', '2024-04-12 09:57:47', '2024-04-12 11:04:55'),
(58, 'gallery_header_image', '661969e92f7502.42121438.jpg', '2024-04-12 09:57:47', '2024-04-12 17:05:49'),
(59, 'faq_header_image', '6619152319b0b7.19884120.jpg', '2024-04-12 09:57:47', '2024-04-12 11:04:05'),
(60, 'contact_header_image', '66196501d9f134.25120748.jpg', '2024-04-12 09:57:47', '2024-04-12 16:44:54'),
(61, 'blog_header_image', '6619150ad22f84.32437136.jpg', '2024-04-12 09:57:47', '2024-04-12 11:03:41'),
(62, 'booking_header_image', '661914bad74938.14621758.jpg', '2024-04-12 09:57:47', '2024-04-12 11:02:23'),
(63, 'sharing_thumbnail', '661cebf15cdbe6.96404601.jpg', '2024-04-14 07:22:14', '2024-04-15 08:57:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(255) NOT NULL,
  `type` int(10) NOT NULL DEFAULT 0 COMMENT '0-user,1-service man',
  `service_type` text DEFAULT NULL,
  `service_desc` text DEFAULT NULL,
  `gender` varchar(255) DEFAULT '',
  `address` text DEFAULT NULL,
  `govt_emp` text NOT NULL DEFAULT '0' COMMENT '0-non govt ,1-govt',
  `govt_rollno` varchar(255) NOT NULL DEFAULT '',
  `year_of_exp` text NOT NULL DEFAULT '0',
  `password` text NOT NULL,
  `profile_picture` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `fullname`, `created_at`, `updated_at`, `phone`, `type`, `service_type`, `service_desc`, `gender`, `address`, `govt_emp`, `govt_rollno`, `year_of_exp`, `password`, `profile_picture`) VALUES
(1, 'aaa@aa.com', 'aaaaaa', '2024-12-25 09:51:02', '2024-12-25 14:22:10', '78978978987', 0, '', '', '', '', '0', '', '0', 'aaa123', ''),
(2, 'bbb@bb.com', 'bbb', '2024-12-25 09:51:02', '2024-12-27 13:58:23', '8765756767', 1, 'test12', '', 'male', '', '0', '1234509', '0', 'bbb123', ''),
(3, 'admin@user.com', 'user1', '0000-00-00 00:00:00', '2024-12-26 14:32:11', '9898989898', 0, NULL, NULL, 'Male', 'dgl', '0', '', '0', '1234567', ''),
(4, 'admin@service.com', 'service1', '0000-00-00 00:00:00', '2024-12-27 13:56:49', '9696969696', 1, 'service1', 'test', 'Male', 'dgl1', '0', '123567889', '5', '1234567', ''),
(5, 'dddd@ddd.com', 'dddd', '0000-00-00 00:00:00', '2024-12-27 13:56:48', '9632587410', 1, 'test12', 'ddd', 'Male', 'ddd', '1', '13243234', '5', '1234567', ''),
(6, 'ssss@sss.com', 'ssss', '0000-00-00 00:00:00', '2024-12-27 13:56:52', '9333587410', 1, 'test12', 'sss', 'Male', 'sss', '1', '963258', '3', '1234567', ''),
(7, 'serv@sss.com', 'sertvv', '0000-00-00 00:00:00', '2024-12-27 14:16:43', '9632587410', 1, 'service1', 'sdsd', 'Male', 'sdad', '1', '321456', '3', '1234567', ''),
(8, 'eb@ee.com', 'eb1', '0000-00-00 00:00:00', '2024-12-30 16:27:42', '9887676636', 1, 'service2', 'sdfsdf', 'Male', 'dsfsdf', '1', '3432423', '4', '1234567', 'uploads/1.jpg'),
(9, 'ccc@ccc.com', 'ccc', '0000-00-00 00:00:00', '2024-12-31 12:52:27', '9585999922', 1, 'service2', 'ssss', 'Female', 'sss', '0', '', '5', '123456', 'uploads/user-img.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
