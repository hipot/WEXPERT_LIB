<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (! $arResult["NavShowAlways"]) {
	if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false)) {
		return;
	}
}

$arResult["NavQueryString"] = preg_replace('CODE=[^&]+','',$arResult["NavQueryString"]);
$arResult["NavQueryString"] = preg_replace('SECTION_CODE=[^&]+','',$arResult["NavQueryString"]);

$strNavQueryString     = ($arResult["NavQueryString"] != "")
						 ? $arResult["NavQueryString"] . "&amp;"
						 : "";
$strNavQueryStringFull = ($arResult["NavQueryString"] != "")
						 ? "?" . $arResult["NavQueryString"]
						 : "";
$arResult["sUrlPath"]  = preg_replace('/page[0-9]+\//', '', $arResult["sUrlPath"]);

?>
<div class="pages">
	<p><?=GetMessage('nav_title') ?></p>

	<ul>
	
		<?
		$bFirst = true;
		if ($arResult["NavPageNomer"] > 1 && $arResult["nStartPage"] > 1):
				$bFirst = false;
				$cl='';
				$class='';
				$num = 1;
				if ($arResult["bSavePage"]){
					$url = $arResult["sUrlPath"].'page1/'.$strNavQueryStringFull;
				} else{
					$url = $arResult["sUrlPath"].$strNavQueryStringFull;
				}
				if ($arResult["nStartPage"] > 2){
					$url = $arResult["sUrlPath"].'page'.round($arResult["nStartPage"] / 2).'/'.$strNavQueryStringFull;
					$num = '...';
					$cl="dots";
				}
				if(strlen($cl)>0){
					$class='class="'.$cl.'"';
				}
				?>
				<li>
					<a <?=$class?> href="<?=$url?>"><?=$num?></a>
				</li>
		<?endif;?>

		<?
		do {?>
			<li>
				<?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
					<span><?=$arResult["nStartPage"]?></span>
				<?else:
					if($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false){
						$url = $arResult["sUrlPath"].$strNavQueryStringFull;
					} else{
						$url = $arResult["sUrlPath"].'page'.$arResult["nStartPage"].'/'.$strNavQueryStringFull;
					}
				?>
				<a href="<?=$url?>"><?=$arResult["nStartPage"]?></a>
				
				<?endif;
				$arResult["nStartPage"]++;
				$bFirst = false;
				?>
			</li>
		<?} while ($arResult["nStartPage"] <= $arResult["nEndPage"]);
		?>

		<?if ($arResult["NavPageNomer"] < $arResult["NavPageCount"] && $arResult["nEndPage"] < $arResult["NavPageCount"]):?>
			<li>
				<?if ($arResult["nEndPage"] < ($arResult["NavPageCount"] - 1)):?>
				<a href="<?=$arResult["sUrlPath"]?>page<?=round($arResult["nEndPage"] + ($arResult["NavPageCount"] - $arResult["nEndPage"]) / 2)?>/<?=$strNavQueryStringFull?>" class="dots">...</a>
				<?endif;?>
				
				<a href="<?=$arResult["sUrlPath"]?>page<?=$arResult["NavPageCount"]?>/<?=$strNavQueryStringFull?>"><?=$arResult["NavPageCount"]?></a>
			</li>
		<?endif;?>
		
	</ul>

	<ul class="nav">
		<li>
		<?if($arResult["NavPageNomer"]-1 < 1):?>
		 <span><?=GetMessage('nav_prev') ?></span>
		<?else:?>
		 <a id="PrevLink" href="<?=$arResult["sUrlPath"]?>page<?=($arResult["NavPageNomer"]-1)?>/<?=$strNavQueryStringFull?>"><?=GetMessage('nav_prev') ?></a>
		<?endif;?>
		</li>
		
		<li>
		<?if($arResult[nEndPage] > $arResult[NavPageNomer]):?>
			<a id="NextLink" href="<?=$arResult["sUrlPath"]?>page<?=($arResult["NavPageNomer"]+1)?>/<?=$strNavQueryStringFull?>"><?=GetMessage('nav_next') ?></a>
		<?else:?>
			<span><?=GetMessage('nav_next') ?></span>
		<?endif;?>
		</li>
	</ul>

</div>


