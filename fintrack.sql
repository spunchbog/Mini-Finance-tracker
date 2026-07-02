-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 07:41 AM
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
-- Database: `fintrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `budget_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `limit_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`budget_id`, `user_id`, `category_id`, `limit_amount`) VALUES
(3, 12413, 1, 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `name`) VALUES
(4, 'Bills'),
(8, 'Business'),
(3, 'Entertainment'),
(1, 'Food'),
(7, 'Freelance'),
(10, 'Investments'),
(11, 'Other Income'),
(5, 'Others'),
(6, 'Salary'),
(9, 'Savings'),
(2, 'Transport');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`transaction_id`, `user_id`, `category_id`, `amount`, `type`, `date`, `description`) VALUES
(58, 12410, 1, 20.00, 'expense', '2026-07-01', ''),
(59, 12413, 1, 20.00, 'expense', '2026-07-01', ''),
(60, 12410, 2, 10.00, 'expense', '2026-05-01', ''),
(61, 12410, 1, 15.00, 'expense', '2026-06-10', ''),
(64, 12410, 3, 35.00, 'expense', '2026-07-02', 'movie'),
(65, 12410, 5, 15.00, 'expense', '2026-07-01', ''),
(66, 12410, 8, 100.00, 'income', '2026-07-02', ''),
(67, 12410, 10, 10.00, 'income', '2026-07-02', ''),
(68, 12410, 4, 100.00, 'expense', '2026-07-01', ''),
(69, 12410, 6, 300.00, 'income', '2026-07-01', ''),
(70, 12410, 7, 50.00, 'income', '2026-07-01', '');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `verification_token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `setup_complete` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email`, `password`, `role`, `verification_token`, `is_verified`, `setup_complete`, `created_at`, `last_login`) VALUES
(12386, 'darvin@gmail.com', '$2y$10$ZKpWXT8qTNy6b9Wmb.aipO/UasVl7l9NBscXdWgsetn8IJ1x9LMxe', 'admin', NULL, 1, 1, '2026-06-29 21:06:23', NULL),
(12392, 'eehern@gmail.com', '$2y$10$6qXHzOe1/UMqxVoXq.K2he3s2VUMrlChNOp609BpYtF.69gkhwrNK', 'admin', NULL, 1, 1, '2026-06-29 21:06:23', '2026-07-01 21:01:43'),
(12396, 'rampubalan@gmail.com', '$2y$10$A8UvW/nClCh21i7/DzHxU.lkfFrFzyCoXw8kqZIwAoIjdoZ1ukUS6', 'user', NULL, 1, 1, '2026-06-29 21:06:23', '2026-06-30 20:34:24'),
(12399, 'pubalanram@gmail.com', '$2y$10$Ki0EUEQyXnbBFMWaAJZsputNmhzPDyt8V.CVPEhss0oNTM8ytS0Ji', 'user', NULL, 1, 1, '2026-06-29 21:06:23', NULL),
(12400, 'rambhaai13@gmail.com', '$2y$10$Cr7qzY602hEkMDP0NB2Xj.heE0Zm3nWi7PrHZo2yV49E.Oq.X3NNy', 'user', 'a32a3f5b0f1dc2b876e34c3a7998fa3aff464324ab8b09f9c755e8bd2914f901', 0, 1, '2026-06-29 21:06:23', NULL),
(12401, 'eejie@gmail.com', '$2y$10$L/VSNW6sktmv5Jt4W22SOuoZ2yrmpeqQ9wjwsWdCzOl.4DaeyCopa', 'admin', NULL, 1, 1, '2026-06-29 21:06:23', NULL),
(12404, 'arjun@gmail.com', '$2y$10$HBi9.7vKLhyrjAA1wgUme.voNXrn4lSg.NNLWwGxKagEKbqbvJkMW', 'admin', NULL, 1, 1, '2026-06-30 23:01:59', '2026-07-01 20:52:53'),
(12410, 'taneehern070823@gmail.com', '$2y$10$pCQXKFNeAGy3myklQuW5me2CDrNlLFtNPRTvUjnqFuv5Vs0SD1N8W', 'user', NULL, 1, 1, '2026-07-02 12:18:32', '2026-07-02 12:49:41'),
(12413, 'keithtan070823@gmail.com', '$2y$10$PUAaFLqu4IBZsdW4n.WeIujEdTCQ4ityTj7zHFV8S466hCV0OUmkm', 'user', NULL, 1, 1, '2026-07-02 12:37:20', '2026-07-02 12:37:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12414;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
