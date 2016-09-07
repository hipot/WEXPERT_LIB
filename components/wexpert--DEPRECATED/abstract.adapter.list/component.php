<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/* @var $this CBitrixComponent */
/* @var $oAdapter AbstractBaseAdapter */

$arParams["CLASS_TYPE"]				= trim($arParams["CLASS_TYPE"]);
$arParams["FILTER_SIMPLE"]			= (count($arParams["FILTER_SIMPLE"]) > 0) ? $arParams["FILTER_SIMPLE"] : array();
$arParams["ORDER"]					= (count($arParams["ORDER"]) > 0) ? $arParams["ORDER"] : array();
$arParams["ADD_WHERE_SQL_NOTSAFE"]	= trim($arParams["ADD_WHERE_SQL_NOTSAFE"]);

$classType							= $arParams["CLASS_TYPE"];

if (! class_exists($classType)) {
	ShowError('Not exists class ' . $classType. '. Sorry (');
	return false;
}
if (! is_subclass_of($classType, 'AbstractBaseAdapter')) {
	ShowError('Not correct type ' . $classType. '. Sorry (');
	return false;
}

if ($this->startResultCache(false)) {

	$arResult["ITEMS"] = array();

	$oAdapter = new $classType();

	$arFilter		= $arParams["FILTER_SIMPLE"];
	$arOrder		= $arParams["ORDER"];
	$addWhereSql	= $arParams["ADD_WHERE_SQL_NOTSAFE"];

	$arResult["ITEMS"]		= $oAdapter->getList($arFilter, $arOrder, false, $cntRowsDummy, $addWhereSql);

	$arResult["ADAPTER"]	= $oAdapter;

	if (count($arResult["ITEMS"]) > 0) {
		$this->setResultCacheKeys(array());
		$this->includeComponentTemplate();
	} else {
		$this->abortResultCache();
	}
}

?>