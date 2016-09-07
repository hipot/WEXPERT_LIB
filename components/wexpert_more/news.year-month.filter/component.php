<?
/**
 * Уникальный компонент всяческих листов элементов инфоблока
 *
 * @version 2.4.1
 * @copyright 2012, WebExpert
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
	$arData  = array();
	$rsItems = array();
	$arItem  = array();

	$rsItems = CIBlockElement::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], 'ACTIVE' => 'Y'), false, false, array("ACTIVE_FROM"));
	while ($arItem = $rsItems->GetNext()){
		$parse = ParseDateTime($arItem['ACTIVE_FROM'], "DD.MM.YYYY");
		$month = FormatDateFromDB($arItem['ACTIVE_FROM'], "f");
		$flag = 0;
		$flagYear = 0;
		if (!isset($arData["MONTH"][0])) {
			$arData["MONTH"][] = array(
				"MONTH" 	 => $parse["MM"],
				"MONTH_NAME" => $month,
				"YEAR"  	 => $parse["YYYY"]
			);
			$arData["YEAR"][] = $parse["YYYY"];
		} else {
			foreach ($arData["MONTH"] as $key=>$data) {
				if ($data["MONTH"] == $parse["MM"] && $data["YEAR"] == $parse["YYYY"]) {
					$flag = 1;
				}
				if ($data["YEAR"] == $parse["YYYY"]) {
					$flagYear = 1;
				}
			}
			if ($flag == 0) {
				$arData["MONTH"][] = array(
					"MONTH" 		=> $parse["MM"],
					"MONTH_NAME" 	=> $month,
					"YEAR"  		=> $parse["YYYY"]
				);
			}
			if ($flagYear == 0) {
				$arData["YEAR"][] = $parse["YYYY"];
			}
		}
	}
	sort($arData["MONTH"]);
	rsort($arData["YEAR"]);
	$arResult["DATA"] = $arData;

	if ($arParams["YEAR"] != null && $arParams["MONTH"] != null) {
		$year 			= $arParams["YEAR"];
		$monthStart 	= $arParams["MONTH"];
		$monthFinish	= $arParams["MONTH"];
		$dayFinish 		= '31';
		$key			= 1;
	} else {
		if ($arParams["YEAR"] != null) {
			$year			= $arParams["YEAR"];
			$monthStart		= '01';
			$monthFinish	= '12';
			$dayFinish		= '31';
			$key			= 1;
		} else {
			(double)$datetimenow = getmicrotime();
			$key = 0;
			$timenow = ConvertTimeStamp($datetimenow, "SHORT");
		}
	}

	if ($key == 0) {
		$arFilter = array("<=DATE_ACTIVE_FROM" => $timenow);
	} else {
		$arFilter = array(
			"><DATE_ACTIVE_FROM" => array('01.'.$monthStart.'.'.$year.'', $dayFinish.'.'.$monthFinish.'.'.$year)
		);
	}
	$arResult['arFilter'] = $arFilter;

	if (count($arResult["DATA"]) > 0) {
		$this->SetResultCacheKeys(array('arFilter'));
		$this->IncludeComponentTemplate();
	} else {
		$this->AbortResultCache();
	}
}



// TOFUTURE возвращаем результат (если нужно)
return $arResult['arFilter'];

?>