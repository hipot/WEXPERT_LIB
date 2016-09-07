<?
/**
 * Уникальный компонент для вывода карточки из инфоблока
 *
 * @version 2.0
 * @copyright 2011, WebExpert
 * @uses NewImage()
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/* @var $this CBitrixComponent */

if (! isset($arParams["CACHE_TIME"])) {
	$arParams["CACHE_TIME"] = 3600;
}

/**
 * Основные параметры:
 *
 * IBLOCK_ID / инфоблок, не обязательно для указания
 * FILTER / если нужна еще какая-то фильтрация
 * SELECT / какие еще поля могут понадобится по-умолчанию array("ID", "CODE", "DETAIL_PAGE_URL", "NAME")
 * GET_PROPERTY / Y – вывести все свойства
 * CACHE_TIME / время кеша (по-умолчанию 3600)
 *
 * Дополнительные параметры:
 *
 * SET_404 / Y установить ли ошибку 404 в случае пустой выборки (по-умолчанию Y)
 * ALWAYS_INCLUDE_TEMPLATE / Y подключать ли шаблон компонента в случае пустой выборки (по-умолчанию N)
 * TEMPLATE_DETAIL_URL / поменять ли шаблоны для ссылок на детальные страницы (по-умолчанию из настроек инфоблока)
 * IMG_WIDTH / к какой ширине подгонать картники из DETAIL_PICTURE если не указано, то они не подгоняются,
 * не забываем указывать в выборке $arParams['SELECT'] ключ DETAIL_PICTURE
 * IMG_HEIGHT / к какой высоте подогнать картники из DETAIL_PICTURE если не указано, то они не подгоняются
 * IMG_WIDTH, IMG_HEIGHT можно задавать как по отдельности, так и совместно (зависит от реализации плота)
 * параметры результирующей картинки получаем в ключе c '_AR' на конце: $arResult["DETAIL_PICTURE_AR"]
 * параметры оригинала сохраняются в ключе с суффиксом '_ORIGINAL', т.е. $arResult["DETAIL_PICTURE_ORIGINAL"]
 * INCLUDE_SEO / Y установить ли сео у страницы, по-умолчанию Y
 */

$arParams["CODE"] = trim($arParams["CODE"]);
$arParams["ID"]   = intval($arParams["ID"]);


if (! isset($arParams["CACHE_TIME"])) {
	$arParams["CACHE_TIME"] = 3600;
}

$arParams['PAGEN_1']		= intval($_REQUEST['PAGEN_1']);
$arParams['IMG_WIDTH']		= intval($arParams['IMG_WIDTH']);
$arParams['IMG_HEIGHT']		= intval($arParams['IMG_HEIGHT']);
$arParams["SET_404"]		= in_array($arParams["SET_404"], array('Y', 'N')) ? $arParams["SET_404"] : 'Y';
$arParams["INCLUDE_SEO"]	= in_array($arParams["INCLUDE_SEO"], array('Y', 'N')) ? $arParams["INCLUDE_SEO"] : 'Y';

/**
 * подключен ли плот - функция автоматической трансформации картинок
 * @var bool
 */
$bIncludedPlot = (function_exists('NewImage'));



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
	$arSelect = array("ID", "CODE", "DETAIL_PAGE_URL", "NAME");
	if ($arParams["SELECT"]) {
		$arSelect = array_merge($arSelect, $arParams["SELECT"]);
	}


	// QUERY 1 MAIN
	$rsItems = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

	if ($arParams["TEMPLATE_DETAIL_URL"] != "") {
		$rsItems->SetUrlTemplates($arParams["TEMPLATE_DETAIL_URL"]);
	}

	/**
	 * картинки для трансформации (может быть PREVIEW_PICTURE и DETAIL_PICTURE)
	 * @var array
	 */
	$arPicsToTransform = array(
		'PREVIEW_PICTURE',
		'DETAIL_PICTURE'
	);

	if ($arItem = $rsItems->GetNext()) {

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
					$res = CIBlockElement::GetList(array(), $arLinkedFilter, false, false, array("ID", "CODE", "NAME"));
					$ar_res = $res->GetNext();
					$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_res;
					$arItem['PROPERTIES'][ '~' . $ar_props['CODE'] . '_RAW' ] = $ar_props;
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

		$arResult = $arItem;
	}

	/**
	 * @todo Всяческие довыборки на произвольный параметр $arParams писать тут
	 * оставить комментарий по параметру, где этот параметр используется
	 */
	if ($arParams['CATALOG_DETAIL'] == 'Y') {
		$rsSection = CIBlockSection::GetByID( intval($arItem['IBLOCK_SECTION_ID']) );
		if ($arSect = $rsSection->GetNext()) {
			$arResult['SECTION'] = array(
				'ID' => $arSect['ID'],
				'SECTION_PAGE_URL'	=> $arSect['SECTION_PAGE_URL'],
				'NAME'	=> $arSect['NAME'],
			);
			
			$arResult['SERIES'] = array(
				'ID'		=> $arResult['PROPERTIES']['series']['ID'],
				'NAME'		=> $arResult['PROPERTIES']['series']['NAME'],
				'DETAIL_PAGE_URL'	=> $arSect['SECTION_PAGE_URL'] . $arResult['PROPERTIES']['series']['CODE'] . '/',
			);
		}
				
		$arResult['WRITER_NAME'] = $arResult['PROPERTIES']['writer']['NAME'];
	}


	if ($arResult['ID'] > 0) {
		$this->SetResultCacheKeys(array(
			'ID',
			'NAME',
			'DETAIL_PAGE_URL',
			'SECTION',
			'SERIES',
			'WRITER_NAME'
		));
	} else {
		if ($arParams["SET_404"] == "Y") {
			@include_once($_SERVER["DOCUMENT_ROOT"] . "/404_inc.php");
		}

		$this->AbortResultCache();
	}

	if ($arResult['ID'] > 0 || $arParams["ALWAYS_INCLUDE_TEMPLATE"] == "Y") {
		$this->IncludeComponentTemplate();
	}
}



// детальная каталога
if ($arParams['CATALOG_DETAIL'] == 'Y') {
	$APPLICATION->SetPageProperty('AUTHOR_BLOCK', '<div class="author_blk">'.$arResult['WRITER_NAME'].'</div>');
	if ($arResult['SECTION']['ID'] > 0) {
		$APPLICATION->AddChainItem($arResult['SECTION']['NAME'], $arResult['SECTION']['SECTION_PAGE_URL']);
	}
	if ($arResult['SERIES']['ID'] > 0) {
		$APPLICATION->AddChainItem($arResult['SERIES']['NAME'], $arResult['SERIES']['DETAIL_PAGE_URL']);
	}
}


/**
 * @todo установка SEO-параметров, хлебные крошки и заголовок
 */
if ($arResult['ID'] > 0 && $arParams['INCLUDE_SEO'] == 'Y') {
	$APPLICATION->SetTitle($arResult["NAME"]);
	$APPLICATION->AddChainItem($arResult["NAME"], $arResult['DETAIL_PAGE_URL']);
}
?>