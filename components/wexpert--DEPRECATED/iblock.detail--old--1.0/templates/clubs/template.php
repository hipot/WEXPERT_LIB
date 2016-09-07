<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?//echo "<pre>"; print_r($arResult); echo "</pre>";?>

<?if(!empty($arResult)):?>

<div class="block">
	<table border="0" cellpadding="0" cellspacing="0">
		<tbody><tr>
			<td class="title">
				<span><?=$arResult['NAME']?></span>
			</td>
		</tr>
	</tbody></table>
	<div class="txt">
		<table class="info" border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>
				<td>Район расположения:</td>
				<td><?=$arResult['district']?></td>
			</tr>
			<tr>
				<td>Часы работы:</td>
				<td><?=$arResult['hours']?></td>
			</tr>
			<tr>
				<td>Тел.:</td>
				<td><?=$arResult['phone']?></td>
			</tr>
		</tbody></table>
		<?=($arResult['schedule']['TYPE']=='html')?$arResult['~schedule']['TEXT']:$arResult['schedule']['TEXT'];?>
		<div class="map_block">
			<big><b>Адрес и карта проезда:</b></big> <?=$arResult['adress']?>
			<br clear="all" />
			<div class="map">
				<?
				if
				$arPos = explode(",", $arResult['map']);
				$APPLICATION->IncludeComponent("bitrix:map.google.view", ".default", array(
					"MAP_DATA" => serialize(array(
			            'google_lat' => $arPos[0],
			            'google_lon' => $arPos[1],
			            'google_scale' => 17,
						'PLACEMARKS' => array (
			                array(
			                    'TEXT' => 'Клуб "'.$arResult["NAME"].'"',
			                    'LON' => $arPos[1],
			                    'LAT' => $arPos[0],
			                ),
			            ),
					)),
					"MAP_WIDTH" => "500",
					"MAP_HEIGHT" => "500",
				), $component
				);?>
			</div>
		</div>


		<br clear="all">
		<div class="mrg30"></div>
		<div class="text">
			<?if(trim($arResult['terms']['TEXT'])!=''):?>
			<big><b>Условия турниров:</b></big>
			<div class="mrg10"></div>
			<?=($arResult['terms']['TYPE']=='html')?$arResult['~terms']['TEXT']:$arResult['terms']['TEXT'];?>
			<br>
			<?endif;?>
			<?if(trim($arResult['ads']['TEXT'])!=''):?>
			<big><b>Объявления клубов</b></big><br>
			<?=($arResult['ads']['TYPE']=='html')?$arResult['~ads']['TEXT']:$arResult['ads']['TEXT'];?>
			<?endif;?>
		</div>
		<div class="mrg30"></div>
	</div>
</div>

<?endif;?>


