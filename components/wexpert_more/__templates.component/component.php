<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/* @var $this CBitrixComponent */

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);

if ($this->StartResultCache(false)) {

	CModule::IncludeModule("iblock");

	if (empty($arResult["ITEMS"])) {
		$this->AbortResultCache();
	} else {
		$this->SetResultCacheKeys(array());
		$this->IncludeComponentTemplate();
	}
}

// FUTURE
return $arResult;


?>