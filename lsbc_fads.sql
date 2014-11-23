-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2014 at 01:25 AM
-- Server version: 5.6.17-log
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lsbc_fads`
--

-- --------------------------------------------------------

--
-- Table structure for table `faapplication`
--

CREATE TABLE IF NOT EXISTS `faapplication` (
  `faApplicationID` int(255) NOT NULL AUTO_INCREMENT,
  `faID` int(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` text,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `dateApplied` date NOT NULL,
  `imgLoc` text,
  `isApproved` tinyint(1) NOT NULL,
  `faVetter` varchar(30) NOT NULL,
  `totalAmtApproved` double NOT NULL,
  `totalAmtDisbursed` double NOT NULL,
  PRIMARY KEY (`faApplicationID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=110 ;

-- --------------------------------------------------------

--
-- Table structure for table `fadetails`
--

CREATE TABLE IF NOT EXISTS `fadetails` (
  `faID` int(255) NOT NULL AUTO_INCREMENT,
  `nric` varchar(9) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `dob` date NOT NULL,
  `address1` text NOT NULL,
  `address2` text,
  `poCode` varchar(6) NOT NULL,
  `homeNum` varchar(8) DEFAULT NULL,
  `handphoneNum` varchar(8) DEFAULT NULL,
  `email` text,
  `description` text,
  `imgLoc` text,
  PRIMARY KEY (`faID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- Table structure for table `fadisbursement`
--

CREATE TABLE IF NOT EXISTS `fadisbursement` (
  `faDisbursementID` int(255) NOT NULL AUTO_INCREMENT,
  `faApplicationID` int(255) NOT NULL,
  `dateDisbursed` date NOT NULL,
  `type` varchar(30) NOT NULL,
  `amount` double NOT NULL,
  `paymentSchdNo` int(11) NOT NULL,
  `issueIncharge` varchar(30) NOT NULL,
  `issueApprover` varchar(30) NOT NULL,
  `description` text,
  `imgLoc` text,
  PRIMARY KEY (`faDisbursementID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1006 ;

-- --------------------------------------------------------

--
-- Table structure for table `systemuser`
--

CREATE TABLE IF NOT EXISTS `systemuser` (
  `userID` int(255) NOT NULL AUTO_INCREMENT,
  `userName` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `systemuser`
--

INSERT INTO `systemuser` (`userID`, `userName`, `password`) VALUES
(4, 'lsbcuser', '5eec41b1bfd3153201b596f6dde168249109a2eb'),
(5, 'lsbcsadmin', '8d1e07e178f65aec40be935675d54874d72e5f32'),
(6, 'lsbcadmin', '6e5d0ff87a8f017146273d141365dc2021bb8683');

-- --------------------------------------------------------

--
-- Table structure for table `systemuserdetails`
--

CREATE TABLE IF NOT EXISTS `systemuserdetails` (
  `userID` int(255) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `systemuserdetails`
--

INSERT INTO `systemuserdetails` (`userID`, `firstName`, `lastName`) VALUES
(4, 'LSBC', 'User'),
(5, 'LSBC', 'Super Admin'),
(6, 'LSBC', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `systemuserperms`
--

CREATE TABLE IF NOT EXISTS `systemuserperms` (
  `userID` int(255) NOT NULL,
  `isSuperuser` tinyint(1) NOT NULL,
  `canViewUser` tinyint(1) NOT NULL,
  `canCreateUser` tinyint(1) NOT NULL,
  `canEditUser` tinyint(1) NOT NULL,
  `canDeleteUser` tinyint(1) NOT NULL,
  `canViewFA` tinyint(1) NOT NULL,
  `canCreateFA` tinyint(1) NOT NULL,
  `canEditFA` tinyint(1) NOT NULL,
  `canDeleteFA` tinyint(1) NOT NULL,
  `canSearchFA` tinyint(1) NOT NULL,
  `canGenerateReport` tinyint(1) NOT NULL,
  `canIssueDisbursement` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `systemuserperms`
--

INSERT INTO `systemuserperms` (`userID`, `isSuperuser`, `canViewUser`, `canCreateUser`, `canEditUser`, `canDeleteUser`, `canViewFA`, `canCreateFA`, `canEditFA`, `canDeleteFA`, `canSearchFA`, `canGenerateReport`, `canIssueDisbursement`) VALUES
(4, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 1, 1),
(5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(6, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
