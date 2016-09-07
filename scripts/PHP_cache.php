<?
$obCache	= new CPHPCache();

$CACHE_ID	= "detail".$IBLOCK_ID.$ID;
$CACHE_TIME	= (COption::GetOptionString("main", "component_cache_on", "Y") == "N") ? 0 : $CACHE_TIME;

if ($obCache->StartDataCache($CACHE_TIME, $CACHE_ID, "php/detail.dopdan/")) {

	// выборка в $arRes

	$obCache->EndDataCache(array("arRes"=>$arRes));
} else {
	$arVars = $obCache->GetVars();
	$arRes = $arVars["arRes"];
}

?>