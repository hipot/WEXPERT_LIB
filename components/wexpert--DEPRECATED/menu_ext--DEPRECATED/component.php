<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
// $arParams['CONTINUE_EMPTY'] - (boolean) пропускать ли пустые секции
// $arParams['CONTINUE_PARENT'] - (boolean) пропускать ли пустые корневые секции
$arParams['CONTINUE_EMPTY'] = (!isset($arParams['CONTINUE_EMPTY']))?$arParams['CONTINUE_EMPTY']:false;
$arParams['CONTINUE_PARENT'] = (!isset($arParams['CONTINUE_PARENT']))?$arParams['CONTINUE_PARENT']:false;
if(!isset($arParams['CACHE_TIME']))
	$arParams['CACHE_TIME'] = 3600;
$arParams['CACHE_TIME']	= (COption::GetOptionString("main", "component_cache_on", "Y") == "N") ? 0 : $arParams['CACHE_TIME'];
// настройка кеша
$curDir = $GLOBALS["APPLICATION"]->GetCurDir();
$CACHE_ID = "left.menu_".$curDir;
$CACHE_DIR = "php";
$obMenuCache = new CPHPCache;
if ($obMenuCache->StartDataCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_DIR)) {
	CModule::IncludeModule("iblock");

	// выборка
	$arFilter = array("IBLOCK_ID"=> $arParams["IBLOCK_ID"], "ACTIVE"=>"Y");

	$db_list = CIBlockSection::GetList(array("LEFT_MARGIN"=>"ASC"), $arFilter, true);
	while($ar_result = $db_list->GetNext())
	{

		$arResult["ITEMS"][$ar_result["ID"]] = $ar_result;
		$arResult["PARENTS"][] = $ar_result["IBLOCK_SECTION_ID"];
	}

	// находим текущий каталог
	foreach($arResult["ITEMS"] as $k => $t){
		if($t["SECTION_PAGE_URL"] == $GLOBALS["APPLICATION"]->GetCurDir()){
			$s = $k;
			break;
		}
	}
	// делаем выделенными всех предков
	while($s > 0){
		$arResult["ITEMS"][$s]["SELECTED"] = 1;
		$s = (int)$arResult["ITEMS"][$s]["IBLOCK_SECTION_ID"];
	}

	$obMenuCache->EndDataCache(array("arResult" => $arResult)); // помним в кеш

} else {
	$arVars = $obMenuCache->GetVars(); // берем кеш
	$arResult = $arVars["arResult"]; // в удобную переменную
}

// раскидываем меню
foreach ($arResult["ITEMS"] as $item){
	$parent = (in_array($item["ID"], $arResult["PARENTS"]))?1:0; // решаем отец или нет
	if($arParams['CONTINUE_EMPTY']){ // пропускаем пустые секции
		if($item["ELEMENT_CNT"] == 0){
			if($arParams['CONTINUE_PARENT'] && $parent == 0){
				continue;
			} else{
				continue;
			}
		}
	}
	$arExtMenu[] = array(
		$item["NAME"],  // имя TEXT
		$item["SECTION_PAGE_URL"], // ссылка LINK
		array(), // ADDITIONAL_LINKS
		array( // параметры PARAMS
			"FROM_IBLOCK" => 'Y',
			"DEPTH_LEVEL" => $item["DEPTH_LEVEL"],
			"IS_PARENT" => $parent,
			"SELECTED" => $item['SELECTED'],
		),
		true
	);
}
return $arExtMenu;
?>