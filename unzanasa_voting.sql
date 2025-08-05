-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost: 8082
-- Generation Time: Aug 05, 2025 at 03:43 PM
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
(24, 'debugtest_1754354780', '$2y$10$HIAyq48iXpjykdT3tVufG.NpEpFlu74xvlMoeHnzr.Fjmw1j10JEe', 'debug1754354780@test.com', 'Debug Test User', '2025-08-05 00:46:20', NULL),
(25, 'test_1754354874', '$2y$10$H0yIuDMbl7WINvVLnvTkROwEyY7m22TjzXEjSUpYRKrZJJJ0384C6', 'test1754354874@example.com', 'Test User', '2025-08-05 00:47:54', NULL),
(26, 'apolllos', '$2y$10$PAftjBkdTPlWBoQCD4fVBOgQPnH9XkN4jpLZ4h7m9D7toi2T/kXW6', 'apollosmwaba@gmail.com', 'apollospatel', '2025-08-05 13:23:46', NULL),
(27, 'admin', '$2y$10$MmSDi1gBV1yH37NfsxcuYOCT9XSiKu/Zy.KSZ5N0c5cNQ3kggE3vy', 'admin@email', 'admin', '2025-08-05 13:25:54', '2025-08-05 13:33:52');

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
(20, 40, 'Apollos', 'Mwaba', 'uploads/candidates/candidate_6891fd8c112c0.jpg', 'ASHBDIBIASDBO', '2025-08-05 12:48:12', '2025-08-05 13:08:46', 13, 'UNZA aPOLLOS', 'ASKJDAS', 1);

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
(13, '', 'Vote for the president', '2025-08-05 01:00:00', '2025-08-28 00:00:00', 'active', NULL, '2025-08-04 23:02:56', '2025-08-04 23:02:56', 'UNZANASA', 'General'),
(14, '', 'Vote for vice president', '2025-08-05 01:00:00', '2025-08-21 00:00:00', 'active', NULL, '2025-08-04 23:03:58', '2025-08-04 23:03:58', 'UNZANASA vice', 'General');

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
(41, 14, 'UNZANASA vice', 'vote for vice president', 1, 1, '2025-08-04 23:03:58', '2025-08-04 23:03:58', 'UNZANASA vice', 1);

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
(2, '2021511952', 'Student', '1952', NULL, '$2y$10$/PXDu4e/DfFzcBjgI75W.egrD7cI0g4Fx.PK/pzjp4wOzw7A414c.', NULL, 1, 0, '2025-08-04 23:44:08', NULL),
(3, '2021511953', 'Student', '1953', NULL, '$2y$10$1ba0vrv7/gcpWVK/G3xxXO5lmYlE4RCB6eJImS/PI2rbG7SNWucPa', NULL, 1, 0, '2025-08-04 23:44:39', NULL),
(11, '2021511912', 'Student', '1912', NULL, '$2y$10$yszz6.B9L1.lEsAOThZoSu/ZG1GzOE/HCQNW.560iHpDL021s6XAG', NULL, 1, 0, '2025-08-04 23:56:13', NULL),
(12, '2021511222', 'Student', '1222', NULL, '$2y$10$nwgFFyOLj6iZuDhEAvxyKu63fb5QeUM.rGmJxCHD2xBm0vKsUZ8hC', NULL, 1, 0, '2025-08-05 00:29:53', NULL),
(13, '2022511952', 'Student', '1952', NULL, '$2y$10$cdy81OHwZNwMg/pN42MlRePDygNq1SfVn9ifadkGzXFM3izp5SMzC', NULL, 1, 0, '2025-08-05 00:30:40', NULL),
(14, '2021511955', 'Student', '1955', NULL, '$2y$10$nN66z1bF/A6f90l6OUotSuirpUndVHz9RxVnw66LHLMwjDMHkhKL6', NULL, 1, 0, '2025-08-05 00:31:50', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valid_computer_numbers`
--
ALTER TABLE `valid_computer_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
