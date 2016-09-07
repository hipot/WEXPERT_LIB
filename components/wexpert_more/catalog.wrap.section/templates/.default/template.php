<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section",
	$arResult["TEMPLATE"],
	$arResult["PARAMS"]
);
?>