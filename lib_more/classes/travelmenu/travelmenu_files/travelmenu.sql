SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
/* ЕСЛИ ВДРУГ НАДО МОЧКАНУТЬ ТАБЛИЦУ - раскоментируй */
-- DROP TABLE travelmenu_country;
-- DROP TABLE travelmenu_city;
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `travelmenu_country` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(255) NOT NULL,
  `NAME` varchar(255),
  PRIMARY KEY (`ID`),
  KEY `CODE` (`CODE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Структура таблицы `travelmenu_city`
--

CREATE TABLE IF NOT EXISTS `travelmenu_city` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `CODE` varchar(255) NOT NULL,
  `NAME` varchar(255),
  `COUNTRY_CODE` varchar(4),
  PRIMARY KEY (`ID`),
  KEY `CODE` (`CODE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
