SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
/* ЕСЛИ ВДРУГ НАДО МОЧКАНУТЬ ТАБЛИЦУ - раскоментируй */
-- DROP TABLE travelmenu_country;
-- DROP TABLE travelmenu_city;
-- --------------------------------------------------------

--
-- Структура таблицы `travelmenu_transitions`
--

CREATE TABLE IF NOT EXISTS `travelmenu_transitions` (
  `EN` varchar(255) NOT NULL,
  `RU` varchar(255),
  `IT` varchar(2)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
