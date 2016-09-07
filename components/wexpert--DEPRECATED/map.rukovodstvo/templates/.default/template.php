<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$images_dir = 'http://'.$_SERVER['HTTP_HOST'].$this->__folder.'/images';

//region ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ
/**
 * Возвращает контент для балуна из массива мастеров
 *
 * @param $ar	массива с информацией о городе и списком мастеров
 *
 * @return mixed	контент для балуна
 */
$additionalFunctions[ 'getBalloonContent' ] = function($ar){
	ob_start();
	?>
	<h2><?=$ar['NAME']?></h2>
	<div class="mngr-box">
		<?foreach($ar['MANAGER_LIST'] as $k=>$m):?>
		<a title="<?=$m['NAME']?>" <?=($k%4==0)?'class="strt"':'';?> href="javascript:void(0)" data-id="<?=$m['ID']?>">
			<img src="<?=CImg::Resize($m['IMG'],83,83,'CROP_TOP')?>" alt="<?=$m['NAME']?>" /> <br>
			<?=$m['NAME']?>
		</a>
		<?endforeach;?>
		<br clear="all" />
	</div>
	<?
	$employeeListHTML = preg_replace('#[\r\n]*#','',ob_get_contents());
	ob_end_clean();
	return $employeeListHTML;
};
//endregion



foreach($arResult['PONT_LIST'] as $p){
	$balloonContent = $additionalFunctions[ 'getBalloonContent' ]($p);
	$arP = explode(',',$p['POINT']);
	if($arP[0] && $arP[1]){
		$arPlacemarks[] = array(
			$arP,
			array(
				'content'			=> $p['NAME'],
				'balloonContent'	=> $balloonContent
			),
			array(
				'iconImageHref'		=> $images_dir.'/point.png',
				'iconImageSize'		=> array(34,36),
				'iconImageOffset'	=> array(-1, -35)
			),
		);
	}
}
if(!empty($arPlacemarks)):?>
	<script type="text/javascript">
		var rukovodstvo_map_component = {};
		rukovodstvo_map_component.path = '<?=$this->__component->__path?>';
		ymaps.ready(function () {
			rukovodstvoMapInit(<?=json_encode($arPlacemarks)?>);
		});
	</script>
<?endif;?>

<div id="rukovodstvo-map" style="width: 493px; height: 420px"></div>

<noindex>
	<div class="mngr-popup">
		<div class="window">
			<div class="closer"></div>
			<div class="inner">
				-
			</div>
		</div>
	</div>
</noindex>