-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 22, 2026 at 06:27 PM
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
-- Database: `CivicComplaintsDB`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(20) NOT NULL,
  `role` enum('citizen','officer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pfp` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `name`, `role`, `created_at`, `pfp`) VALUES
(8, '', '$2y$10$bkSQW8yYwy/x8oDBaZJlzO0aK.Maxz/ufGG56ZKNfDLPHh1uFjLfy', '', 'citizen', '2026-08-22 12:16:44', '/dashboard/CIvicComplaints/Civic-Complaints/photos/Pain.jpg'),
(9, 'papapapap', '$2y$10$1ddbmzk95pUNM8SvORVa0eOyOfLJYer96xaPFDPI1ZI4k1DgrUHFy', 'papapa', 'citizen', '2026-08-22 12:17:15', '/dashboard/CIvicComplaints/Civic-Complaints/photos/default.png'),
(10, 'praaa', '$2y$10$Lt8KshZLHAFMXwbE/hhJueiveU.jw179r7QmMn/28KJsFHpKpRg1W', 'praaa', 'citizen', '2026-08-22 12:30:34', '/dashboard/CIvicComplaints/Civic-Complaints/photos/default.jpg'),
(11, 'ttt', '$2y$10$PVJEdOSawXUpFLET58as9O/fpU/rPp85DGdmLgI6WttEDWb9vaozS', 'ttt', 'citizen', '2026-08-22 12:30:56', '/dashboard/CIvicComplaints/Civic-Complaints/photos/Gargantua.jpg'),
(13, 'prathamp', '$2y$10$JISjfxozxgfnXrwcZz3ZTuAoxD5lQ2fuvwR5oJysOH1LZ6Il1NEwe', 'pratham', 'officer', '2026-08-22 12:33:57', '/dashboard/CivicComplaints/Civic-Complaints/photos/Earth.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
