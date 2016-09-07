<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/* @var $this CBitrixComponent */

$arParams["CNT"]		= intval($arParams["CNT"]) > 0 ? $arParams["CNT"] : 40;
$arParams["curPage"]	= $APPLICATION->GetCurPage();
$arParams["pageTitle"]	= $APPLICATION->GetTitle();

if ($this->startResultCache(false)) {
	CModule::IncludeModule('search');

	$obSearch = new CSearch();
	$obSearch->SetOptions(array(
		"ERROR_ON_EMPTY_STEM" => false,
		"NO_WORD_LOGIC" => true
	));

	$arFilter = array('SITE_ID' => 's1', 'QUERY' => $arParams["pageTitle"], 'TAGS' => '');
	$aSort = array('CUSTOM_RANK' => 'DESC', 'TITLE_RANK' => 'DESC', 'RANK' => 'DESC', 'DATE_CHANGE' => 'DESC');
	$exFILTER = array(
		array( '=MODULE_ID' => 'main', 'URL' => array ( 0 => '/%', ), ),
		array( '=MODULE_ID' => 'iblock', 'PARAM1' => 'info', 'PARAM2' => array ( 0 => 2, 1 => 3))
	);

	$obSearch->Search($arFilter, $aSort, $exFILTER);
	if ($obSearch->errorno == 0) {
		$arResult["SEARCH"] = array();

		$i = 0;
		while ($ar = $obSearch->GetNext()) {
			$ar['URL'] = str_replace(array('index.php'), array(''), $ar['URL']);

			if ($i >= $arParams["CNT"]) {
				break;
			}

			if ($ar['URL'] != $arParams['curPage']) {
				$arResult["SEARCH"][] = array(
					'TITLE' => $ar['TITLE'],
					'URL'	=> $ar['URL']
				);
				$i++;
			}
		}
	}

	if (count($arResult["SEARCH"]) > 0) {
		$this->setResultCacheKeys(array());
		$this->includeComponentTemplate();
	} else {
		$this->abortResultCache();
	}
}
?>