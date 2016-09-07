<?
/**
 * Скрипт проверяет ya_makrket.xml переходя по ссылкам и сверяя цену в файле с ценой
 * на сайте.
 *
 * Запускать из консоли!
 * php -f ya_market_check.php > result_check.txt
 * затем не-совпавшие будут в файле result_check.txt отмечены как RED
 *
 * @uses phpQuery
 */
require __DIR__ . '/phpQuery-onefile.php';

// PARAMS:
$xmlOffers		= 'http://..................../bitrix/catalog_export/yandex_export.php';
// запрос по поиску цены в карточке товара
$getPriceQuery	= '.info-text .price_new:first';

// RUN:
$doc = phpQuery::newDocumentXML( file_get_contents($xmlOffers) );

$arOffers = array();
$tags = array('url', 'price', 'currencyId');

$offfers = pq('offer');
foreach ($offfers as $k => $off) {
	foreach ($tags as $tag) {
		$arOffers[ $k ][ $tag ] =   pq($tag, $off)->text();
	}
	/*if ($k > 10) {
		break;
	}*/
}


foreach ($arOffers as &$off) {
	$doc = phpQuery::newDocument( file_get_contents($off['url']) );
	$off['URL_PRICE'] = trim(pq()->text());
	$off['URL_PRICE'] = floatval( str_replace(' ', '', $off['URL_PRICE']) );

	if ($off['URL_PRICE'] != $off['price']) {
		$off['RED'] = 'Y';
	}

}
unset($off);

var_export($arOffers);

?>