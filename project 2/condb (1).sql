-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 06:05 AM
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
-- Database: `condb`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `subject`, `message`, `attachment`, `status`, `created_at`, `updated_at`) VALUES
(2, 100, 'อยากกินข้าวมันไก่', 'มีแต่ก๋วยเตี๋ยว', NULL, 'approved', '2025-05-20 09:11:54', '2025-05-21 03:15:55'),
(3, 104, 'าาา', '5555', NULL, 'pending', '2025-05-20 10:23:53', '2025-05-21 03:16:13'),
(4, 105, 'ข้าวอร่อยมั้ย', 'ข้าวอร่อยน่ะ แต่ก๋วยเตี๋ยวอร่อยกว่า', NULL, 'rejected', '2025-05-21 02:16:39', '2025-05-21 03:15:53'),
(5, 105, 'รูปสวยมั้ย', 'รูปสวยดี', 'file_682d3f992fa70.png', 'approved', '2025-05-21 02:51:05', '2025-05-21 03:15:50'),
(6, 107, '55555', '77', NULL, 'pending', '2025-05-21 03:20:35', '2025-05-23 03:33:27'),
(7, 106, '.', '......', NULL, 'approved', '2025-05-23 03:47:38', '2025-05-23 03:47:52'),
(8, 109, 'อยากกินข้าวมันไก่', '123', NULL, 'approved', '2025-05-26 08:51:00', '2025-05-26 08:51:13'),
(9, 106, 'bim tools', 'bim tool ออกช้าจังเลย', NULL, 'approved', '2025-05-26 08:57:23', '2025-05-27 02:52:16'),
(10, 106, '123', '456', 'file_6835225251d1c.png', 'approved', '2025-05-27 02:24:18', '2025-05-27 02:52:04'),
(11, 106, 'รูปสวยมั้ย', '22220', NULL, 'rejected', '2025-05-27 03:50:13', '2025-05-27 04:05:25'),
(12, 106, 'รูปสวยมั้ย', '12345', NULL, 'rejected', '2025-05-27 04:05:32', '2025-05-27 04:05:36'),
(13, 110, 'เจอยุค 2ไหม', 'อยากต่อยยุค 0 1 ว่ะ ที่ชื่อเข้มฮยอกซอก', NULL, 'pending', '2025-05-27 04:08:24', '2025-05-28 01:59:06'),
(14, 111, '.', '......', NULL, 'rejected', '2025-05-28 02:00:07', '2025-05-28 02:00:36'),
(15, 113, '.', '.', 'file_6837e625c5c73.png', 'pending', '2025-05-29 04:44:21', '2025-05-29 04:44:21'),
(16, 113, '.', '.', 'file_68380364ea794.png', 'pending', '2025-05-29 06:49:08', '2025-05-29 06:49:08'),
(17, 113, '.', '.', NULL, 'pending', '2025-05-29 06:56:28', '2025-05-29 06:56:28'),
(18, 113, '.', '.', 'file_6838052f2de61.png', 'approved', '2025-05-29 06:56:47', '2025-05-29 07:24:01'),
(19, 113, '.', '.', 'file_68380559a7f40.png', 'rejected', '2025-05-29 06:57:29', '2025-05-29 07:23:56'),
(20, 106, '.', '.', 'file_6838122055e2b.docx', 'pending', '2025-05-29 07:52:00', '2025-05-29 07:52:00'),
(21, 113, '.', '.', 'file_6839386c3ef17.jpg', 'pending', '2025-05-30 04:47:40', '2025-05-30 04:47:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`) VALUES
(100, 'sakda', '1c395a8dce135849bd73c6dba3b54809', 'tungnum99@gmail.com', 'user'),
(101, 'ttxng', 'ef775988943825d2871e1cfa75473ec0', 'mbp200564@gmail.com', 'admin'),
(102, 'acc123', 'c81e728d9d4c2f636f067f89cc14862c', '879745napwweea@ezmail.shop', 'user'),
(103, 'new', 'eccbc87e4b5ce2fe28308fd9f2a7baf3', 'new@gmail.com', 'user'),
(104, 'new1', 'f638f4354ff089323d1a5f78fd8f63ca', '879745napww22eea@ezmail.shop', 'user'),
(105, 'kem', 'e10adc3949ba59abbe56e057f20f883e', 'kemkem@gmail.com', 'user'),
(106, 'kim', '$2y$10$o3bl4GUiabdgUiOjIwiNc.eUu6AVzLvp3U6lfafPiw3vIrhAV2qby', 'kimkim@gmail.com', 'admin'),
(107, 'lol', '$2y$10$wYKAqGpeX4LbLALa0kOjn.L0dR2D8TTvDVtnsi9JU8GJ8LmcjAl/i', '55555@gmail.com', 'user'),
(108, 'km', '$2y$10$waS8w.Ko0uyc4CCuOIKAS.OpWzeV.ZHNu97G3eBO6WcFSw667.suO', 'km@km.com', 'user'),
(109, 'kimm', '$2y$10$112S2Y6pLcUFY81kHZgcfu9L9pyZd3abkigbpmMGguEYnfDLTlFTq', 'kkim@gmail.com', 'user'),
(110, 'คิมกรยง', '$2y$10$ZA/wqgNDqMBWxGUqZ3z3cOwReVLeuct9hpaaqPjILgC54SSV/XC16', 'kimrayong@gamil.com', 'user'),
(111, 'po', '$2y$10$9gGJCy0x/1K3BSXj0BX4FeBnSCee8fme6mBo8cS4yK76icZ/5a.By', 'po@gmail.com', 'user'),
(112, 'kim123', '$2y$10$NrJbbBMCP.9itNJmGkmLW.PYzz9EgBxgyX5km1YBxY5kzOF959s9S', 'tung8@gmail.com', 'user'),
(113, 'kuomi', '$2y$10$jLdagywSqIeMEbVkk1UA0eeXnJLGLVKVowdaRWhjWgLOJuMKcoIHi', 'kuomi@gmail.com', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
