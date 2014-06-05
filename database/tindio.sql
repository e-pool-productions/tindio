-- phpMyAdmin SQL Dump
-- version 4.0.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 17, 2014 at 01:57 PM
-- Server version: 5.6.11-log
-- PHP Version: 5.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tindio`
--
CREATE DATABASE IF NOT EXISTS `tindio` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `tindio`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`) VALUES
('Admin');

-- --------------------------------------------------------

--
-- Table structure for table `asset`
--

CREATE TABLE IF NOT EXISTS `asset` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `global` tinyint(1) NOT NULL DEFAULT '0',
  `uploaddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`asset_id`),
  KEY `type_id` (`type_id`),
  KEY `author` (`author`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `assettype`
--

CREATE TABLE IF NOT EXISTS `assettype` (
  `assettype_id` int(11) NOT NULL,
  `type_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`assettype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `assettype`
--

INSERT INTO `assettype` (`assettype_id`, `type_name`) VALUES
(0, 'Video'),
(1, 'Audio'),
(2, '3D Model'),
(3, 'Image'),
(4, 'Link'),
(5, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `title`) VALUES
(0, '3D animation'),
(1, '2D animation'),
(2, 'Animation'),
(3, 'VFX'),
(4, 'Movie');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `task_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `globallog`
--

CREATE TABLE IF NOT EXISTS `globallog` (
  `globallog_id` int(11) NOT NULL AUTO_INCREMENT,
  `logtype_id` int(11) NOT NULL,
  `link` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT 'project_id / username',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`globallog_id`),
  KEY `logtype_id` (`logtype_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=72 ;

-- --------------------------------------------------------

--
-- Table structure for table `logtype`
--

CREATE TABLE IF NOT EXISTS `logtype` (
  `logtype_id` int(11) NOT NULL,
  `event` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`logtype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `logtype`
--

INSERT INTO `logtype` (`logtype_id`, `event`) VALUES
(0, 'new_project'),
(1, 'new_user'),
(2, 'delete_project'),
(3, 'delete_user'),
(4, 'finish_project');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `shortcode` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `logo` int(11) DEFAULT NULL,
  `creationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `project_name` (`title`),
  KEY `shortcode` (`shortcode`),
  KEY `status_id` (`status_id`),
  KEY `category_id` (`category_id`),
  KEY `logo` (`logo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `projectasset`
--

CREATE TABLE IF NOT EXISTS `projectasset` (
  `project_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  PRIMARY KEY (`project_id`,`asset_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projectobserver`
--

CREATE TABLE IF NOT EXISTS `projectobserver` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `project_id` int(11) NOT NULL,
  KEY `username` (`username`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projecttype`
--

CREATE TABLE IF NOT EXISTS `projecttype` (
  `projecttype_id` int(11) NOT NULL AUTO_INCREMENT,
  `genre` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`projecttype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scene`
--

CREATE TABLE IF NOT EXISTS `scene` (
  `scene_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `project_id` int(11) NOT NULL,
  `logo` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `startdate` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `orderposition` int(11) NOT NULL,
  `creationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scene_id`),
  KEY `status_id` (`status_id`),
  KEY `project_id` (`project_id`),
  KEY `logo` (`logo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- Table structure for table `sceneasset`
--

CREATE TABLE IF NOT EXISTS `sceneasset` (
  `scene_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`scene_id`,`asset_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shot`
--

CREATE TABLE IF NOT EXISTS `shot` (
  `shot_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `logo` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `deadline` date DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `project_id` int(11) NOT NULL,
  `scene_id` int(11) NOT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `orderposition` int(11) NOT NULL,
  `creationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`shot_id`),
  KEY `status_id` (`status_id`),
  KEY `scene_id` (`scene_id`),
  KEY `project_id` (`project_id`),
  KEY `logo` (`logo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `shotasset`
--

CREATE TABLE IF NOT EXISTS `shotasset` (
  `shot_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  PRIMARY KEY (`shot_id`,`asset_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE IF NOT EXISTS `skill` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `skill`
--

INSERT INTO `skill` (`skill_id`, `title`) VALUES
(1, 'Rigger'),
(3, 'Medeler');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `status_id` int(11) NOT NULL,
  `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `title`) VALUES
(0, 'unassigned'),
(1, 'pre_production'),
(2, 'in_progress'),
(3, 'for_approval'),
(4, 'finished');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status_id` int(11) DEFAULT '0',
  `project_id` int(11) NOT NULL,
  `shot_id` int(11) NOT NULL,
  `orderposition` int(11) NOT NULL,
  `creationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `status_id` (`status_id`),
  KEY `shot_id` (`shot_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `taskasset`
--

CREATE TABLE IF NOT EXISTS `taskasset` (
  `task_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `local` tinyint(1) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`,`asset_id`),
  KEY `task_id` (`task_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `lastname` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastaccess` datetime DEFAULT NULL,
  `gravatar_email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `newassignments` int(11) NOT NULL,
  `creationtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `timezone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC',
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`lastname`, `firstname`, `username`, `password`, `mail`, `lastaccess`, `gravatar_email`, `newassignments`, `creationtime`, `timezone`) VALUES
('Doe', 'John', 'Admin', '200ceb26807d6bf99fd6f4f0d1ca54d4', 'trash@tindio.com', '2014-03-26 13:12:39', 'trash@tindio.com', 0, '2014-03-03 13:43:26', 'UTC');

-- --------------------------------------------------------

--
-- Table structure for table `userproject`
--

CREATE TABLE IF NOT EXISTS `userproject` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`project_id`),
  KEY `username` (`username`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userscene`
--

CREATE TABLE IF NOT EXISTS `userscene` (
  `scene_id` int(11) NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`scene_id`,`username`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usershot`
--

CREATE TABLE IF NOT EXISTS `usershot` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `shot_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`shot_id`),
  KEY `shot_id` (`shot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userskill`
--

CREATE TABLE IF NOT EXISTS `userskill` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `skill_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`skill_id`),
  KEY `skill_id` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usertask`
--

CREATE TABLE IF NOT EXISTS `usertask` (
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `task_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`task_id`),
  KEY `user` (`username`),
  KEY `task` (`task_id`),
  KEY `username` (`username`),
  KEY `task_2` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflow`
--

CREATE TABLE IF NOT EXISTS `workflow` (
  `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`workflow_id`),
  UNIQUE KEY `title` (`title`),
  KEY `created_by` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=110 ;

--
-- Dumping data for table `workflow`
--

INSERT INTO `workflow` (`workflow_id`, `title`, `username`) VALUES
(102, 'VFX chroma key shot', 'Admin'),
(103, 'VFX Tracking shot', 'Admin'),
(104, '2D animation shot', 'Admin'),
(105, '3D animation shot', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `workflowstructure`
--

CREATE TABLE IF NOT EXISTS `workflowstructure` (
  `workflow_id` int(11) NOT NULL,
  `orderposition` int(11) NOT NULL,
  `task_title` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`orderposition`,`workflow_id`),
  KEY `workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `workflowstructure`
--

INSERT INTO `workflowstructure` (`workflow_id`, `orderposition`, `task_title`, `description`) VALUES
(102, 0, 'Storyboard', ''),
(103, 0, 'Storyboard', ''),
(104, 0, 'Storyboard', ''),
(105, 0, 'Storyboard', ''),
(102, 1, 'Concept Art', ''),
(103, 1, 'Concept Art', ''),
(104, 1, 'Audio recor', ''),
(105, 1, 'Audio recor', ''),
(102, 2, 'Import foot', ''),
(103, 2, 'Import foot', ''),
(104, 2, 'Animatic', ''),
(105, 2, 'Concept Art', ''),
(102, 3, 'Matte', ''),
(103, 3, 'Tracking', ''),
(104, 3, 'Concept Art', ''),
(105, 3, 'Modeling', ''),
(102, 4, 'Keying', ''),
(103, 4, 'Modeling', ''),
(104, 4, 'Background', ''),
(105, 4, 'Rigging', ''),
(102, 5, 'Compositing', ''),
(103, 5, 'Rigging', ''),
(104, 5, 'Key poses', ''),
(105, 5, 'Shading', ''),
(102, 6, 'Rendering', ''),
(103, 6, 'Shading', ''),
(104, 6, 'Extremes', ''),
(105, 6, 'Layout', ''),
(103, 7, 'Animation', ''),
(104, 7, 'Inbetweens', ''),
(105, 7, 'Animation', ''),
(103, 8, 'VFX', ''),
(104, 8, 'Polish', ''),
(105, 8, 'VFX', ''),
(103, 9, 'Lighting', ''),
(104, 9, 'Ink & paint', ''),
(105, 9, 'Lighting', ''),
(103, 10, 'Compositing', ''),
(104, 10, 'Compositing', ''),
(105, 10, 'Compositing', ''),
(103, 11, 'Rendering', ''),
(104, 11, 'Export', ''),
(105, 11, 'Rendering', '');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE;

--
-- Constraints for table `asset`
--
ALTER TABLE `asset`
  ADD CONSTRAINT `asset_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `assettype` (`assettype_id`);

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `globallog`
--
ALTER TABLE `globallog`
  ADD CONSTRAINT `globallog_ibfk_1` FOREIGN KEY (`logtype_id`) REFERENCES `logtype` (`logtype_id`);

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `project_ibfk_3` FOREIGN KEY (`logo`) REFERENCES `asset` (`asset_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `projectasset`
--
ALTER TABLE `projectasset`
  ADD CONSTRAINT `projectasset_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `projectasset_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`asset_id`);

--
-- Constraints for table `projectobserver`
--
ALTER TABLE `projectobserver`
  ADD CONSTRAINT `projectobserver_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `projectobserver_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Constraints for table `scene`
--
ALTER TABLE `scene`
  ADD CONSTRAINT `scene_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `scene_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `scene_ibfk_4` FOREIGN KEY (`logo`) REFERENCES `asset` (`asset_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sceneasset`
--
ALTER TABLE `sceneasset`
  ADD CONSTRAINT `sceneasset_ibfk_1` FOREIGN KEY (`scene_id`) REFERENCES `scene` (`scene_id`),
  ADD CONSTRAINT `sceneasset_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`asset_id`) ON UPDATE CASCADE;

--
-- Constraints for table `shot`
--
ALTER TABLE `shot`
  ADD CONSTRAINT `shot_ibfk_2` FOREIGN KEY (`scene_id`) REFERENCES `scene` (`scene_id`),
  ADD CONSTRAINT `shot_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `shot_ibfk_4` FOREIGN KEY (`logo`) REFERENCES `asset` (`asset_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `shot_ibfk_5` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`);

--
-- Constraints for table `shotasset`
--
ALTER TABLE `shotasset`
  ADD CONSTRAINT `shotasset_ibfk_1` FOREIGN KEY (`shot_id`) REFERENCES `shot` (`shot_id`),
  ADD CONSTRAINT `shotasset_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`asset_id`);

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `task_ibfk_2` FOREIGN KEY (`shot_id`) REFERENCES `shot` (`shot_id`),
  ADD CONSTRAINT `task_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Constraints for table `taskasset`
--
ALTER TABLE `taskasset`
  ADD CONSTRAINT `taskasset_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`),
  ADD CONSTRAINT `taskasset_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`asset_id`);

--
-- Constraints for table `userproject`
--
ALTER TABLE `userproject`
  ADD CONSTRAINT `userproject_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `userproject_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Constraints for table `userscene`
--
ALTER TABLE `userscene`
  ADD CONSTRAINT `userscene_ibfk_2` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `userscene_ibfk_3` FOREIGN KEY (`scene_id`) REFERENCES `scene` (`scene_id`);

--
-- Constraints for table `usershot`
--
ALTER TABLE `usershot`
  ADD CONSTRAINT `usershot_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usershot_ibfk_2` FOREIGN KEY (`shot_id`) REFERENCES `shot` (`shot_id`);

--
-- Constraints for table `userskill`
--
ALTER TABLE `userskill`
  ADD CONSTRAINT `userskill_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`),
  ADD CONSTRAINT `userskill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`skill_id`),
  ADD CONSTRAINT `userskill_ibfk_3` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `userskill_ibfk_4` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`skill_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usertask`
--
ALTER TABLE `usertask`
  ADD CONSTRAINT `usertask_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON UPDATE CASCADE,
  ADD CONSTRAINT `usertask_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`);

--
-- Constraints for table `workflowstructure`
--
ALTER TABLE `workflowstructure`
  ADD CONSTRAINT `workflowstructure_ibfk_1` FOREIGN KEY (`workflow_id`) REFERENCES `workflow` (`workflow_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
