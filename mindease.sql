-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 09:12 AM
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
-- Database: `mindease`
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('breathing','grounding','journal','other') NOT NULL DEFAULT 'other',
  `duration_seconds` int(11) DEFAULT NULL,
  `content_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `actions`
--

INSERT INTO `actions` (`id`, `slug`, `title`, `type`, `duration_seconds`, `content_json`, `created_at`, `updated_at`) VALUES
(1, 'box_breathing_2m', 'Box Breathing — 2 minutes', 'breathing', 120, '[{\"step\": 1, \"text\": \"Find a comfortable seat.\"}, {\"step\": 2, \"text\": \"Inhale for 4 seconds.\"}, {\"step\": 3, \"text\": \"Hold for 4 seconds.\"}, {\"step\": 4, \"text\": \"Exhale for 4 seconds.\"}, {\"step\": 5, \"text\": \"Hold for 4 seconds. Repeat for 2 minutes.\"}]', '2025-11-27 22:03:41', '2025-11-27 22:03:41'),
(2, 'grounding_543', 'Grounding — 5-4-3-2-1', 'grounding', 180, '[{\"step\": 1, \"text\": \"Name 5 things you can see.\"}, {\"step\": 2, \"text\": \"Name 4 things you can touch.\"}, {\"step\": 3, \"text\": \"Name 3 things you can hear.\"}, {\"step\": 4, \"text\": \"Name 2 things you can smell (or would like to).\"}, {\"step\": 5, \"text\": \"Name 1 positive thing about yourself.\"}]', '2025-11-27 22:03:41', '2025-11-27 22:03:41'),
(3, 'micro_journal_1', 'Micro-Journal — 1 sentence', 'journal', 120, '[{\"step\": 1, \"text\": \"Write one sentence describing what is bothering you.\"}, {\"step\": 2, \"text\": \"If helpful, write one small next step to try.\"}]', '2025-11-27 22:03:41', '2025-11-27 22:03:41'),
(4, 'short_walk_5m', 'Short Walk — 5 minutes', 'other', 300, '[{\"step\": 1, \"text\": \"Stand up and step outside or walk around your space for 5 minutes.\"}, {\"step\": 2, \"text\": \"Breathe deeply and notice sensations in your feet.\"}]', '2025-11-27 22:03:41', '2025-11-27 22:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `meta_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_json`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `event_type`, `meta_json`, `created_at`) VALUES
(1, 'save_entry_fatal', '{\"error\":{\"type\":1,\"message\":\"Uncaught ArgumentCountError: The number of variables must match the number of parameters in the prepared statement in C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php:100\\nStack trace:\\n#0 C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php(100): mysqli_stmt_bind_param(Object(mysqli_stmt), \'sssis\', NULL, \'quick\', NULL, 7, \'[]\')\\n#1 {main}\\n  thrown\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php\",\"line\":100},\"buffer\":\"\"}', '2025-11-27 23:12:34'),
(2, 'save_entry_fatal', '{\"error\":{\"type\":1,\"message\":\"Uncaught mysqli_sql_exception: Cannot add or update a child row: a foreign key constraint fails (`mindease`.`entries`, CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`suggestion_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL) in C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php:115\\nStack trace:\\n#0 C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php(115): mysqli_stmt_execute(Object(mysqli_stmt))\\n#1 {main}\\n  thrown\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php\",\"line\":115},\"buffer\":\"\"}', '2025-11-27 23:43:29'),
(3, 'save_entry_fatal', '{\"error\":{\"type\":1,\"message\":\"Uncaught mysqli_sql_exception: Cannot add or update a child row: a foreign key constraint fails (`mindease`.`entries`, CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`suggestion_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL) in C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php:115\\nStack trace:\\n#0 C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php(115): mysqli_stmt_execute(Object(mysqli_stmt))\\n#1 {main}\\n  thrown\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\mindease\\\\src\\\\api\\\\save_entry.php\",\"line\":115},\"buffer\":\"\"}', '2025-11-27 23:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE `entries` (
  `id` bigint(20) NOT NULL,
  `user_hash` varchar(128) DEFAULT NULL,
  `method` enum('quick','text') NOT NULL,
  `input_text` text DEFAULT NULL,
  `slider_value` tinyint(4) DEFAULT NULL,
  `score` tinyint(4) NOT NULL COMMENT '0-10 stress score',
  `type` varchar(64) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `provider` varchar(64) DEFAULT NULL,
  `scale` int(11) NOT NULL DEFAULT 10,
  `tone_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tone_tags`)),
  `suggestion_id` int(11) DEFAULT NULL,
  `feedback` enum('helpful','not_helpful') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entries`
--

INSERT INTO `entries` (`id`, `user_hash`, `method`, `input_text`, `slider_value`, `score`, `type`, `note`, `provider`, `scale`, `tone_tags`, `suggestion_id`, `feedback`, `created_at`) VALUES
(1, NULL, 'quick', NULL, NULL, 5, NULL, NULL, NULL, 10, '[]', 3, NULL, '2025-11-27 23:16:31'),
(4, NULL, 'text', 'devastated', NULL, 4, NULL, NULL, NULL, 10, '[\"off\"]', NULL, NULL, '2025-11-27 23:47:10'),
(5, NULL, '', 'breakup', NULL, 5, NULL, NULL, NULL, 10, '[\"off\"]', NULL, NULL, '2025-11-27 23:54:55'),
(6, NULL, '', 'I feel overwhelmed and exhausted because of studies.', NULL, 6, NULL, NULL, NULL, 10, '[\"anxious\"]', NULL, NULL, '2025-11-28 00:00:42'),
(7, NULL, '', 'I feel overwhelmed and exhausted because of studies.', NULL, 6, NULL, NULL, NULL, 10, '[\"anxious\"]', NULL, NULL, '2025-11-28 00:02:16'),
(8, NULL, '', 'I feel overwhelmed because of exams', NULL, 7, NULL, NULL, NULL, 10, '[\"overwhelmed\",\"anxious\"]', NULL, NULL, '2025-11-28 00:12:21'),
(9, NULL, '', 'i\'m feeling sad', NULL, 5, NULL, NULL, NULL, 10, '[\"sad\"]', NULL, NULL, '2025-11-28 00:22:30'),
(10, NULL, '', 'not so good', NULL, 7, NULL, NULL, NULL, 10, '[\"negative\",\"unhappy\"]', NULL, NULL, '2025-11-28 00:23:06'),
(11, NULL, 'text', 'heart broken', NULL, 8, NULL, NULL, NULL, 10, '[\"sad\",\"heartbroken\"]', NULL, NULL, '2025-11-28 00:24:07'),
(12, NULL, 'quick', 'breakup', NULL, 6, NULL, NULL, NULL, 10, '[]', NULL, NULL, '2025-11-30 20:18:03'),
(13, NULL, '', 'breakup', NULL, 7, NULL, NULL, NULL, 10, '[\"sad\",\"heartbroken\"]', NULL, NULL, '2025-11-30 20:18:13'),
(14, NULL, 'text', 'Not good', NULL, 3, NULL, NULL, NULL, 10, '[\"off\"]', NULL, NULL, '2025-11-30 20:46:56'),
(15, NULL, 'text', 'Not good', NULL, 8, NULL, NULL, NULL, 10, '[\"negative\",\"worried\"]', NULL, NULL, '2025-11-30 20:47:04'),
(16, NULL, '', NULL, NULL, 6, NULL, NULL, NULL, 10, '[]', NULL, NULL, '2025-11-30 20:47:26'),
(17, NULL, 'quick', NULL, NULL, 6, NULL, NULL, NULL, 10, '[\"anxious\"]', 1, NULL, '2025-11-30 21:02:18'),
(18, NULL, 'quick', NULL, NULL, 6, NULL, NULL, NULL, 10, '[\"anxious\"]', 1, NULL, '2025-11-30 21:08:12'),
(19, NULL, 'quick', NULL, NULL, 5, NULL, NULL, NULL, 10, '[\"off\"]', 1, NULL, '2025-11-30 21:08:33'),
(20, NULL, 'quick', NULL, NULL, 6, NULL, NULL, NULL, 10, '[\"anxious\"]', 1, NULL, '2025-11-30 21:08:41'),
(21, NULL, 'quick', NULL, NULL, 8, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:08:53'),
(22, NULL, 'quick', NULL, NULL, 8, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:09:09'),
(23, NULL, 'quick', NULL, NULL, 8, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:09:19'),
(24, NULL, 'quick', NULL, NULL, 8, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:12:31'),
(25, NULL, 'quick', NULL, NULL, 8, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:13:49'),
(26, NULL, 'quick', NULL, NULL, 10, NULL, NULL, NULL, 10, '[\"very_overwhelmed\"]', 1, NULL, '2025-11-30 21:13:53'),
(27, NULL, 'text', 'breakup', NULL, 8, NULL, NULL, NULL, 10, '[\"sad\",\"heartbroken\"]', NULL, NULL, '2025-11-30 21:14:08'),
(28, NULL, 'text', 'devastated', NULL, 9, NULL, NULL, NULL, 10, '[\"devastated\",\"sad\"]', NULL, NULL, '2025-11-30 21:15:00'),
(29, NULL, 'text', 'could not sleep', NULL, 7, NULL, NULL, NULL, 10, '[\"anxiety\",\"frustration\"]', NULL, NULL, '2025-11-30 21:41:38'),
(30, NULL, 'text', 'work stress', NULL, 5, NULL, NULL, NULL, 10, '[\"neutral\"]', 3, NULL, '2025-11-30 21:45:31'),
(31, NULL, 'text', 'tired and devastated', NULL, 7, NULL, NULL, NULL, 10, '[\"negative\"]', 2, NULL, '2025-11-30 21:57:07'),
(32, NULL, 'text', 'sleepy', NULL, 5, NULL, NULL, NULL, 10, '[\"neutral\"]', 3, NULL, '2025-11-30 21:57:42'),
(33, NULL, 'text', 'sleepy', NULL, 5, NULL, NULL, NULL, 10, '[\"neutral\"]', 3, NULL, '2025-11-30 21:59:11'),
(34, NULL, 'quick', NULL, NULL, 6, NULL, NULL, NULL, 10, NULL, 2, NULL, '2025-11-30 22:45:15'),
(35, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[]', 3, NULL, '2025-11-30 23:04:04'),
(36, NULL, 'quick', NULL, 8, 8, '0', NULL, 'local-heuristic', 10, '[]', 1, NULL, '2025-11-30 23:06:41'),
(37, NULL, 'quick', NULL, 8, 8, '0', NULL, 'local-heuristic', 10, '[]', 1, NULL, '2025-11-30 23:06:41'),
(38, NULL, 'text', 'stressed', 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 1, NULL, '2025-11-30 23:24:14'),
(39, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '0', 2, NULL, '2025-11-30 23:25:23'),
(40, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-11-30 23:25:23'),
(41, NULL, 'quick', NULL, 5, 5, '0', NULL, 'local-heuristic', 10, '[]', NULL, NULL, '2025-11-30 23:31:27'),
(42, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[]', NULL, NULL, '2025-11-30 23:31:56'),
(43, NULL, 'quick', NULL, 7, 7, 'quick', NULL, 'openai', 10, '[\"positive\"]', NULL, NULL, '2025-11-30 23:34:33'),
(44, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"positive\"]', NULL, NULL, '2025-11-30 23:34:33'),
(45, NULL, 'quick', NULL, 7, 7, 'quick', NULL, 'openai', 10, '[\"stressed\"]', NULL, NULL, '2025-11-30 23:38:33'),
(46, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"stressed\"]', NULL, NULL, '2025-11-30 23:38:33'),
(47, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-11-30 23:44:24'),
(48, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-11-30 23:44:24'),
(49, NULL, 'quick', NULL, 0, 0, 'quick', NULL, 'openai', 10, '[\"calm\"]', NULL, NULL, '2025-11-30 23:44:51'),
(50, NULL, 'quick', NULL, 0, 0, '0', NULL, 'local-heuristic', 10, '[\"calm\"]', NULL, NULL, '2025-11-30 23:44:51'),
(51, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-11-30 23:44:57'),
(52, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-11-30 23:44:57'),
(53, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"overwhelmed\",\"stressed\"]', 1, NULL, '2025-11-30 23:45:16'),
(54, NULL, 'quick', NULL, 1, 1, '0', NULL, 'local-heuristic', 10, '[\"calm\"]', NULL, NULL, '2025-11-30 23:47:58'),
(55, NULL, 'text', 'Stressed', 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 1, NULL, '2025-11-30 23:48:51'),
(56, NULL, 'text', 'Stressed', 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 1, NULL, '2025-11-30 23:48:59'),
(57, NULL, 'quick', NULL, 5, 5, '0', NULL, 'local-heuristic', 10, '[\"stressed\"]', NULL, NULL, '2025-11-30 23:51:30'),
(58, NULL, 'quick', NULL, 7, 7, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-11-30 23:59:39'),
(59, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-11-30 23:59:39'),
(60, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-11-30 23:59:46'),
(61, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-11-30 23:59:52'),
(62, NULL, 'quick', NULL, 6, 6, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:03:16'),
(63, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:03:16'),
(64, NULL, 'quick', NULL, 7, 7, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:03:34'),
(65, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:03:34'),
(66, NULL, 'quick', NULL, 6, 6, 'quick', NULL, 'openai', 10, '[\"stressed\"]', NULL, NULL, '2025-12-01 00:04:51'),
(67, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"stressed\"]', NULL, NULL, '2025-12-01 00:04:51'),
(68, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"overwhelmed\"]', NULL, NULL, '2025-12-01 00:04:58'),
(69, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"overwhelmed\"]', NULL, NULL, '2025-12-01 00:04:58'),
(70, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"overwhelmed\",\"stressed\"]', 1, NULL, '2025-12-01 00:05:07'),
(71, NULL, 'quick', NULL, 0, 0, '0', NULL, 'local-heuristic', 10, '[\"calm\"]', NULL, NULL, '2025-12-01 00:05:14'),
(72, NULL, 'quick', NULL, 0, 0, 'quick', NULL, 'openai', 10, '[\"calm\"]', NULL, NULL, '2025-12-01 00:05:24'),
(73, NULL, 'quick', NULL, 0, 0, '0', NULL, 'local-heuristic', 10, '[\"calm\"]', NULL, NULL, '2025-12-01 00:05:24'),
(74, NULL, 'quick', NULL, 5, 5, 'quick', NULL, 'openai', 10, '[\"stressed\"]', NULL, NULL, '2025-12-01 00:05:31'),
(75, NULL, 'quick', NULL, 5, 5, '0', NULL, 'local-heuristic', 10, '[\"stressed\"]', NULL, NULL, '2025-12-01 00:05:31'),
(76, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-12-01 00:05:40'),
(77, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"happy\",\"positive\"]', NULL, NULL, '2025-12-01 00:05:40'),
(78, NULL, 'quick', NULL, 6, 6, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:06:25'),
(79, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:06:25'),
(80, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"overwhelmed\",\"stressed\"]', 1, NULL, '2025-12-01 00:06:49'),
(81, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"overwhelmed\",\"stressed\"]', 1, NULL, '2025-12-01 00:06:49'),
(82, NULL, 'quick', NULL, 10, 10, 'quick', NULL, 'openai', 10, '[\"happy\"]', NULL, NULL, '2025-12-01 00:06:59'),
(83, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"happy\"]', NULL, NULL, '2025-12-01 00:06:59'),
(84, NULL, 'quick', NULL, 8, 8, 'quick', NULL, 'openai', 10, '[\"stressed\",\"overwhelmed\"]', 1, NULL, '2025-12-01 00:07:08'),
(85, NULL, 'quick', NULL, 8, 8, '0', NULL, 'local-heuristic', 10, '[\"stressed\",\"overwhelmed\"]', 1, NULL, '2025-12-01 00:07:08'),
(86, NULL, 'quick', NULL, 8, 8, 'quick', NULL, 'openai', 10, '[\"stressed\",\"overwhelmed\"]', 1, NULL, '2025-12-01 00:09:29'),
(87, NULL, 'quick', NULL, 8, 8, '0', NULL, 'local-heuristic', 10, '[\"stressed\",\"overwhelmed\"]', 1, NULL, '2025-12-01 00:09:29'),
(88, NULL, 'text', 'stressed', 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 1, NULL, '2025-12-01 00:11:25'),
(89, NULL, 'quick', NULL, 6, 6, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:12:25'),
(90, NULL, 'quick', NULL, 6, 6, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:12:25'),
(91, NULL, 'quick', NULL, 7, 7, 'quick', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:12:28'),
(92, NULL, 'quick', NULL, 7, 7, '0', NULL, 'local-heuristic', 10, '[\"anxious\"]', 2, NULL, '2025-12-01 00:12:28'),
(93, NULL, 'quick', NULL, 10, 10, '0', NULL, 'local-heuristic', 10, '[\"overwhelmed\"]', NULL, NULL, '2025-12-01 00:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_log`
--

CREATE TABLE `feedback_log` (
  `id` bigint(20) NOT NULL,
  `entry_id` bigint(20) DEFAULT NULL,
  `user_hash` varchar(128) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `email_hash` varchar(128) DEFAULT NULL,
  `settings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings_json`)),
  `consent_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`consent_flags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`);

--
-- Indexes for table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_hash` (`user_hash`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `suggestion_id` (`suggestion_id`);

--
-- Indexes for table `feedback_log`
--
ALTER TABLE `feedback_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entry_id` (`entry_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `feedback_log`
--
ALTER TABLE `feedback_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`suggestion_id`) REFERENCES `actions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback_log`
--
ALTER TABLE `feedback_log`
  ADD CONSTRAINT `feedback_log_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
