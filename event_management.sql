-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2025 at 01:40 PM
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
-- Database: `event_management`
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

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `package_id`, `event_date`, `event_place`, `user_id`, `status`, `package_type`, `discount_amount`, `created_at`, `updated_at`) VALUES
(1, 7, '2025-04-26', 'dgl', 2, 'pending', 'package', 0.00, '2025-04-26 10:05:44', '2025-04-26 10:05:44'),
(2, 7, '2025-04-26', 'test', 17, 'confirmed', 'package', 0.00, '2025-04-26 10:47:48', '2025-04-26 10:51:35'),
(4, 12, '2025-04-25', 'asdf', 17, 'confirmed', 'package', 0.00, '2025-04-26 11:28:57', '2025-04-26 11:29:09');

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

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `booking_id`, `guest_name`, `guest_contact`, `guest_email`, `rsvp_status`, `created_at`) VALUES
(1, 1, 'test', '989898989', 'test@test.com', 0, '2025-04-26 10:06:10'),
(2, 2, 'test', '6382775774', 'lakshmanan@gmail.com', 0, '2025-04-26 10:52:21'),
(3, 2, 'test', 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 0, '2025-04-26 10:52:44'),
(4, 4, 'Lakshmanan', 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 0, '2025-04-26 11:30:10'),
(5, 4, 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 2, '2025-04-26 11:34:01'),
(6, 4, 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 'klakshmanan48@gmail.com', 1, '2025-04-26 11:38:35');

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
(7, 'Luxury Event Package', 'Our most exclusive package with all premium services and personalized attention to detail.', 2000.00, '6803635983c7f1.23004531.jpg', 0, '2025-04-19 14:18:34', '2025-04-19 14:18:34'),
(11, 'asdf', 'asdf', 1600.00, '680cc1951973f2.27096303.jpg', 0, '2025-04-26 16:50:58', '2025-04-26 16:50:58'),
(12, 'asdf', 'test', 800.00, '680cc23f38b9f8.49401855.jpg', 0, '2025-04-26 16:57:10', '2025-04-26 16:57:10');

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
(1, 8, 7, '2025-04-26'),
(2, 10, 7, '2025-04-26'),
(3, 12, 6, '2025-04-26'),
(4, 14, 6, '2025-04-26'),
(5, 8, 5, '2025-04-26'),
(6, 10, 5, '2025-04-26'),
(7, 11, 5, '2025-04-26'),
(8, 7, 4, '2025-04-26'),
(9, 8, 4, '2025-04-26'),
(10, 9, 4, '2025-04-26'),
(11, 10, 4, '2025-04-26'),
(12, 13, 3, '2025-04-26'),
(13, 15, 3, '2025-04-26'),
(14, 8, 11, '2025-04-26'),
(15, 10, 11, '2025-04-26'),
(16, 9, 12, '2025-04-26');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `AuthId`, `AuthKey`, `created_at`, `AuthUsername`, `ip_addr`) VALUES
(146, '1', 'd5806798c72126ff239a9a5be9b05cb3', '2025-04-26 11:36:48', 'admin@iwd.com', '::1'),
(132, '11', 'c7f9ce9289eff1df4f2be601b05225de', '2025-04-23 12:38:55', 'testu3@iwd.com', '::1'),
(145, '1', '8878605c3f500ea9011c482b838eec0e', '2025-04-26 11:32:33', 'admin@iwd.com', '::1'),
(144, '1', 'e0ad4e7f7fff3558ec875f67563799fc', '2025-04-26 11:20:29', 'admin@iwd.com', '::1'),
(143, '1', '1e28e808aaa5a4d39783ef2ea5ada4e5', '2025-04-26 11:03:03', 'admin@iwd.com', '::1'),
(142, '1', 'bde7af1ba52efd8ebc2216b90637002c', '2025-04-26 11:00:48', 'admin@iwd.com', '::1'),
(125, '11', 'bc0b8fa28374725d72139e6d547700d3', '2025-04-23 12:10:30', 'testu3@iwd.com', '::1'),
(126, '11', '165f0518aad16b60886cb390041b8a37', '2025-04-23 12:28:29', 'testu3@iwd.com', '::1'),
(141, '1', '4e4070f429a1cadb59ba4b74fa6c8035', '2025-04-26 10:56:43', 'admin@iwd.com', '::1'),
(124, '11', '6013e2b47409162bdd4f1aa696e7424d', '2025-04-21 17:10:27', 'testu3@iwd.com', '::1'),
(140, '1', '93008b5588de89e0dd2d4bbeef12c45d', '2025-04-26 10:46:54', 'admin@iwd.com', '::1'),
(123, '11', '5f3e38c85ff12fcd02b7a45226277517', '2025-04-21 17:09:05', 'testu3@iwd.com', '::1'),
(150, '1', '8095f4d1ac7fe373936acfb1c5573c53', '2025-04-26 15:00:50', 'admin@iwd.com', '::1'),
(139, '1', 'f91693e4797db92c39b5f2463f144032', '2025-04-26 10:45:32', 'admin@iwd.com', '::1'),
(149, '2', 'ee6987ab240aac24912b41765309dbfc', '2025-04-26 14:50:40', 'user@iwd.com', '::1'),
(138, '1', '7df5ebd2bcc9c8ffecf1b1b75e0f0805', '2025-04-26 10:42:36', 'admin@iwd.com', '::1'),
(116, '7', 'e549aaa57b1ff9e6914ff51fd4c3a856', '2025-04-21 16:20:37', 'manager3@iwd.com', '::1'),
(117, '3', 'd0a292981e4a210ab08ce56490af582b', '2025-04-21 16:21:28', 'manager@iwd.com', '::1'),
(137, '1', 'f5ee130829c6a18c582655478051707a', '2025-04-26 10:17:40', 'admin@iwd.com', '::1'),
(136, '1', '1ebfbe6c46adf5580c6396f63b2c7109', '2025-04-26 00:18:54', 'admin@iwd.com', '::1'),
(148, '1', '65f3310952efaff7c9055569d6918394', '2025-04-26 14:48:29', 'admin@iwd.com', '::1'),
(135, '1', 'd5f8313ccd3b31134566db2fece878f3', '2025-04-25 23:11:14', 'admin@iwd.com', '::1'),
(147, '2', '0a9fd3e08855f6bcca49a1abd5c51329', '2025-04-26 12:09:47', 'user@iwd.com', '::1'),
(151, '2', 'd6b0aafa145c88a79e7395161da1bfba', '2025-04-26 15:02:57', 'user@iwd.com', '::1'),
(152, '2', 'f25816d13a261023a1db35444d50446e', '2025-04-26 15:04:25', 'user@iwd.com', '::1'),
(153, '1', 'b6bace38710f0eb7d3d0a5384adbef07', '2025-04-26 15:21:15', 'admin@iwd.com', '::1'),
(154, '1', '3b1b26e1bf3ef6d6bdd401a0c16ac4bd', '2025-04-26 15:30:41', 'admin@em4.com', '::1'),
(155, '2', '7ddd7c6ede7563029369ab65e1fac3d7', '2025-04-26 15:35:15', 'user@em4.com', '::1'),
(156, '17', '68c0ac3e416edc938573209378acc5ee', '2025-04-26 16:13:05', 'jane@example.com', '::1'),
(157, '17', 'a5e6824110be2b364bed54100fc6ec13', '2025-04-26 16:15:20', 'jane@example.com', '::1'),
(158, '17', 'd8ad8e16351827cea5f7242c21aa6c19', '2025-04-26 16:15:24', 'jane@example.com', '::1'),
(159, '17', 'dfd84e49aaf9efc7550295918d91112e', '2025-04-26 16:17:38', 'jane@example.com', '::1'),
(160, '17', '0154e93aad789d5fe6c726f6a9c7db66', '2025-04-26 16:17:55', 'jane@example.com', '::1'),
(161, '1', '1140e2f440aa1e103e9d0ab0f8060fc0', '2025-04-26 16:19:02', 'admin@em4.com', '::1'),
(162, '17', '80d934b2dba6d6e076a755c512d797ed', '2025-04-26 16:35:50', 'jane@example.com', '::1'),
(163, '17', 'e0827ddaa4ecc20285b425de5a9bb3a6', '2025-04-26 16:45:31', 'jane@example.com', '::1'),
(164, '17', '44bd640218bb487af1dbef71fdfbdd8b', '2025-04-26 16:58:42', 'jane@example.com', '::1');

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
(1, 'app_name', 'EVENT MANAGEMENT V$', '0000-00-00 00:00:00', '2025-04-26 15:29:01'),
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
(1, 'admin@em4.com', 'ADMIN', 'admin123', 'admin', '2022-10-16 02:02:58', '2025-04-26 15:29:51', '6382775774', '', 1, ''),
(2, 'user@em4.com', 'User Test', 'user123', 'client', '2022-10-16 02:02:58', '2025-04-26 16:53:10', '9361458213', '', 0, ''),
(3, 'manager@em4.com', 'MEMBER', 'manager123', 'manager', '2025-04-18 17:31:49', '2025-04-26 15:30:29', '9865321470', '', 1, ''),
(17, 'jane@example.com', 'jane@example.com', 'jane@example.com', 'client', '2025-04-26 16:12:57', '2025-04-26 16:53:15', 'jane@example.com', '', 0, '');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `package_services`
--
ALTER TABLE `package_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
