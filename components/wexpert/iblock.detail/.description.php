<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * Основные параметры:
 *
 * IBLOCK_ID / инфоблок, не обязательно для указания
 * CODE / Символьный код элемента ЛИБО
 * ID / Идентификатор элемента
 * FILTER / если нужна еще какая-то фильтрация
 * SELECT / какие еще поля могут понадобится по-умолчанию array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "NAME")
 * GET_PROPERTY / Y – вывести все свойства
 * CACHE_TIME / время кеша
 *
 * Дополнительные параметры:
 *
 * SET_404 / Y установить ли ошибку 404 в случае пустой выборки (по-умолчанию Y)
 * INCLUDE_SEO / Y установить ли сео у страницы, по-умолчанию Y
 */

$arComponentDescription = array(
	"NAME"			=> "iblock.detail pages mutator",
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