<?
/**
 * $arParams -->
 * 'ID' - идентификатор элемента
 * 'arSelect' - выбираемые поля
 * 'SELECT_PROP' - выбирать ли свойства
 * 'IMAGE' - массив. кого превратить в путь к картинке
 * 'IMAGE'=>array('PREVIEW_PICTURE', 'DETAIL_PICTURE')
 * 'CROP' - массив. кому обрезание. обрезанная картинка доступна из '_'+имя свойства
 * 'CROP' => array('PREVIEW_PICTURE'=>array('W'=>200, 'H'=>200) или просто число 200 - это высота)
 * 'NO_404' - 404 не выводить?
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

$arParams['PAGEN_1'] = $_REQUEST['PAGEN_1'];
$arParams['IMAGE'] = empty($arParams['IMAGE'])?array():$arParams['IMAGE'];
$arParams['IMAGE'] = array_merge($arParams['IMAGE'], array('PREVIEW_PICTURE', 'DETAIL_PICTURE'));

if($this->StartResultCache(false))
{
	CModule::IncludeModule("iblock");

	$arOrder = array('SORT'=>'ASC');
	$arFilter = array('ID'=>$arParams['ID']);
	$arSelect = array();
	$arSelect = !empty($arParams['arSelect'])?$arParams['arSelect']:$arSelect;

	$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
	if ($arItem = $rsItems->GetNext()){
		if ($arParams["SELECT_PROP"] == "Y") {
			$rsProperties = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem["ID"], array("sort" => "asc"), array());
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

		$arResult = $arItem;
	}

	if(empty($arResult))
	{
		$this->AbortResultCache();
		if($arParams["NO_404"] != "Y")
			require_once($_SERVER["DOCUMENT_ROOT"]."/404_inc.php");
	}
	else
	{
		$this->SetResultCacheKeys(array());
		$this->IncludeComponentTemplate();
	}
}

?>