<?
/**
 * Уникальный компонент всяческих листов элементов инфоблока
 *
 * @version 2.0.1
 * @copyright 2011, WebExpert
 * @uses NewImage()
 */
if (! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/* @var $this CBitrixComponent */

/**
 * Основные параметры:
 *
 * IBLOCK_ID / конечно же указать инфоблок
 * ORDER / если нужна иная сортировка, по-умолчанию array("SORT" => "ASC")
 * FILTER / если нужна еще какая-то фильтрация
 * NTOPCOUNT / ограничение количества элементов (имеет более высокий приоритет над PAGESIZE)
 * PAGESIZE / сколько элементов на странице, при постраничной навигации
 * SELECT / какие еще поля могут понадобится по-умолчанию array("ID", "CODE", "DETAIL_PAGE_URL", "NAME")
 * GET_PROPERTY / Y – вывести все свойства
 * CACHE_TIME / время кеша (по-умолчанию 3600)
 *
 * Дополнительные параметры:
 *
 * NAV_TEMPLATE / шаблон постранички (по-умолчанию .default)
 * NAV_SHOW_ALWAYS / показывать ли постаничку всегда (по-умолчанию N)
 * NAV_PAGEWINDOW / ширина диапазона постранички, т.е. напр. тут ширина = 3 "1 .. 3 4 5 .. 50" (т.е. 3,4,5 - 3 шт)
 * SET_404 / Y установить ли ошибку 404 в случае пустой выборки (по-умолчанию N)
 * ALWAYS_INCLUDE_TEMPLATE / Y подключать ли шаблон компонента в случае пустой выборки (по-умолчанию N)
 * TEMPLATE_DETAIL_URL / поменять ли шаблоны для ссылок на детальные страницы (по-умолчанию из настроек инфоблока)
 * IMG_WIDTH / к какой ширине подгонать картники из PREVIEW_PICTURE, если не указано, то они не подгоняются
 * 		не забываем указывать в выборке $arParams['SELECT'] ключ PREVIEW_PICTURE
 * IMG_HEIGHT / к какой высоте подогнать картники из PREVIEW_PICTURE, если не указано, то они не подгоняются
 * IMG_WIDTH, IMG_HEIGHT можно задавать как по отдельности, так и совместно (зависит от реализации плота)
 * 		параметры результирующей картинки получаем в ключе c '_AR' на конце: $arResult["ITEMS"][0]["PREVIEW_PICTURE_AR"]
 * 		параметры оригинала сохраняются в ключе с суффиксом '_ORIGINAL', т.е. $arResult["ITEMS"][0]["PREVIEW_PICTURE_ORIGINAL"]
 */

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if (! isset($arParams["CACHE_TIME"])) {
	$arParams["CACHE_TIME"] = 3600;
}

$arParams['PAGEN_1']			= intval($_REQUEST['PAGEN_1']);
//$arParams['SHOWALL_1']		= intval($_REQUEST['SHOWALL_1']);
$arParams['NAV_TEMPLATE']		= (trim($arParams['NAV_TEMPLATE']) != '') ? $arParams['NAV_TEMPLATE'] : '';
$arParams['NAV_SHOW_ALWAYS']	= (trim($arParams['NAV_SHOW_ALWAYS']) == 'Y') ? 'Y' : 'N';
$arParams['IMG_WIDTH']			= intval($arParams['IMG_WIDTH']);
$arParams['IMG_HEIGHT']			= intval($arParams['IMG_HEIGHT']);


/**
 * подключен ли плот - функция автоматической трансформации картинок
 * @var bool
 */
$bIncludedPlot = (function_exists('NewImage'));



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
		$arNavParams["bShowAll"]	= false;
	}

	$arSelect = array("ID", "IBLOCK_ID", "CODE", "DETAIL_PAGE_URL", "NAME");
	if ($arParams["SELECT"]) {
		$arSelect = array_merge($arSelect, $arParams["SELECT"]);
	}

	// QUERY 1 MAIN
	$rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);

	if ($arParams["TEMPLATE_DETAIL_URL"] != "") {
		$rsItems->SetUrlTemplates($arParams["TEMPLATE_DETAIL_URL"]);
	}
	
	$arResult["SELECTED_ROWS_COUNT"] = $rsItems->SelectedRowsCount();

	/**
	 * картинки для трансформации (может быть PREVIEW_PICTURE и DETAIL_PICTURE)
	 * @var array
	 */
	$arPicsToTransform = array(
		'PREVIEW_PICTURE',
		'DETAIL_PICTURE'		// не нужно в лентах, но на будущее
	);

	while ($arItem = $rsItems->GetNext()) {

		if ($arParams['GET_PROPERTY'] == "Y") {
			// QUERY 2
			$db_props = CIBlockElement::GetProperty(
				$arParams["IBLOCK_ID"],
				$arItem['ID'],
				array("sort" => "asc"),
				array("EMPTY" => "N")
			);
			while ($ar_props = $db_props->GetNext()) {
				if ($ar_props['MULTIPLE'] == "Y") {
					$arItem['PROPERTIES'][ $ar_props['CODE'] ][] = $ar_props;
				} elseif ($ar_props['PROPERTY_TYPE'] == "E") {
					$arLinkedFilter = array(
						"IBLOCK_ID"		=> $ar_props['LINK_IBLOCK_ID'],
						"ID"			=> $ar_props["VALUE"]
					);
					// QUERY 3
					$res = CIBlockElement::GetList(array(), $arLinkedFilter, false, false, array("ID", "NAME"));
					$ar_res = $res->GetNext();
					$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_res;
				} else {
					$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_props;
				}
			}
		}

		// $pictureKey - это либо PREVIEW_PICTURE, либо DETAIL_PICTURE
		foreach ($arPicsToTransform as $pictureKey) {
			if ($arItem[$pictureKey] > 0) {

				$arPicParams		= CFile::GetFileArray($arItem[$pictureKey]);
				$arPicParams['SRC']	= CFile::GetPath($arItem[$pictureKey]);

				if ($bIncludedPlot && (($arParams['IMG_WIDTH'] > 0) || ($arParams['IMG_HEIGHT'] > 0))) {
					$width	= $arParams['IMG_WIDTH'];
					$height	= $arParams['IMG_HEIGHT'];
					$type	= 'list_' . $width;
					if ($height > 0) {
						$type .= 'h' . $height;
					}
					$arItem[$pictureKey . "_AR"] = NewImage(CFile::GetPath($arItem[$pictureKey]), $type, $width, $height);
				} else {
					$arItem[$pictureKey . "_AR"] = $arPicParams;
				}

				// оригинальные параметры картинки
				$arItem[$pictureKey . "_ORIGINAL"] = $arPicParams;
			}
		}



		/**
		 * @todo Всяческие довыборки на каждый элемент $arItem по произвольному параметру $arParams писать тут
		 * оставить комментарий по параметру, где этот параметр используется
		 */

		$arResult["ITEMS"][] = $arItem;
	}

	/**
	 * @todo Всяческие довыборки на произвольный параметр $arParams писать тут
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
		}

		$this->SetResultCacheKeys(array(
			'SELECTED_ROWS_COUNT'
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


return $arResult;
?>