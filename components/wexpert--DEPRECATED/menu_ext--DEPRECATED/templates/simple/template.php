<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$curDir = $APPLICATION->GetCurPage(false);?>
<ul>
<?foreach ($arResult as $arItem):?>
    <li>
         <?if ($curDir == $arItem["LINK"]):?>
                  <?=$arItem["TEXT"];?>
         <?elseif ($arItem["SELECTED"]):?>
                  <a href="<?=$arItem["LINK"];?>"><b><?=$arItem["TEXT"];?></b></a>
         <?else:?>
                  <a href="<?=$arItem["LINK"];?>"><?=$arItem["TEXT"];?></a>
         <?endif?>
     </li>
<?endforeach;?>
</ul>