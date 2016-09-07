-- phpMyAdmin SQL Dump
-- version 3.4.2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 29 2011 г., 10:19
-- Версия сервера: 5.5.13
-- Версия PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: ``
--

-- --------------------------------------------------------
/* ЕСЛИ ВДРУГ НАДО МОЧКАНУТЬ ТАБЛИЦУ - раскоментируй */
-- DROP TABLE we_comments;
-- DROP TABLE we_comments_rate;
-- --------------------------------------------------------

--
-- Структура таблицы `we_comments`
--

CREATE TABLE IF NOT EXISTS `we_comments` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE` datetime DEFAULT NULL,
  `IBLOCK_ELEMENT_ID` int(18) NOT NULL,
  `PARENT_ID` int(18) DEFAULT NULL,
  `BAD` text COLLATE utf8_unicode_ci,
  `GOOD` text COLLATE utf8_unicode_ci,
  `TEXT` text COLLATE utf8_unicode_ci,
  `AUTHOR_NAME` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AUTHOR_EMAIL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `USER_ID` int(18) DEFAULT NULL,
  `STATUS` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IBLOCK_ELEMENT_ID` (`IBLOCK_ELEMENT_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Структура таблицы `we_comments_rate`
--

CREATE TABLE IF NOT EXISTS `we_comments_rate` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `DATE` datetime DEFAULT NULL,
  `COMMENT_ID` int(18) NOT NULL,
  `RATE` float DEFAULT NULL,
  `IP_ADRESS` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ID`),
  KEY `COMMENT_ID` (`COMMENT_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
