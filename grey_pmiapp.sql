-- PHP Version: 5.3.10-1ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `grey_pmiapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE IF NOT EXISTS `data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_type_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `valid` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `data`
--

INSERT INTO `data` (`id`, `data_type_id`, `created`, `modified`, `valid`, `key`, `value`) VALUES
(1, 1, '2013-07-12 20:48:22', '2013-07-17 13:31:41', 1, 'phase', '0.306414889497529'),
(4, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'fullMoon', 'Mon Jul 22 14:16:32'),
(5, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'lastQuarter', 'Mon Jul 29 13:44:43'),
(6, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'distance', '369679.405713522'),
(7, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'firstQuarterPrev', 'Mon Jul 15 23:19:57'),
(8, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'iconNumber', '3'),
(9, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'age', '9.04861206717917'),
(10, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'sunAngle', '0.524579233561934'),
(11, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'newMoonPrev', 'Mon Jul  8 03:16:06'),
(12, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'lastQuarterPrev', 'Sun Jun 30 00:55:21'),
(13, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'fullMoonPrev', 'Sun Jun 23 07:33:38'),
(14, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'sunDistance', '152036420.82142'),
(15, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'angle', '0.538732087917104'),
(16, 1, '2013-07-13 18:14:07', '2013-07-17 13:31:41', 1, 'illum', '0.673544433354469'),
(17, 1, '2013-07-13 18:14:08', '2013-07-17 13:31:42', 1, 'firstQuarter', 'Wed Aug 14 06:57:33'),
(18, 1, '2013-07-13 18:14:08', '2013-07-17 13:31:42', 1, 'newMoon', 'Tue Aug  6 17:51:32'),
(19, 1, '2013-07-13 20:40:35', '2013-07-17 13:31:41', 1, 'phaseOrder', 'fullMoonPrev,lastQuarterPrev,newMoonPrev,firstQuarterPrev,fullMoon,lastQuarter,newMoon,firstQuarter');

-- --------------------------------------------------------

--
-- Table structure for table `data_types`
--

CREATE TABLE IF NOT EXISTS `data_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `data_types`
--

INSERT INTO `data_types` (`id`, `name`, `created`) VALUES
(1, 'Moon', '2013-07-12 20:47:26');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
