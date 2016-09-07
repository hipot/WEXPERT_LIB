<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$images_dir = 'http://'.$_SERVER['HTTP_HOST'].$this->__folder.'/images';
?>

<div class="mngr-card">
	<table class="top">
		<tr>
			<? if ($arResult[ 'PREVIEW_PICTURE' ]): ?>
			<td>
				<img src="<?=CImg::Resize($arResult['PREVIEW_PICTURE'],165,165,'CROP_TOP')?>" alt="Чеботарев Александр Анатольевич" />
			</td>
			<? endif; ?>
			<td class="name">
				<?=$arResult['NAME']?> <br>
				<span class="pos"><?=$arResult['P']['position']['VALUE']?></span>
			</td>
		</tr>
	</table>
	<div class="info">
		<? if (trim($arResult[ 'P' ][ 'education' ][ 'VALUE' ])!='' || trim($arResult['P']['prim_education']['VALUE']['TEXT'])!=''): ?>
		<p><b>Образование:</b> <?=$arResult['P']['education']['VALUE']?></p>
		<? if (trim($arResult['P']['prim_education']['VALUE']['TEXT'])!=''){
		echo html_entity_decode($arResult['P']['prim_education']['VALUE']['TEXT']);
		}?>
		<br>
		<? endif; ?>

		<? if (trim($arResult['P']['add_education']['VALUE']['TEXT'])!=''): ?>
		<p><b>Дополнительное образование:</b></p>
		<?
		echo html_entity_decode($arResult['P']['add_education']['VALUE']['TEXT']);?>
		<br>
		<? endif; ?>

		<? if (trim($arResult['P']['experience']['VALUE']['TEXT'])!=''): ?>
			<p><b>Опыт работы:</b></p>
		<?
			echo html_entity_decode($arResult['P']['experience']['VALUE']['TEXT']);?>
		<br>
		<? endif; ?>
	</div>
</div>
<br clear="all" />