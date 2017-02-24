-- phpMyAdmin SQL Dump
-- version 4.0.10.16
-- http://www.phpmyadmin.net
--
-- Host: 172.16.2.10:3306
-- Generation Time: Jul 25, 2016 at 12:14 PM
-- Server version: 5.1.73-log
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wft_up`
--

-- --------------------------------------------------------

--
-- Table structure for table `ps_wechat_out_chain`
--

CREATE TABLE IF NOT EXISTS `ps_wechat_out_chain` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '微信服务号外链id',
  `type_info` varchar(64) NOT NULL COMMENT '外链对应情况说明',
  `content` varchar(255) NOT NULL COMMENT '外链内容',
  `out_chain` varchar(255) NOT NULL COMMENT '外链地址',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `ps_wechat_out_chain`
--

INSERT INTO `ps_wechat_out_chain` (`type_id`, `type_info`, `content`, `out_chain`) VALUES
(2, '下单成功', '11', '3'),
(3, '退款中', '', ''),
(4, '退款中', '', ''),
(5, '已使用', '', ''),
(6, '', '', ''),
(7, '', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
