<?
/**
 *
 * Использование:
 *
 * 1/
 * Генерируем карту сайта модулем СЕО, указываем карту сайта как sitemap_old.xml
 *
 * 2/
 * Кидаем данный скрипт в корень сайта и запускаем его
 *
 * 3/
 * Получаем в файле sitemap.xml склеенную карту сайта
 *
 * @version 1.0
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * Делаем один файл с картой сайта из имеющихся битриксовых
 */
function MakeCombineSiteMap()
{
	$sitemap_old = "sitemap_old.xml";
	$sitemap_new = "sitemap.xml";

	$finalDoc = new DOMDocument('1.0', 'UTF-8');
	$urlset = $finalDoc->appendChild($finalDoc->createElement('urlset'));
	$urlset->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");

	$doc = new DOMDocument();
	$doc->load($_SERVER["DOCUMENT_ROOT"] . '/' . $sitemap_old);
	foreach ($doc->getElementsByTagName("loc") as $arItem) {
		if (!empty($arItem->nodeValue)) {
			$parentDoc = new DOMDocument();
			$parentDoc->load($arItem->nodeValue);

			foreach ($parentDoc->getElementsByTagName("urlset") as $arItem) {
				foreach ($arItem->childNodes as $arNode) {

					// delete lastmod tag
					/*$lastmod = $arNode->getElementsByTagName('lastmod')->item(0);
					$arNode->removeChild( $lastmod );*/

					$urlset->appendChild( $finalDoc->importNode($arNode, true) );
				}
			}
		}
	}

	$finalDoc->save($_SERVER["DOCUMENT_ROOT"] . '/' . $sitemap_new);
}


MakeCombineSiteMap();
echo 'DONE!';


