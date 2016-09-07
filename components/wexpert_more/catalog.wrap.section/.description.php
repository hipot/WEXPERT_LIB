<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Основные параметры:
 *
 * IBLOCK_ID / конечно же указать инфоблок
 * SECTION_ID / Номер Секция(если нужно). Если нет секции, включается параметр BY_LINK
 * SECTION_CODE / Код секции, может использоваться вместо номера (При ЧПУ)
 * SECTION_USER_FIELDS / Пользовательские поля секции
 * ORDER / если нужна иная сортировка, по-умолчанию array("SORT"=>"ASC", "ID" => "DESC")
 * PAGESIZE / сколько элементов на странице, при постраничной навигации
 * SELECT_PROPS / Свойства товаров для выборки
 * OFFERS_SELECT_FIELDS / какие еще поля ТОРГОВЫХ ПРЕДЛОЖЕНИЙ могут понадобится. По-умолчанию array("ID","NAME","DETAIL_PAGE_URL")
 * OFFERS_SELECT_PROPS / свойства ТОРГОВЫХ ПРЕДЛОЖЕНИЙ
 * PRICE_CODE / Коды цен в виде массива array('BASE','RETAIL')
 * FILTER / если нужна еще какая-то фильтрация
 * CACHE_TIME / время кеша
 * REWRITE_PARAMS / Прочие настройки компонента catalog.section, если понадобятся
 *
 * Дополнительные условия:
 *
 * у компонента ВСЕГДА подключается шаблон .default
 * Шаблон, который указывается при вызове компонента wexpert - передается в качестве шаблона в компонент catalog.section
 */

$arComponentDescription = array(
	"NAME" => "catalog.section wrapper",
	"DESCRIPTION" => "",
	"ICON" => "/images/ico.gif",
	"PATH" => array(
		"ID" => "wexpert_root",
		"NAME" => "weXpert"
	),
	"AREA_BUTTONS" => array(),
	"CACHE_PATH" => "Y",
	"COMPLEX" => "N"
);
?>
