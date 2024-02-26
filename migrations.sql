-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Feb 24, 2024 alle 16:38
-- Versione del server: 5.7.31
-- Versione PHP: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agenzia`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `dati`
--

DROP TABLE IF EXISTS `dati`;
CREATE TABLE IF NOT EXISTS `dati` (
  `journey` int(15) NOT NULL AUTO_INCREMENT,
  `countries` varchar(20) NOT NULL,
  `seats` tinyint(1) NOT NULL,
  PRIMARY KEY (`journey`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `dati`
--

INSERT INTO `dati` (`journey`, `countries`, `seats`) VALUES
(1, '3@', 5),
(2, '3@2@5@', 2),
(3, '7@8@2@', 6),
(4, '7@', 1),
(6, '1@2@15@', 4),
(13, '1@3@15@', 4),
(11, '1@2@', 5),
(12, '1@2@', 9);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
