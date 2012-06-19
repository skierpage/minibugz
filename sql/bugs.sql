-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 19, 2012 at 01:35 AM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `minibugz`
--

-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE IF NOT EXISTS `bugs` (
  `bug_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varbinary(255) NOT NULL,
  `description` varbinary(1000) NOT NULL,
  `status_id` tinyint(3) NOT NULL COMMENT 'see status_code',
  `status_last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'update to now() on status changes',
  PRIMARY KEY (`bug_id`),
  UNIQUE KEY `title` (`title`),
  KEY `status_id` (`status_id`),
  KEY `status_last_modified` (`status_last_modified`)
) ENGINE=InnoDB DEFAULT CHARSET=binary AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
