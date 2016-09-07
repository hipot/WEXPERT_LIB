<?
/**
 * $arParams -->
 * 'IBLOCK_ID' - идентификатор инфоблока
 * 'arOrder' - сортировка
 * 'arFilter' - фильтр
 * 'arSelect' - выбираемые поля
 * 'nTopCount' - ограничить количестуво сверху
 * 'nPageSize' - количество элементов на странице при постраничной навигации
 * 'SELECT_PROP' - выбирать ли свойства
 * 'IMAGE' - массив. кого превратить в путь к картинке
 * 'IMAGE'=>array('PREVIEW_PICTURE', 'DETAIL_PICTURE')
 * 'CROP' - массив. кому обрезание. обрезанная картинка доступна из '_'+имя свойства
 * 'CROP' => array('PREVIEW_PICTURE'=>array('W'=>200, 'H'=>200) или просто число 200 - это высота)
 * 'SHOW_404' - (Y / N) выводить 404 ошибку и файл 404_inc.php
 * 'GET_SECTIONS' - выбирать ли секции
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams['PAGEN_1'] = $_REQUEST['PAGEN_1'];
$arParams['IMAGE'] = empty($arParams['IMAGE']) ? array() : $arParams['IMAGE'];
$arParams['IMAGE'] = array_merge($arParams['IMAGE'], array('PREVIEW_PICTURE', 'DETAIL_PICTURE'));

if($this->StartResultCache()) {
	CModule::IncludeModule("iblock");

	if($arParams['GET_SECTIONS']){
		$rsSec = CIBlockSection::GetList(array('SORT'=>'ASC'), array('IBLOCK_ID'=>$arParams['IBLOCK_ID'], 'ACTIVE'=>'Y'), true );
		while ($arSec = $rsSec->GetNext()){
			$arResult['SECTIONS'][ $arSec['ID'] ] = $arSec;
		}
	}

	$arOrder = array('SORT'=>'ASC');
	if (is_array($arParams["arOrder"]))
		$arOrder = $arParams["arOrder"];
	
	$arFilter = array('IBLOCK_ID'=>$arParams['IBLOCK_ID'], 'ACTIVE'=>'Y');
	$arFilter = !empty($arParams['arFilter']) ? array_merge($arFilter, $arParams['arFilter']) : $arFilter;
	
	$arNavParams = false;
	if(isset($arParams["nTopCount"]))
		$arNavParams["nTopCount"] = $arParams["nTopCount"];
	if ($arParams["nPageSize"] > 0)
		$arNavParams["nPageSize"] = $arParams["nPageSize"];

	$arSelect = array(
	    "ID",
	    "DETAIL_PAGE_URL",
	    "NAME"
	);
	if ($arParams["SELECT"]) {
    	$arSelect = array_merge($arSelect, $arParams["SELECT"]);

	$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);
	while ($arItem = $rsItems->GetNext()){
		if ($arParams["SELECT_PROP"] == "Y") {
			$rsProperties = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arItem["ID"], array("sort" => "asc"), array());
			while ($arProperty = $rsProperties->GetNext()) {
				if ($arProperty["MULTIPLE"] == "Y")	{
					$arItem[ $arProperty["CODE"] ][]       = $arProperty["VALUE"];
					$arItem[ '~' . $arProperty["CODE"] ][] = $arProperty["~VALUE"];
				} else {
					$arItem[ $arProperty["CODE"] ]       = $arProperty["VALUE"];
					$arItem[ '~' . $arProperty["CODE"] ] = $arProperty["~VALUE"];
				}
			}
		}

		foreach ($arParams['IMAGE'] as $i){
			$arItem[$i] = CFile::GetPath($arItem[$i]);
		}

		foreach ($arParams['CROP'] as $n=>$p){
			$p = (is_array($p))?$p:array('W'=>$p);
			$fa = CFile::MakeFileArray($arItem[$n]);
			if(isset($p['W'])){
				$w = $p['W'];
			} else{
				$w = round($p['H'] * $fa["WIDTH"] / $fa["HEIGHT"]);
			}
			if(isset($p['H'])){
				$h = $p['H'];
			} else{
				$h = round($p['W'] * $fa["HEIGHT"] / $fa["WIDTH"]);
			}
			if((int)$arItem[$n]>0){
				$arItem[$n] = CFile::GetPath($arItem[$n]);
			}
			$arItem['_'.$n] = NewImage($arItem[$n], 'list_'.$w.'x'.$h, $w, $h);
		}

		$arResult["ITEMS"][] = $arItem;
	}

	if ($arNavParams["nPageSize"]>0) {
		$rsItems->nPageWindow = ($arParams['nPageWindow']) ? $arParams['nPageWindow'] : 5;
		$arResult["NAV_STRING"] = $rsItems->GetPageNavString(array(), $arParams["PAGER_TEMPLATE_NAME"], false);
	}

	if (empty($arResult["ITEMS"])) {
		$this->AbortResultCache();
		if ($arParams["SHOW_404"] == "Y")
			require_once($_SERVER["DOCUMENT_ROOT"]."/404_inc.php");
	}
	else {
		$this->SetResultCacheKeys(array());
		$this->IncludeComponentTemplate();
	}
}

?>