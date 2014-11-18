-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2014 at 03:01 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lsbc`
--

-- --------------------------------------------------------

--
-- Table structure for table `faapplicant`
--

CREATE TABLE IF NOT EXISTS `faapplicant` (
  `faID` int(255) NOT NULL AUTO_INCREMENT,
  `isValid` tinyint(1) NOT NULL,
  PRIMARY KEY (`faID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100 ;

--
-- Dumping data for table `faapplicant`
--



-- --------------------------------------------------------

--
-- Table structure for table `faapplication`
--

CREATE TABLE IF NOT EXISTS `faapplication` (
  `faApplicationID` int(11) NOT NULL AUTO_INCREMENT,
  `faID` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` text,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `dateApplied` date NOT NULL,
  `imgLoc` varchar(30) DEFAULT NULL,
  `isApproved` tinyint(1) NOT NULL,
  `faVetter` varchar(30) NOT NULL,
  `totalAmtApproved` double NOT NULL,
  `totalAmtDisbursed` double NOT NULL,
  PRIMARY KEY (`faApplicationID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=103 ;

--
-- Dumping data for table `faapplication`
--



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
  `homeNum` varchar(11) NOT NULL,
  `handphoneNum` varchar(11) NOT NULL,
  `email` text,
  `description` text,
  `imgLoc` text,
  PRIMARY KEY (`faID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=102 ;

--
-- Dumping data for table `fadetails`
--



-- --------------------------------------------------------

--
-- Table structure for table `fadisbursement`
--

CREATE TABLE IF NOT EXISTS `fadisbursement` (
  `faDisbursementID` int(11) NOT NULL AUTO_INCREMENT,
  `faApplicationID` int(11) NOT NULL,
  `dateDisbursed` date NOT NULL,
  `type` varchar(30) NOT NULL,
  `amount` double NOT NULL,
  `paymentSchdNo` int(11) NOT NULL,
  `issueIncharge` text NOT NULL,
  `issueApprover` text NOT NULL,
  `description` text,
  `imgLoc` text,
  PRIMARY KEY (`faDisbursementID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1006 ;

--
-- Dumping data for table `fadisbursement`
--



-- --------------------------------------------------------

--
-- Table structure for table `favalid`
--

CREATE TABLE IF NOT EXISTS `favalid` (
  `faID` int(255) NOT NULL,
  `faApplicationID` int(11) NOT NULL,
  PRIMARY KEY (`faID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `favalid`
--


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
(1, 'zhouao', '40bd001563085fc35165329ea1ff5c5ecbdbbeef');

-- --------------------------------------------------------

--
-- Table structure for table `systemuserdetails`
--

CREATE TABLE IF NOT EXISTS `systemuserdetails` (
  `userID` int(255) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
