<?
$arResult['ALL_CNT'] = sizeof($arResult['HOTELS']);

$from = $arParams['PER_PAGE']*($_REQUEST['PAGEN_1']-1);

$arResult['HOTELS'] = array_slice($arResult['HOTELS'], $from, $arParams['PER_PAGE']);

$arResult['PER_PAGE'] = sizeof($arResult['HOTELS']);

ob_start();
	$arResult["nPageWindow"] = 15;
	$arResult["NavShowAlways"] = true;
	$arResult["bDescPageNumbering"] = false;
	$arResult["nStartPage"] = 1;
	$arResult["NavPageSize"] = $arParams['PER_PAGE'];
	$arResult["NavRecordCount"] = $arResult['ALL_CNT'];
	$arResult["nEndPage"] = ceil($arResult["NavRecordCount"]/$arResult["NavPageSize"]);
	$arResult["NavPageNomer"] = $_REQUEST['PAGEN_1'];
	$arResult["sUrlPath"] = GetPagePath(false, false);
	$arResult["NavQueryString"]= htmlspecialchars(DeleteParam(array(
		"PAGEN_1",
		"PHPSESSID",
		"clear_cache",
	)));
	require($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/components/bitrix/system.pagenavigation/.default/template.php');
	$arResult['PAGER'] = ob_get_contents();
ob_end_clean();
?>
