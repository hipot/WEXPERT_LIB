<?
CModule::IncludeModule("iblock");

$IBLOCK_ID		= 3;
$SECTION_CODE	= 'industry';
$CACHE_TIME		= (COption::GetOptionString("main", "component_cache_on", "Y") == "N") ? 0 : 3600;
$cahcePath		= 'php/sub_left_menu/';

$aMenuLinksDyn 	= array();
$CACHE_ID = __FILE__ . $IBLOCK_ID . $SECTION_CODE;

$obMenuCache = new CPHPCache;

if ($obMenuCache->StartDataCache($CACHE_TIME, $CACHE_ID, $cahcePath)) {
	
	$arOrder = array(
		'sort'			=> 'asc'
	);
	$arFilter = array(
		'IBLOCK_ID'		=> $IBLOCK_ID,
		'ACTIVE'		=> 'Y',
		'SECTION_CODE'	=> $SECTION_CODE
	);
	$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, false, array('ID', 'NAME', 'DETAIL_PAGE_URL'));
	while ($arItem = $rsItems->GetNext()) {
		$aMenuLinksDyn[] = array(
			$arItem['NAME'],
			$arItem['DETAIL_PAGE_URL'],
			array(),
			array()
		);
	}
	
	if (empty($aMenuLinksDyn)) {
		$obMenuCache->AbortDataCache();
	} else {
		$obMenuCache->EndDataCache(array("aMenuLinksDyn" => $aMenuLinksDyn));
	}
		
} else {
	$arVars = $obMenuCache->GetVars();
	$aMenuLinksDyn = $arVars["aMenuLinksDyn"];
}

$aMenuLinks = array_merge($aMenuLinksDyn, $aMenuLinks);
?>