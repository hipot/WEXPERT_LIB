<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/* @var $this CBitrixComponent */

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);


$componentName		= basename(__DIR__);
$CACHE_ID			= "php/$componentName".md5(serialize($arParams));
$CACHE_DIR			= "php/$componentName/";
$obPageCache		= new CPHPCache;

if ($obPageCache->StartDataCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_DIR)) {

	CModule::IncludeModule("iblock");

	if (empty($arResult["ITEMS"])) {
		$obPageCache->AbortDataCache();
	} else {
		$obPageCache->EndDataCache(array("arResult" => $arResult));
	}

} else {
	$arVars		= $obPageCache->GetVars();
	$arResult	= $arVars["arResult"];
}

// if need
$this->IncludeComponentTemplate();

// FUTURE
return $arResult;


?>