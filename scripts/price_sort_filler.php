<?
/**
 *
 * Товары на сайте в разных валютах и не представляется возможности их сортировать верно одним списком.
 * Это старая проблема системы битрикса http://dev.1c-bitrix.ru/community/forums/forum6/topic38885/ ,
 * http://idea.1c-bitrix.ru/5542/
 *
 * Поэтому приходится хранить кеш в виде рублевой цены в каждом товаре, и уже по нему сортировать.
 * Кеш храниться в свойстве товаров `PROPERTY_price_sort` => `asc,nulls`
 *
 * Считаю это 50% доработкой (50% гарантии), Т.к. это ошибка частично наша.
 *
 */

set_time_limit(0);
ini_set('mbstring.func_overload', "2");
ini_set('mbstring.internal_encoding', "UTF-8");

// установить здесь значение после переноса!
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/www';
$_SERVER['SERVER_NAME'] = 'http://www.exller.ru';

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

set_time_limit(0);

CModule::IncludeModule('iblock');
CModule::IncludeModule('currency');

$arFilter = array('IBLOCK_ID' => array(10, 11, 6, 7, 8, 12, 14, 13, 9), 'ACTIVE' => 'Y');
// тип цены, которую пишем в свойство
$priceId = 3;
$arSelect = array('ID', 'NAME', 'IBLOCK_ID', 'CATALOG_GROUP_' . $priceId);

$oTimer = new Timer();
$oTimer->setStart();

$r = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);
while ($ar = $r->Fetch()) {
	$priceRub = CCurrencyRates::ConvertCurrency($ar['CATALOG_PRICE_' . $priceId], $ar['CATALOG_CURRENCY_' . $priceId], 'RUB');

	$VALUE = ceil( $priceRub );
	if (intval($VALUE) == 0) {
		$VALUE = false;
	}

	CIBlockElement::SetPropertyValuesEx($ar['ID'], $ar['IBLOCK_ID'], array(
		'price_sort'	=> $VALUE
	));
}

$oTimer->setStop();

// раскомментировать для отладки
//echo ' ROWS: ' . $r->SelectedRowsCount() . ', ELAPSED: ' . round($oTimer->elapsed(), 5);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>