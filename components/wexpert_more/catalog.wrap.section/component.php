<?
/**
 * Компонент - обёртка над Bitrix:catalog.section
 * Может использоваться как iblock.list для отображения товаров в блоке
 *
 * @version 1.0.0
 * @copyright 2014, WebExpert
 */

if (! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('iblock');

$arDefaultParams = Array(
	//Основные параметры
	"IBLOCK_ID" => "",
	"IBLOCK_TYPE" => "",
	"SECTION_ID" => "",
	"SECTION_CODE" => "",

	//Источник данных
	"SECTION_USER_FIELDS" => array(),

	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_SORT_FIELD2" => "name",
	"ELEMENT_SORT_ORDER2" => "asc",

	"FILTER_NAME" => "arrFilter",
	"INCLUDE_SUBSECTIONS" => "Y",
	"SHOW_ALL_WO_SECTION" => "Y",
	"HIDE_NOT_AVAILABLE" => "Y",

	//Шаблоны ссылок
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"BASKET_URL" => "/personal/basket.php",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop",
	"SECTION_ID_VARIABLE" => "SECTION_ID",

	//Дополнительные настройки
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"BROWSER_TITLE" => "-",
	"ADD_SECTIONS_CHAIN" => "Y",
	"DISPLAY_COMPARE" => "N",
	"SET_TITLE" => "Y",
	"SET_STATUS_404" => "N",
	"CACHE_FILTER" => "Y",

	//Внешний вид
	"PAGE_ELEMENT_COUNT" => "30",
	"LINE_ELEMENT_COUNT" => "3",

	"PROPERTY_CODE" => array(),

	"OFFERS_FIELD_CODE" => array("ID","NAME","DETAIL_PAGE_URL"),
	"OFFERS_PROPERTY_CODE" => array(),
	"OFFERS_LIMIT" => "5",

	"OFFERS_SORT_FIELD" => "sort",
	"OFFERS_SORT_ORDER" => "asc",
	"OFFERS_SORT_FIELD2" => "active_from",
	"OFFERS_SORT_ORDER2" => "desc",


	"PRICE_CODE" => array(),
	"USE_PRICE_COUNT" => "Y",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",

	"PRODUCT_PROPERTIES" => array(),
	"USE_PRODUCT_QUANTITY" => "Y",

	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",

	"CACHE_GROUPS" => "Y",

	"DISPLAY_TOP_PAGER" => "Y",
	"DISPLAY_BOTTOM_PAGER" => "Y",

	"PAGER_TITLE" => "Товары",
	"PAGER_SHOW_ALWAYS" => "Y",
	"PAGER_TEMPLATE" => "",
	"PAGER_DESC_NUMBERING" => "Y",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
	"PAGER_SHOW_ALL" => "Y",


	"OFFERS_CART_PROPERTIES" => array(),
	"QUANTITY_FLOAT" => "N",

	//Управление режимом AJAX
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",

	"CONVERT_CURRENCY" => "Y",
	"CURRENCY_ID" => "RUB",
);

$arCatalogParams = $arDefaultParams;


/**
 * Указываем инфоблок
 */
$obCache = new CPHPCache;
if ($obCache->StartDataCache($arParams["CACHE_TIME"], $arParams["IBLOCK_ID"], '/')) {

	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arIblock = CIBlock::GetList(array("SORT"=>"ASC"), array("ID" => $arParams["IBLOCK_ID"]))->Fetch();
	if (is_array($arIblock)) {
		$arCatalogParams["IBLOCK_ID"] = $arIblock["ID"];
		$arCatalogParams["IBLOCK_TYPE"] = $arIblock["TYPE"];

		$obCache->EndDataCache(array(
			"IBLOCK_ID"	=> $arCatalogParams["IBLOCK_ID"],
			"IBLOCK_TYPE" => $arCatalogParams["IBLOCK_TYPE"]
		));
	} else {
		$obCache->AbortDataCache();
		@include_once($_SERVER["DOCUMENT_ROOT"] . "/404_inc.php");
	}

} else {

	$arVars = $obCache->GetVars();
	$arCatalogParams["IBLOCK_ID"] = $arVars["IBLOCK_ID"];
	$arCatalogParams["IBLOCK_TYPE"] = $arVars["IBLOCK_TYPE"];

}


/**
 * Указываем секцию
 * Если нет секции - включаем BY_LINK
 */
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
if ($arParams["SECTION_ID"] <= 0 && strlen($arParams["SECTION_CODE"]) == 0) {
	$arCatalogParams["BY_LINK"] = "Y";
} else {
	$arCatalogParams["SECTION_ID"] = $arParams["SECTION_ID"];
	$arCatalogParams["SECTION_CODE"] = $arParams["SECTION_CODE"];
}

/**
 * Сортировка
 */
$i = 1;
foreach ($arParams["ORDER"] as $by => $order) {
	$arCatalogParams["ELEMENT_SORT_FIELD" . ($i > 1 ? $i : "")] = $by;
	$arCatalogParams["ELEMENT_SORT_ORDER" . ($i > 1 ? $i : "")] = $order;

	$i++;
}



/**
 * Шаблоны ссылок
 */
if (!empty($arParams["URL_REWRITE"])) {
	foreach ($arParams["URL_REWRITE"] as $param => $value) {
		if (isset($arCatalogParams[$param]) && !empty($value)) {
			$arCatalogParams[$param] = $value;
		}
	}
}

/**
 * Постраничка
 */
$arParams["PAGESIZE"] = intval($arParams["PAGESIZE"]);
if ($arParams["PAGESIZE"] > 0) {
	$arCatalogParams["PAGE_ELEMENT_COUNT"] = $arParams["PAGESIZE"];
}

/**
 * Установка полей выборки
 */
if (is_array($arParams["SECTION_USER_FIELDS"])) {
	$arCatalogParams["SECTION_USER_FIELDS"] += $arParams["SECTION_USER_FIELDS"];
}
if (is_array($arParams["SELECT_PROPS"])) {
	$arCatalogParams["PROPERTY_CODE"] += $arParams["SELECT_PROPS"];
}
if (is_array($arParams["OFFERS_FIELD_CODE"])) {
	$arCatalogParams["OFFERS_FIELD_CODE"] += $arParams["OFFERS_SELECT_FIELDS"];
}
if (is_array($arParams["OFFERS_PROPERTY_CODE"])) {
	$arCatalogParams["OFFERS_PROPERTY_CODE"] += $arParams["OFFERS_SELECT_PROPS"];
}

/**
 * Цены
 */
if (is_array($arParams["PRICE_CODE"])) {
	$arCatalogParams["PRICE_CODE"] = $arParams["PRICE_CODE"];
}

/**
 * Кеш
 */
if (isset($arParams["CACHE_TIME"])) {
	$arCatalogParams["CACHE_TIME"] = $arParams["CACHE_TIME"];
}
if (isset($arParams["CACHE_TYPE"])) {
	$arCatalogParams["CACHE_TYPE"] = $arParams["CACHE_TYPE"];
}

/**
 * Если понадобится - изменение стандартныйх параметров компонента
 */
if (!empty($arParams["REWRITE_PARAMS"])) {
	$arCatalogParams = array_merge($arCatalogParams, $arParams["REWRITE_PARAMS"]);
}

/**
 * Добавляем дополнительные значения фильтру
 */
if (is_array($arParams["FILTER"])) {
	global ${$arCatalogParams["FILTER_NAME"]};
	$arrFilter = &${$arCatalogParams["FILTER_NAME"]};

	if(!is_array($arrFilter))
		$arrFilter = array();

	$arrFilter = array_merge($arrFilter, $arParams["FILTER"]);
}


$arResult["PARAMS"] = $arCatalogParams;

$arResult["TEMPLATE"] = $this->GetTemplateName();
$this->setTemplateName('');

$this->IncludeComponentTemplate();






