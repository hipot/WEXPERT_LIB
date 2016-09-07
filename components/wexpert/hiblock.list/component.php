<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$requiredModules = array('highloadblock');

foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return 0;
	}
}

$arParams['PAGEN_1']	= intval($_REQUEST['PAGEN_1']);
$arParams['SHOWALL_1']	= intval($_REQUEST['SHOWALL_1']);

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
if ($this->StartResultCache(false)) {

// hlblock info
	$hlblock_id = $arParams['IBLOCK_ID'];

	$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();

	if (empty($hlblock))
	{
		ShowError('404');
		return 0;
	}

	$entity = HL\HighloadBlockTable::compileEntity($hlblock);

// uf info
	$fields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);



// sort
	if ($arParams["ORDER"]) {
		$arOrder = $arParams["ORDER"];
	} else {
		$arOrder = array("ID" => "DESC");
	}

// limit
	$limit = array(
		'nPageSize' => intval($arParams["PAGESIZE"]) > 0 ? intval($arParams["PAGESIZE"]) : 10,
		'iNumPage' => is_set($arParams['PAGEN_1']) ? $arParams['PAGEN_1'] : 1,
		'bShowAll' => ($arParams['NAV_SHOW_ALL'] == 'Y'),
		'nPageTop' => intval($arParams["NTOPCOUNT"])
	);


	$arSelect = array("*");
	if (!empty($arParams["SELECT"])) {
		$arSelect = $arParams["SELECT"];
		$arSelect[] = "ID";
	}

	$arFilter = array();
	if (!empty($arParams["FILTER"])) {
		$arFilter = $arParams["FILTER"];
	}

	$arGroupBy = array();
	if (!empty($arParams["GROUP_BY"])) {
		$arGroupBy = $arParams["GROUP_BY"];
	}

	$entity_class = $entity->getDataClass();

	$result = $entity_class::getList(array(
		"order" => $arOrder,
		"select" => $arSelect,
		"filter" => $arFilter,
		"group" => $arGroupBy,
		"limit" => $limit["nPageTop"] > 0 ? $limit["nPageTop"] : 0,
	));


	if ($limit["nPageTop"] <= 0) {
		$result = new CDBResult($result);
		$result->NavStart($limit,false,true);

		$arResult["NAV_STRING"] = $result->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"]);
		$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
		$arResult["NAV_RESULT"] = $result;
	}

// build results
	$rows = array();

	$tableColumns = array();

	while ($row = $result->Fetch())
	{
		foreach ($row as $k => $v)
		{
			if ($k == "ID") {
				continue;
			}
			$arUserField = $fields[$k];

			$html = call_user_func_array(
				array($arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"),
				array(
					$arUserField,
					array(
						"NAME" => "FIELDS[".$row['ID']."][".$arUserField["FIELD_NAME"]."]",
						"VALUE" => htmlspecialcharsbx($v)
					)
				)
			);

			if($html == '')
			{
				$html = '&nbsp;';
			}

			$row[$k] = $html;
			$row["~".$k] = $v;
		}

		$rows[] = $row;
	}

	$arResult["ITEMS"] = $rows;




	if (count($arResult["ITEMS"]) > 0) {

		// добавли сохранение ключей по параметру
		$arSetCacheKeys = array();
		if (is_array($arParams['SET_CACHE_KEYS'])) {
			$arSetCacheKeys = $arParams['SET_CACHE_KEYS'];
		}

		$this->SetResultCacheKeys($arSetCacheKeys);
	} else {
		if ($arParams["SET_404"] == "Y") {
			@include_once($_SERVER["DOCUMENT_ROOT"] . "/404_inc.php");
		}

		$this->AbortResultCache();
	}

	if (count($arResult["ITEMS"]) > 0 || $arParams["ALWAYS_INCLUDE_TEMPLATE"] == "Y") {
		$this->IncludeComponentTemplate();
	}
}

return $arResult;