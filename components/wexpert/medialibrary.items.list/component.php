<?
/**
 * Вывод файлов из альбома(мов) медиабиблиотеки
 *
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/* @var $this CBitrixComponent */

if (! is_array($arParams['COLLECTION_IDS'])) {
	$arParams['COLLECTION_IDS'] = array($arParams['COLLECTION_IDS']);
}
$arParams['COLLECTION_IDS'] = array_filter($arParams['COLLECTION_IDS']);

if ($arParams['ONLY_RETURN_ITEMS'] == 'Y') {
	$arParams['CACHE_TIME']	= 0;
}

if ($this->StartResultCache(false)) {
	CModule::IncludeModule("fileman");
	CMedialib::Init();

	$Params = array();
	if (count($arParams['COLLECTION_IDS']) > 0) {
		$Params = array('arCollections' => $arParams['COLLECTION_IDS']);
	}

	$rsCol = CMedialibItem::GetList($Params);
	foreach ($rsCol as $v) {
		// future iterator
		$arResult['ITEMS'][] = $v;
	}

	if (count($arResult['ITEMS']) > 0) {
		$this->SetResultCacheKeys(array());

		if ($arParams['ONLY_RETURN_ITEMS'] != 'Y') {
			$this->IncludeComponentTemplate();
		}
	} else {
		$this->AbortResultCache();
	}
}

return $arResult;
?>