<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

$aMenuLinksExt = $APPLICATION->IncludeComponent("nobitrix:menu_ext", "", array(
	"IBLOCK_ID" => 2,
	"CACHE_TIME" => 3600
));
$aMenuLinks = (!empty($aMenuLinks))?array_merge($aMenuLinks, $aMenuLinksExt):$aMenuLinksExt;
?>