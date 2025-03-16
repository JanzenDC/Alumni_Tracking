-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2025 at 11:36 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alumni_rtu`
--

-- --------------------------------------------------------

--
-- Table structure for table `college_departments`
--

CREATE TABLE `college_departments` (
  `dept_id` int(10) UNSIGNED NOT NULL,
  `dept_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `nx_batches`
--

CREATE TABLE `nx_batches` (
  `batchID` int(11) NOT NULL,
  `batch_name` varchar(100) NOT NULL,
  `batch_date` int(11) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `profile` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_batches`
--

INSERT INTO `nx_batches` (`batchID`, `batch_name`, `batch_date`, `cover_photo`, `profile`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Batch A', 20231001, 'cover1.jpg', 'profile1.jpg', 'Description for Batch A.', '2024-10-10 14:55:43', '2024-10-10 14:55:43'),
(2, 'Batch B', 20231002, 'cover2.jpg', 'profile2.jpg', 'Description for Batch B.', '2024-10-10 14:55:43', '2024-10-10 14:55:43'),
(3, 'Batch C', 20231003, 'cover3.jpg', 'profile3.jpg', 'Description for Batch C.', '2024-10-10 14:55:43', '2024-10-10 14:55:43'),
(4, 'Batch D', 20231004, 'cover4.jpg', 'profile4.jpg', 'Description for Batch D.', '2024-10-10 14:55:43', '2024-10-10 14:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `nx_employees`
--

CREATE TABLE `nx_employees` (
  `employeeID` int(11) NOT NULL,
  `pID` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_employees`
--

INSERT INTO `nx_employees` (`employeeID`, `pID`, `position`, `department`, `hire_date`, `status`) VALUES
(3, 1, 'tst', '', '0000-00-00', 1),
(4, 7, '', '', '0000-00-00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nx_events`
--

CREATE TABLE `nx_events` (
  `eventID` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_events`
--

INSERT INTO `nx_events` (`eventID`, `event_name`, `description`, `event_date`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'Alumni Batch Get together', 'batch sana di drawing', '2024-10-12 22:26:00', 1, '2024-10-12 14:26:49', '2024-10-12 14:26:49'),
(3, 'Alumni Batch Get together', 'testt', '2024-10-22 22:28:00', 1, '2024-10-12 14:28:55', '2024-10-12 14:28:55');

-- --------------------------------------------------------

--
-- Table structure for table `nx_friends`
--

CREATE TABLE `nx_friends` (
  `friendshipID` int(11) NOT NULL,
  `userID1` int(11) NOT NULL,
  `userID2` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_friends`
--

INSERT INTO `nx_friends` (`friendshipID`, `userID1`, `userID2`, `created_at`, `status`) VALUES
(1, 1, 2, '2024-10-09 23:57:09', 1),
(2, 1, 3, '2024-10-09 23:57:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nx_job_interests`
--

CREATE TABLE `nx_job_interests` (
  `interestID` int(11) NOT NULL,
  `pID` int(11) NOT NULL,
  `jobID` int(11) NOT NULL,
  `expressed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `nx_job_postings`
--

CREATE TABLE `nx_job_postings` (
  `jobID` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `posted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_job_postings`
--

INSERT INTO `nx_job_postings` (`jobID`, `title`, `description`, `posted_by`, `created_at`, `updated_at`) VALUES
(2, 'Data Analyst', 'Join our team as a data analyst to help us make data-driven decisions.', 2, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(3, 'Project Manager', 'Seeking an experienced project manager to oversee our development projects.', 3, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(4, 'UI/UX Designer', 'We need a creative UI/UX designer to enhance our user experience.', 1, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(5, 'DevOps Engineer', 'Looking for a DevOps engineer to streamline our development and operations.', 2, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(6, 'Marketing Specialist', 'Seeking a marketing specialist to boost our online presence.', 3, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(7, 'Product Manager', 'Join us as a product manager and lead our product strategy.', 1, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(8, 'Content Writer', 'Looking for a content writer to create engaging articles and blog posts.', 2, '2024-10-11 03:18:45', '2024-10-11 03:18:45'),
(9, 'System Analyst', 'test', 1, '2024-10-12 15:08:24', '2024-10-12 15:08:24');

-- --------------------------------------------------------

--
-- Table structure for table `nx_logs`
--

CREATE TABLE `nx_logs` (
  `logID` int(11) NOT NULL,
  `pID` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_logs`
--

INSERT INTO `nx_logs` (`logID`, `pID`, `action`, `target_type`, `target_id`, `timestamp`, `remark`) VALUES
(1, 1, 'User logged in', 'user', 1, '2024-10-09 23:39:39', 'User logged in successfully.'),
(2, 1, 'Updated profile picture', 'user', 1, '2024-10-09 23:39:39', 'Profile picture updated.'),
(3, 1, 'Created a new event', 'event', 1, '2024-10-09 23:39:39', 'Event created successfully.'),
(4, 1, 'Posted a new job', 'job', 1, '2024-10-09 23:39:39', 'Job posting created.'),
(5, 1, 'Logged out', 'user', 1, '2024-10-09 23:39:39', 'User logged out successfully.');

-- --------------------------------------------------------

--
-- Table structure for table `nx_users`
--

CREATE TABLE `nx_users` (
  `pID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `graduation_date` varchar(255) NOT NULL,
  `remark` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `college_department` varchar(255) DEFAULT NULL,
  `college_graduate` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_users`
--

INSERT INTO `nx_users` (`pID`, `username`, `email`, `password_hash`, `fname`, `mname`, `lname`, `date_of_birth`, `profile_picture`, `bio`, `phone_number`, `address`, `city`, `state`, `zip_code`, `country`, `graduation_date`, `remark`, `created_at`, `updated_at`, `college_department`, `college_graduate`) VALUES
(2, 'user1', 'user1@example.com', 'hashed_password_1', 'First', NULL, 'User', '1990-01-01', '', 'Bio for user 1', '1234567890', 'Address 1', 'City1', 'State1', '12345', 'Country1', '', 1, '2024-10-09 23:56:39', '2024-10-09 23:57:45', NULL, ''),
(3, 'user2', 'user2@example.com', 'hashed_password_2', 'Second', NULL, 'User', '1992-02-02', '', 'Bio for user 2', '1234567891', 'Address 2', 'City2', 'State2', '12346', 'Country2', '', 1, '2024-10-09 23:56:39', '2024-10-09 23:57:53', NULL, ''),
(4, 'user3', 'user3@example.com', 'hashed_password_3', 'Third', NULL, 'User', '1994-03-03', '', 'Bio for user 3', '1234567892', 'Address 3', 'City3', 'State3', '12347', 'Country3', '', 1, '2024-10-09 23:56:39', '2024-10-09 23:57:58', NULL, ''),
(5, 'user4', 'user4@example.com', 'hashed_password_4', 'Fourth', NULL, 'User', '1996-04-04', '', 'Bio for user 4', '1234567893', 'Address 4', 'City4', 'State4', '12348', 'Country4', '', 1, '2024-10-09 23:56:39', '2024-10-09 23:58:00', NULL, ''),
(6, 'user5', 'user5@example.com', 'hashed_password_5', 'Fifth', NULL, 'User', '1998-05-05', '', 'Bio for user 5', '1234567894', 'Address 5', 'City5', 'State5', '12349', 'Country5', '', 1, '2024-10-09 23:56:39', '2024-10-09 23:58:01', NULL, ''),
(8, 'Admin', 'Admin@gmail.com', '$2y$10$yR8qHLm0yCTrYQDvFpCLEOKfEs88O9f.rM6EV/SFYvMq/4epx9BJG', 'Admin', 'Admin', 'Admin', '2025-03-06', NULL, '', '09123456789', '', '', '', '', '', '', 1, '2025-03-06 07:27:42', '2025-03-16 10:32:28', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `nx_user_batches`
--

CREATE TABLE `nx_user_batches` (
  `user_batchID` int(11) NOT NULL,
  `pID` int(11) NOT NULL,
  `batchID` int(11) NOT NULL,
  `is_active` int(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_user_batches`
--

INSERT INTO `nx_user_batches` (`user_batchID`, `pID`, `batchID`, `is_active`, `joined_at`) VALUES
(11, 1, 3, 1, '2025-03-05 14:10:31'),
(13, 8, 3, 1, '2025-03-11 12:11:30');

-- --------------------------------------------------------

--
-- Table structure for table `nx_user_type`
--

CREATE TABLE `nx_user_type` (
  `tID` int(11) NOT NULL,
  `pID` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remark` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `nx_user_type`
--

INSERT INTO `nx_user_type` (`tID`, `pID`, `type`, `date`, `remark`) VALUES
(1, 1, '3', '2024-10-10 00:23:11', 1),
(6, 1, '2', '2025-03-05 13:18:48', 1),
(7, 1, '2', '2025-03-05 13:18:52', 1),
(8, 1, '2', '2025-03-05 14:01:36', 1),
(10, 7, '1', '2025-03-06 05:52:49', 1),
(11, 8, '3', '2025-03-06 07:27:42', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `college_departments`
--
ALTER TABLE `college_departments`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `nx_batches`
--
ALTER TABLE `nx_batches`
  ADD PRIMARY KEY (`batchID`);

--
-- Indexes for table `nx_employees`
--
ALTER TABLE `nx_employees`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `nx_events`
--
ALTER TABLE `nx_events`
  ADD PRIMARY KEY (`eventID`);

--
-- Indexes for table `nx_friends`
--
ALTER TABLE `nx_friends`
  ADD PRIMARY KEY (`friendshipID`),
  ADD UNIQUE KEY `unique_friendship` (`userID1`,`userID2`);

--
-- Indexes for table `nx_job_interests`
--
ALTER TABLE `nx_job_interests`
  ADD PRIMARY KEY (`interestID`),
  ADD UNIQUE KEY `pID` (`pID`,`jobID`);

--
-- Indexes for table `nx_job_postings`
--
ALTER TABLE `nx_job_postings`
  ADD PRIMARY KEY (`jobID`);

--
-- Indexes for table `nx_logs`
--
ALTER TABLE `nx_logs`
  ADD PRIMARY KEY (`logID`);

--
-- Indexes for table `nx_users`
--
ALTER TABLE `nx_users`
  ADD PRIMARY KEY (`pID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `nx_user_batches`
--
ALTER TABLE `nx_user_batches`
  ADD PRIMARY KEY (`user_batchID`),
  ADD UNIQUE KEY `pID` (`pID`,`batchID`);

--
-- Indexes for table `nx_user_type`
--
ALTER TABLE `nx_user_type`
  ADD PRIMARY KEY (`tID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `college_departments`
--
ALTER TABLE `college_departments`
  MODIFY `dept_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nx_batches`
--
ALTER TABLE `nx_batches`
  MODIFY `batchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nx_employees`
--
ALTER TABLE `nx_employees`
  MODIFY `employeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `nx_events`
--
ALTER TABLE `nx_events`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nx_friends`
--
ALTER TABLE `nx_friends`
  MODIFY `friendshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `nx_job_interests`
--
ALTER TABLE `nx_job_interests`
  MODIFY `interestID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nx_job_postings`
--
ALTER TABLE `nx_job_postings`
  MODIFY `jobID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `nx_logs`
--
ALTER TABLE `nx_logs`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nx_users`
--
ALTER TABLE `nx_users`
  MODIFY `pID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `nx_user_batches`
--
ALTER TABLE `nx_user_batches`
  MODIFY `user_batchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `nx_user_type`
--
ALTER TABLE `nx_user_type`
  MODIFY `tID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
