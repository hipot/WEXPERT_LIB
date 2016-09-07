<?
/**
 * Уникальный компонент для вывода карточки из инфоблока
 *
 * @version 2.4.1
 * @copyright 2012, WebExpert
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/* @var $this CBitrixComponent */


$arParams["CODE"] = trim($arParams["CODE"]);
$arParams["ID"]   = intval($arParams["ID"]);


$arParams["SET_404"]		= in_array($arParams["SET_404"], array('Y', 'N')) ? $arParams["SET_404"] : 'Y';
$arParams["INCLUDE_SEO"]	= in_array($arParams["INCLUDE_SEO"], array('Y', 'N')) ? $arParams["INCLUDE_SEO"] : 'Y';


if ($this->StartResultCache(false)) {
	CModule::IncludeModule("iblock");

	$arFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y");
	if (count($arParams["~FILTER"]) > 0) {
		$arFilter = array_merge($arFilter, $arParams["~FILTER"]);
	}
	if ($arParams["ID"] == 0 && $arParams["CODE"] == '') {
		// генерируем ошибку 404 если нет элемента
		$arFilter["ID"] = 0;
	} else if ($arParams["ID"] > 0) {
		$arFilter["ID"] = $arParams["ID"];
	} else if ($arParams["CODE"] != '') {
		$arFilter["CODE"] = $arParams["CODE"];
	}
	$arSelect = array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "NAME", "DETAIL_TEXT", "TIMESTAMP_X");
	if ($arParams["SELECT"]) {
		$arSelect = array_merge($arSelect, $arParams["SELECT"]);
	}

	// QUERY 1 MAIN
	$rsItems = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

	if ($arItem = $rsItems->GetNext()) {

		if ($arParams['GET_PROPERTY'] == "Y") {
			// QUERY 2
			$db_props = CIBlockElement::GetProperty(
				$arItem["IBLOCK_ID"],
				$arItem['ID'],
				array("sort" => "asc"),
				array("EMPTY" => "N")
			);
			while ($ar_props = $db_props->GetNext()) {
				if ($ar_props['PROPERTY_TYPE'] == "E") {
					$arLinkedFilter = array(
						"IBLOCK_ID"		=> $ar_props['LINK_IBLOCK_ID'],
						"ID"			=> $ar_props["VALUE"]
					);
					// QUERY 3
					$res = CIBlockElement::GetList(array(), $arLinkedFilter, false, false, array("ID", "NAME", "DETAIL_PAGE_URL"));
					$ar_res = $res->GetNext();
					$ar_props['ELEM'] = $ar_res;
				}

				// для свойств TEXT/HTML не верно экранируются символы
				if ($ar_props['PROPERTY_TYPE'] == "S" && isset($ar_props['VALUE']['TEXT'], $ar_props['VALUE']['TYPE'])) {
					$ar_props['VALUE']['TEXT'] = FormatText($ar_props['VALUE']['TEXT'], $ar_props['VALUE']['TYPE']);
				}

				if ($ar_props['MULTIPLE'] == "Y") {
					$arItem['PROPERTIES'][ $ar_props['CODE'] ][] = $ar_props;
				} else {
					$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_props;
				}
			}
		}


		/**
		 * TOFUTURE Всяческие довыборки на каждый элемент $arItem по произвольному параметру $arParams писать тут
		 * оставить комментарий по параметру, где этот параметр используется
		 */

		$arResult = $arItem;
	}

	/**
	 * TOFUTURE Всяческие довыборки на произвольный параметр $arParams писать тут
	 * оставить комментарий по параметру, где этот параметр используется
	 */

	if ($arResult['ID'] > 0) {
		$this->SetResultCacheKeys(array(
			'ID',
			'NAME',
			'DETAIL_PAGE_URL',
		));
	} else {
		if ($arParams["SET_404"] == "Y") {
			@include_once($_SERVER["DOCUMENT_ROOT"] . "/404_inc.php");
		}

		$this->AbortResultCache();
	}

	if ($arResult['ID'] > 0) {
		$this->IncludeComponentTemplate();
	}
}


/**
 * Установка SEO-параметров, хлебные крошки и заголовок
 */
if ($arResult['ID'] > 0 && $arParams['INCLUDE_SEO'] == 'Y') {
	$APPLICATION->SetTitle($arResult["NAME"]);
	$APPLICATION->AddChainItem($arResult["NAME"], $arResult['DETAIL_PAGE_URL']);
}

// TOFUTURE возвращаем результат (если нужно)
return $arResult;


?>