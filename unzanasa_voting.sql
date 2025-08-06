-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost: 8082
-- Generation Time: Aug 06, 2025 at 10:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unzanasa_voting`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `full_name`, `created_at`, `last_login`) VALUES
(25, 'test_1754354874', '$2y$10$H0yIuDMbl7WINvVLnvTkROwEyY7m22TjzXEjSUpYRKrZJJJ0384C6', 'test1754354874@example.com', 'Test User', '2025-08-05 00:47:54', NULL),
(26, 'apolllos', '$2y$10$PAftjBkdTPlWBoQCD4fVBOgQPnH9XkN4jpLZ4h7m9D7toi2T/kXW6', 'apollosmwaba@gmail.com', 'apollospatel', '2025-08-05 13:23:46', NULL),
(27, 'admin', '$2y$10$MmSDi1gBV1yH37NfsxcuYOCT9XSiKu/Zy.KSZ5N0c5cNQ3kggE3vy', 'admin@email', 'admin', '2025-08-05 13:25:54', '2025-08-05 23:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `photo` varchar(150) DEFAULT NULL,
  `platform` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `election_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `position_id`, `firstname`, `lastname`, `photo`, `platform`, `created_at`, `updated_at`, `election_id`, `name`, `bio`, `status`) VALUES
(20, 40, 'Apollos', 'Mwaba', 'uploads/candidates/candidate_6891fd8c112c0.jpg', 'ASHBDIBIASDBO', '2025-08-05 12:48:12', '2025-08-05 13:08:46', 13, 'UNZA aPOLLOS', 'ASKJDAS', 1),
(28, 44, 'APOLLOS', 'MWABA', 'uploads/candidates/candidate_68928d6de1fa3.jpg', 'HHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHHH', '2025-08-05 23:02:05', '2025-08-05 23:02:05', 17, 'APOLLOS', 'JJJJJJJJJJJJJJJJJJJJJJJJJJJJJJJ', 1),
(29, 44, 'BROOKS', 'PATEL', 'uploads/candidates/candidate_68928dbc123f6.jpg', 'KJJJJJJJJJJ', '2025-08-05 23:03:24', '2025-08-05 23:03:24', 17, 'BROOKS', 'KLLLLLLLLLLL', 1),
(30, 44, 'BROOKS', 'PATEL', 'uploads/candidates/candidate_68928de9ec6e4.jpg', 'DCSDCSDCD', '2025-08-05 23:04:09', '2025-08-05 23:04:18', 17, 'BROOKS', 'HKHBKB', 1),
(31, 40, 'John', 'Doe', NULL, 'Making student life better with innovative programs and transparent leadership.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 13, 'John Doe', NULL, 1),
(32, 40, 'Jane', 'Smith', NULL, 'Advocating for student rights and improving campus facilities.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 13, 'Jane Smith', NULL, 1),
(33, 43, 'Mike', 'Johnson', NULL, 'Promoting diversity and inclusion on campus.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 16, 'Mike Johnson', NULL, 1),
(34, 43, 'Sarah', 'Williams', NULL, 'Enhancing academic support and resources for all students.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 16, 'Sarah Williams', NULL, 1),
(35, 44, 'David', 'Brown', NULL, 'Building stronger connections between students and faculty.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 17, 'David Brown', NULL, 1),
(36, 44, 'Lisa', 'Davis', NULL, 'Creating more opportunities for student engagement and leadership.', '2025-08-05 23:09:41', '2025-08-05 23:09:41', 17, 'Lisa Davis', NULL, 1),
(37, 44, 'BROOKS', 'PATEL', 'uploads/candidates/candidate_68928f763a9f4.jpg', 'k nnnnnnnnnnnnnmbbbbbbbb', '2025-08-05 23:10:46', '2025-08-05 23:10:46', 17, 'BROOKS', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `title` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `name`, `description`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`, `title`, `position`) VALUES
(13, '', 'Vote for the president', '2025-08-06 00:27:10', '2025-09-05 01:27:10', 'active', NULL, '2025-08-04 23:02:56', '2025-08-05 23:27:10', 'UNZANASA', 'General'),
(16, '', 'some', '2025-08-06 00:27:10', '2025-09-05 01:27:10', 'active', 27, '2025-08-05 22:42:16', '2025-08-05 23:27:10', 'some', 'General'),
(17, '', 'VOTE FOR CS PRESIDENT', '2025-08-06 00:27:10', '2025-09-05 01:27:10', 'active', 27, '2025-08-05 23:00:38', '2025-08-05 23:27:10', 'COMPUTER SOCIETY', 'General');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_vote` int(11) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `election_id`, `title`, `description`, `max_vote`, `display_order`, `created_at`, `updated_at`, `name`, `priority`) VALUES
(40, 13, 'UNZANASA presidential elctiosn', 'Vote for president', 1, 1, '2025-08-04 23:02:56', '2025-08-04 23:02:56', 'UNZANASA presidential elctiosn', 1),
(43, 16, 'SOME', 'PSDNCOSDNCOSDC', 1, 1, '2025-08-05 22:42:16', '2025-08-05 22:42:16', 'SOME', 1),
(44, 17, 'COMPUTER SOCIETY', 'VOTE FOR CS PRESIDENT', 1, 1, '2025-08-05 23:00:38', '2025-08-05 23:00:38', 'COMPUTER SOCIETY', 1);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `valid_computer_numbers`
--

CREATE TABLE `valid_computer_numbers` (
  `id` int(11) NOT NULL,
  `computer_number` varchar(10) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `voter_id` varchar(50) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(150) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0 COMMENT '0=Inactive, 1=Active',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voters`
--

INSERT INTO `voters` (`id`, `voter_id`, `firstname`, `lastname`, `email`, `password`, `photo`, `status`, `is_verified`, `created_at`, `last_login`) VALUES
(15, '2021511952', 'Student', '1952', NULL, '$2y$10$RsNdJ4W4XfkF4tmpRbzoEe3n.JnWYE.BUURH0P3D5zTJvzsvucNQG', NULL, 1, 0, '2025-08-05 20:15:13', NULL),
(16, '2021511955', 'Student', '1955', NULL, '$2y$10$q24h4AJBhbN.nKFSyaUIT.nU/OoB0bXMl6t5YNUzLuf9iR1P8rMUW', NULL, 1, 0, '2025-08-05 20:15:37', NULL),
(17, '2021511912', 'Student', '1912', NULL, '$2y$10$GhFU0YanLGbKCAawpEfNfe5ZpvOTZnM8UIOjRzL736oFrD8vk8l5W', NULL, 1, 0, '2025-08-05 20:16:00', NULL),
(18, '2021511999', 'Student', '1999', NULL, '$2y$10$96MfjrgE8QTOLF2Dtyz9ceWVdLzGBudaMRWVxFZMc3qRMSxmoNDEe', NULL, 1, 0, '2025-08-05 20:20:03', NULL),
(19, '2021511222', 'Student', '1222', NULL, '$2y$10$7Blutxik146JCO46zX2SZOVFKPaPlyL0wSb1.e3YobXmZSCMVPQz6', NULL, 1, 0, '2025-08-05 20:20:25', NULL),
(20, '1111111111', 'Student', '1111', NULL, '$2y$10$E/BCthSOUe/BzKMoYyZs..LMsTptPF21PLqIl2BDG54lO/UcJZQgS', NULL, 1, 0, '2025-08-05 20:21:19', NULL),
(21, '2022123452', 'Student', '3452', NULL, '$2y$10$pRKdNsbc6N/bq5G2pI6mNeRFjr/.l/5KzNwNEyGBQvo2iMqUMTyly', NULL, 1, 0, '2025-08-05 20:21:40', NULL),
(22, '0909000000', 'Student', '0000', NULL, '$2y$10$MbUGYo1nwgU5dLjV95M8gOFtKdczs3EX4wDMWIrCUuecJa1mQ0uYC', NULL, 1, 0, '2025-08-05 20:21:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `computer_number` varchar(20) NOT NULL DEFAULT '',
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `voter_id`, `election_id`, `position_id`, `candidate_id`, `computer_number`, `voted_at`, `ip_address`, `user_agent`) VALUES
(45, 15, 13, 40, 20, '2021511952', '2025-08-05 22:52:18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0'),
(46, 19, 13, 40, 31, '2021511222', '2025-08-05 23:28:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36'),
(47, 19, 16, 43, 33, '2021511222', '2025-08-05 23:28:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36'),
(48, 19, 17, 44, 30, '2021511222', '2025-08-05 23:28:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36'),
(49, 15, 17, 44, 35, '2021511952', '2025-08-05 23:28:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36'),
(50, 15, 16, 43, 33, '2021511952', '2025-08-05 23:29:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `voting_logs`
--

CREATE TABLE `voting_logs` (
  `id` int(11) NOT NULL,
  `computer_number` varchar(10) NOT NULL,
  `election_id` int(11) NOT NULL,
  `action` enum('vote_cast','invalid_attempt','duplicate_attempt') NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_elections_status` (`status`),
  ADD KEY `idx_elections_dates` (`start_date`,`end_date`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `valid_computer_numbers`
--
ALTER TABLE `valid_computer_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `computer_number` (`computer_number`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voter_id` (`voter_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote_per_election` (`election_id`,`computer_number`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `idx_votes_election` (`election_id`);

--
-- Indexes for table `voting_logs`
--
ALTER TABLE `voting_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valid_computer_numbers`
--
ALTER TABLE `valid_computer_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `voting_logs`
--
ALTER TABLE `voting_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `elections`
--
ALTER TABLE `elections`
  ADD CONSTRAINT `elections_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `valid_computer_numbers`
--
ALTER TABLE `valid_computer_numbers`
  ADD CONSTRAINT `valid_computer_numbers_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`voter_id`) REFERENCES `voters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `voting_logs`
--
ALTER TABLE `voting_logs`
  ADD CONSTRAINT `voting_logs_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
