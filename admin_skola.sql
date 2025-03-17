-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Värd: localhost:3306
-- Tid vid skapande: 17 mars 2025 kl 09:19
-- Serverversion: 10.6.20-MariaDB
-- PHP-version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `admin_skola`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `printer` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `user` varchar(255) NOT NULL,
  `hours` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tabellstruktur `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `printer` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumpning av Data i tabell `maintenance`
--

INSERT INTO `maintenance` (`id`, `printer`, `start_date`, `end_date`) VALUES
(5, 'Prusa Mk4S', '2025-03-17', '2025-03-23');

-- --------------------------------------------------------

--
-- Tabellstruktur `printers`
--

CREATE TABLE `printers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tabellstruktur `print_jobs`
--

CREATE TABLE `print_jobs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `progress` int(11) NOT NULL,
  `user` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumpning av Data i tabell `print_jobs`
--

INSERT INTO `print_jobs` (`id`, `name`, `progress`, `user`) VALUES
(2, 'Namnskyltar - Skuggare o Prao ', 10, NULL);

-- --------------------------------------------------------

--
-- Tabellstruktur `support_requests`
--

CREATE TABLE `support_requests` (
  `id` int(11) NOT NULL,
  `user` varchar(255) NOT NULL,
  `printer` varchar(255) NOT NULL,
  `issue` text NOT NULL,
  `response` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumpning av Data i tabell `support_requests`
--

INSERT INTO `support_requests` (`id`, `user`, `printer`, `issue`, `response`, `status`) VALUES
(3, 'Siam.karlsson', 'Prusa Mk4S', 'Hej! Detta Ã¤r ett test', 'Test', 'In Progress');

-- --------------------------------------------------------

--
-- Tabellstruktur `unavailable_dates`
--

CREATE TABLE `unavailable_dates` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumpning av Data i tabell `unavailable_dates`
--

INSERT INTO `unavailable_dates` (`id`, `date`) VALUES
(1, '2025-03-15'),
(4, '2025-03-16');

-- --------------------------------------------------------

--
-- Tabellstruktur `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumpning av Data i tabell `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `token`, `verified`) VALUES
(1, 'admin', '$2y$10$0R1BwNJqnVJiSZcyff.H1uR7FZByNJjh6zlFxM7lG8befFAVtw8Lm', 'admin', 'siam.karlsson@gmail.com', '9f8d8206dd15372ef4f393f28e636cbd', 1),
(2, 'user', '$2y$10$0R1BwNJqnVJiSZcyff.H1uR7FZByNJjh6zlFxM7lG8befFAVtw8Lm', 'user', 'siam.karlsson@elev.ga.ntig.se', '7eff3ea5f7e2e4e8f7b3355aff3a94be', 1);

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `print_jobs`
--
ALTER TABLE `print_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `support_requests`
--
ALTER TABLE `support_requests`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Index för tabell `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT för tabell `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT för tabell `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `print_jobs`
--
ALTER TABLE `print_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT för tabell `support_requests`
--
ALTER TABLE `support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT för tabell `unavailable_dates`
--
ALTER TABLE `unavailable_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT för tabell `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
