-- phpMyAdmin SQL Dump
-- version 4.0.10.16
-- http://www.phpmyadmin.net
--
-- 主机: localhost:3306
-- 生成日期: 2016-07-28 02:16:21
-- 服务器版本: 5.6.14-log
-- PHP 版本: 5.5.37

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `wft_test`
--

-- --------------------------------------------------------

--
-- 表的结构 `ps_op_version`
--

CREATE TABLE IF NOT EXISTS `ps_op_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `version_id` int(11) NOT NULL COMMENT '版本号',
  `site_name` varchar(50) NOT NULL COMMENT '网站名称',
  `now_cid` varchar(50) NOT NULL COMMENT '现有版本commit_id',
  `next_cid` varchar(50) NOT NULL COMMENT '下一版本commit_id',
  `diff_detail` text NOT NULL COMMENT 'diff详细内容',
  `diff_info` text NOT NULL COMMENT 'diff简明内容',
  `create_user` varchar(50) NOT NULL COMMENT '上线人',
  `detail` varchar(500) NOT NULL COMMENT '上线说明',
  `module` varchar(100) NOT NULL COMMENT '上线module',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '当前版本状态-0:未选中|1:线上版本',
  `created` datetime NOT NULL COMMENT '创建时间',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='运维-上线版本表' AUTO_INCREMENT=5 ;

