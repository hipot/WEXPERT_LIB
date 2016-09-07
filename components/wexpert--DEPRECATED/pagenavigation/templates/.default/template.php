<?
/*в компоненте if ($arParams["USE_PAGER"] == "Y")
	{
		$arResult['NAV_STRING'] = $rsItems->GetPageNavStringEx($navComponentObject, "", false);

		//количество ссылок на странице, после выборки:
		if(!isset($arParams["PAGES_ON_WINDOW"]))
			$arParams["PAGES_ON_WINDOW"] = 10;

		$rsItems->nPageWindow = $arParams["PAGES_ON_WINDOW"];
	}
*/?>
<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}

//echo "<pre>"; print_r($arResult);echo "</pre>";

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");
?>
<font class="text">

	<?if ($arResult["NavPageNomer"] > 1):?>
		<a href="<?=$strNavQueryString?>">первая</a>
		|
		<?if($arResult["NavPageNomer"] > 0):?>
			<?if($arResult["NavPageNomer"] - $arResult["nPageWindow"] < 1):?>
				<a href="<?=$strNavQueryString?>">группа назад</a>&nbsp;|
			<?else:?>
			    <a href="<?=$strNavQueryString?>page<?echo $arResult["NavPageNomer"]-$arResult["nPageWindow"]; ?>/">группа назад</a>&nbsp;|
			<?endif;?>
		<?endif;?>
		<?if(($arResult["NavPageNomer"]!=2)):?>
			<a href="<?=$strNavQueryString?>page<?=($arResult["NavPageNomer"]-1)?>/">пред.</a>
		<?else:?>
			<a href="<?=$strNavQueryString?>">пред.</a>
		<?endif?>
		|
	<?else:?>
		первая&nbsp;|&nbsp;группа назад&nbsp;|&nbsp;пред.&nbsp;|
	<?endif;?>

	<?while($arResult["nStartPage"] <= $arResult["nEndPage"]):?>

		<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
			<b><?=$arResult["nStartPage"]?></b>
		<?elseif($arResult["nStartPage"] == 1 ):?>
			<a href="<?=$strNavQueryString?>"><?=$arResult["nStartPage"]?></a>
		<?else:?>
			<a href="<?=$strNavQueryString?>page<?=$arResult["nStartPage"]?>/"><?=$arResult["nStartPage"]?></a>
		<?endif?>
		<?$arResult["nStartPage"]++?>
	<?endwhile?>
	|
	<?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
		<a href="<?=$strNavQueryString?>page<?=($arResult["NavPageNomer"]+1)?>/">след.</a>&nbsp;|
		<?if($arResult["NavPageNomer"] + $arResult["nPageWindow"] > $arResult["NavPageCount"]):?>
		    &nbsp;<a href="<?=$strNavQueryString?>page<?=$arResult["NavPageCount"]?>/">группа вперед</a>&nbsp;|
		<?else:?>
		    &nbsp;<a href="<?=$strNavQueryString?>page<?echo $arResult["NavPageNomer"]+$arResult["nPageWindow"]; ?>/">группа вперед</a>&nbsp;|
		<?endif;?>
		<a href="<?=$strNavQueryString?>page<?=$arResult["NavPageCount"]?>/">последняя</a>
	<?else:?>
		след.&nbsp;|&nbsp;группа вперед&nbsp;|&nbsp;последняя
	<?endif?>

</font>
