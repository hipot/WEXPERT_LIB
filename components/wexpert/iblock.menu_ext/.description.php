<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Компонент с php-кешем для построения ext-меню со списком инфоблоков или секций
 *
 * TYPE - elements|sections - тип выборки, элементы или секции (ОБЯЗАТЕЛЬНЫЙ)
 * CACHE_TAG - пусть сохранения кеша, будет сохранено /bitrix/cache/php/CACHE_TAG/ (ОБЯЗАТЕЛЬНЫЙ)
 * CACHE_TIME - время кеша (ОБЯЗАТЕЛЬНЫЙ)
 *
 * Параметры выборки
 *
 * IBLOCK_ID / конечно же указать инфоблок
 * ORDER / если нужна иная сортировка, по-умолчанию array("SORT" => "ASC")
 * FILTER / если нужна еще какая-то дополнительная фильтрация
 *
 * @version 1.0
 * @copyright weXpert, 2014
 */

$arComponentDescription = array(
	"NAME"			=> basename(__DIR__),
	"DESCRIPTION"	=> "",
	"ICON"			=> "/images/ico.gif",
	"PATH" => array(
		"ID"		=> "wexpert_root",
		"NAME"		=> "weXpert"
	),
	"AREA_BUTTONS"	=> array(),
	"CACHE_PATH"	=> "Y",
	"COMPLEX"		=> "N"
);

?>