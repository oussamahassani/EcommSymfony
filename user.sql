-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 13, 2025 at 05:35 PM
-- Server version: 10.6.0-MariaDB
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autolinkdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `address_street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  KEY `IDX_8D93D649D60322AC` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `role_id`, `name`, `last_name`, `phone`, `email`, `password`, `created_at`, `image_path`, `verification_token`, `is_verified`, `reset_token`, `reset_token_expires_at`, `address_street`, `address_city`, `address_postal_code`, `address_country`) VALUES
(1, 2, 'oussama', 'hassani', 123456, 'hassani20120@gmail.com', '$2y$13$BBDbbDHJyYRJIo.0SKEvbOFYTjVfvAKnL0GEV.yfDe/dVzDN6G4Wq', '2025-05-08 08:30:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, 'admin', 'admin', 20140140, 'admin@gmail.com', '$2y$13$BBDbbDHJyYRJIo.0SKEvbOFYTjVfvAKnL0GEV.yfDe/dVzDN6G4Wq', '2025-05-08 09:59:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 2, 'user', 'user', 201100100, 'user1@gmail.com', '$2y$13$sVHKz6SX7844rQoO8XJrMuwDS8J23VZ.NB3DN4HykopZxD4y/qTJK', '2025-05-13 17:13:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
