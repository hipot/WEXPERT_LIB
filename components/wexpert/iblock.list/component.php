<?
/**
 * Уникальный компонент всяческих листов элементов инфоблока
 *
 * @version 2.x, см. CHANGELOG.TXT
 * @copyright 2014, WebExpert
 */
if (! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/* @var $this CBitrixComponent */

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

$arParams['PAGEN_1']			= intval($_REQUEST['PAGEN_1']);
$arParams['SHOWALL_1']			= intval($_REQUEST['SHOWALL_1']);
$arParams['NAV_TEMPLATE']		= (trim($arParams['NAV_TEMPLATE']) != '') ? $arParams['NAV_TEMPLATE'] : '';
$arParams['NAV_SHOW_ALWAYS']	= (trim($arParams['NAV_SHOW_ALWAYS']) == 'Y') ? 'Y' : 'N';

if ($this->StartResultCache(false)) {
	CModule::IncludeModule("iblock");

	if ($arParams["ORDER"]) {
		$arOrder = $arParams["ORDER"];
	} else {
		$arOrder = array("SORT" => "ASC");
	}

	$arFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y");
	if (count($arParams["FILTER"]) > 0) {
		$arFilter = array_merge($arFilter, $arParams["~FILTER"]);
	}

	$arNavParams = false;
	if ($arParams["NTOPCOUNT"] > 0) {
		$arNavParams["nTopCount"] = $arParams["NTOPCOUNT"];
	} else if ($arParams["PAGESIZE"] > 0) {
		$arNavParams["nPageSize"]	= $arParams["PAGESIZE"];
		$arNavParams["bShowAll"]	= ($arParams['NAV_SHOW_ALL'] == 'Y');
	}

	$arSelect = array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "NAME", "TIMESTAMP_X");
	if ($arParams["SELECT"]) {
		$arSelect = array_merge($arSelect, $arParams["SELECT"]);
	}

	// QUERY 1 MAIN
	$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);

	while ($arItem = $rsItems->GetNext()) {

		if ($arParams['GET_PROPERTY'] == "Y") {
			// QUERY 2
			$db_props = CIBlockElement::GetProperty(
				$arItem["IBLOCK_ID"],
				$arItem['ID'],
				array("sort" => "asc"),
				array("EMPTY" => "N")
			);
			while ($ar_props = $db_props->GetNext()) {
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

		/*
		 * TOFUTURE Всяческие довыборки на каждый элемент $arItem по произвольному
		 * параметру $arParams писать тут
		 * оставить комментарий по параметру, где этот параметр используется
		 */

		$arResult["ITEMS"][] = $arItem;
	}

	/*
	 * TOFUTURE Всяческие довыборки на произвольный параметр $arParams писать тут
	 * оставить комментарий по параметру, где этот параметр используется
	 */

	if (count($arResult["ITEMS"]) > 0) {
		if ($arParams["PAGESIZE"]) {
			if ($arParams['NAV_PAGEWINDOW'] > 0) {
				$rsItems->nPageWindow = $arParams['NAV_PAGEWINDOW'];
			}
			$arResult["NAV_STRING"] = $rsItems->GetPageNavStringEx(
				$navComponentObject,
				"",
				$arParams['NAV_TEMPLATE'],
				($arParams["NAV_SHOW_ALWAYS"] == 'Y')
			);

			$arResult["NAV_RESULT"] = array(
				'PAGE_NOMER'					=> $rsItems->NavPageNomer,		// номер текущей страницы постранички
				'PAGES_COUNT'					=> $rsItems->NavPageCount,		// всего страниц постранички
				'RECORDS_COUNT'					=> $rsItems->NavRecordCount,	// размер выборки, всего строк
				'CURRENT_PAGE_RECORDS_COUNT'	=> count($arResult["ITEMS"])	// размер выборки текущей страницы
			);
		}

		$this->SetResultCacheKeys(array(
			"NAV_RESULT"
		));
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

// TOFUTURE возвращаем результат (если нужно)
return $arResult;


?>