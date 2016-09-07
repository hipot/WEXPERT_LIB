<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 *
 * Компонент "Список элементов медиабиблиотеки"
 * medialibrary.items.list
 *
 * @version 1.0, 12.09.2012
 * @author weXpert
 *
 */


/**
 * Параметры компонента (пока 3 штуки):
 *
 * COLLECTION_IDS			- числовые идентификаторы коллекций (либо один числовой идентификатор)
 * CACHE_TIME				- время кеша
 * ONLY_RETURN_ITEMS 		- Y/N, по-умолчанию N.
 * 		параметр, используется компонентом medialibrary.collection.list
 * 		если установлено в Y, то кеш отключается, а компонент не подключая шаблон возвращает
 * 		свой массив элементов (т.е. компонент возвращает свой $arResult)
 *
 */

$arComponentDescription = array(
	"NAME"			=> "medialibrary.items.list",
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