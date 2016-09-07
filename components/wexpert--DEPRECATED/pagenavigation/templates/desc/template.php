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

<?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
	<a href="<?=$strNavQueryString?>page<?=($arResult["NavPageNomer"]+1)?>/">пред.</a>&nbsp;|
	<?if($arResult["NavPageNomer"] + $arResult["nPageWindow"] > $arResult["NavPageCount"]):?>
	    &nbsp;<a href="<?=$strNavQueryString?>page<?=$arResult["NavPageCount"]?>/">группа назад</a>&nbsp;|
	<?else:?>
	    &nbsp;<a href="<?=$strNavQueryString?>page<?echo $arResult["NavPageNomer"]+$arResult["nPageWindow"]; ?>/">группа назад</a>&nbsp;|
	<?endif;?>
	<a href="<?=$strNavQueryString?>page<?=$arResult["NavPageCount"]?>/">первая</a>
<?else:?>
	первая&nbsp;|&nbsp;группа назад&nbsp;|&nbsp;пред.&nbsp;|
<?endif?>


<?while($arResult["nStartPage"] >= $arResult["nEndPage"]):?>
	<?$NavRecordGroupPrint = $arResult["NavPageCount"] - $arResult["nStartPage"] + 1;?>

	<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
		<b><?=$NavRecordGroupPrint?></b>
	<?elseif($arResult["nStartPage"] == $arResult["NavPageCount"] && $arResult["bSavePage"] == false):?>
		<a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>"><?=$NavRecordGroupPrint?></a>
	<?else:?>
		<a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>"><?=$NavRecordGroupPrint?></a>
	<?endif?>

	<?$arResult["nStartPage"]--?>
<?endwhile?>
|
<?if ($arResult["NavPageNomer"] > 1):?>
	<?if(($arResult["NavPageNomer"]!=2)):?>
		<a href="<?=$strNavQueryString?>page<?=($arResult["NavPageNomer"]-1)?>/">след.</a>
	<?else:?>
		<a href="<?=$strNavQueryString?>">след.</a>
	<?endif?>
	|
	<?if($arResult["NavPageNomer"] > 0):?>
		<?if($arResult["NavPageNomer"] - $arResult["nPageWindow"] < 1):?>
			<a href="<?=$strNavQueryString?>">группа вперед</a>&nbsp;|
		<?else:?>
		    <a href="<?=$strNavQueryString?>page<?echo $arResult["NavPageNomer"]-$arResult["nPageWindow"]; ?>/">группа вперед</a>&nbsp;|
		<?endif;?>
	<?endif;?>
	<a href="<?=$strNavQueryString?>">последняя</a>
<?else:?>
	след.&nbsp;|&nbsp;группа вперед&nbsp;|&nbsp;последняя
<?endif;?>

</font>