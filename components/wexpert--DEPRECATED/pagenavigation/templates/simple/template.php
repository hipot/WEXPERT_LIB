<?
if(!$arResult["NavShowAlways"])
{
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
		return;
}
$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");
$arResult["sUrlPath"] = preg_replace("/page[0-9]{1,2}\//", "", $arResult["sUrlPath"]);
$Q = $strNavQueryStringFull;
$halfW = floor($arResult["nPageWindow"]/2);
$pages = ceil($arResult["NavRecordCount"]/$arResult["NavPageSize"]);
$from = $arResult["NavPageNomer"] - $halfW;
if($from < 1){
	$from = 1;
}
$to = $from + $arResult["nPageWindow"];
if($to > $pages){
	$to = $pages;
}
$arResult["nStartPage"] = $from;
$arResult["nEndPage"] = $to;
?>
<!-- <?=$arResult["sUrlPath"]?> -->
<div class="pages">
	<p>Страницы:</p>
	<ul>
	<?while($arResult["nStartPage"] <= $arResult["nEndPage"]):?>
		<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
			<li><span><?=$arResult["nStartPage"]?></span></li>
		<?elseif($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false):?>
			<li><a href="<?=$arResult["sUrlPath"]?><?//=$strNavQueryStringFull?><?=$Q?>"><?=$arResult["nStartPage"]?></a></li>
		<?else:?>
			<li><a href="<?=$arResult["sUrlPath"]?>page<?=$arResult["nStartPage"]?>/<?=$Q?>"><?=$arResult["nStartPage"]?></a></li>
		<?endif?>
		<?$arResult["nStartPage"]++;?>
	<?endwhile;?>
	</ul>
	<div class="clear"></div>
</div>
