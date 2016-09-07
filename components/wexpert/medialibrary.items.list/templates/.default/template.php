<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
if ($USER->IsAdmin()) {
	echo '<pre>', print_r($arResult, true), '</pre>';
}
?>