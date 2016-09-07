<a href="http://maps.yandex.ru/?ll=<?=$sh['PROPERTY_PLACE_PROPERTY_POINT_VALUE']?>&pt=<?=$sh['PROPERTY_PLACE_PROPERTY_POINT_VALUE']?>&key=<?=$MAP_KEY?>">Точка на карте</a>
описание точек http://api.yandex.ru/maps/staticapi/doc/dg/concepts/markers.xml

http://maps.yandex.ru/?z=14&ll=37.607026138615,55.777494391948&pt=37.607026138615,55.777494391948&key=ANnIbE0BAAAAcY9iXQIAThlyCsSDiizqV-x0Za69CBhjObcAAAAAAAAAAABGXGlv5xa2UTWXMiJVrTjo2ngyTQ==

получение ключа APIkey
<?
$MAP_KEY = '';
$strMapKeys = COption::GetOptionString('fileman', 'map_yandex_keys');

$strDomain = $_SERVER['HTTP_HOST'];
$wwwPos = strpos($strDomain, 'www.');
if ($wwwPos === 0)
$strDomain = substr($strDomain, 4);

if ($strMapKeys)
{
	$arMapKeys = unserialize($strMapKeys);

	if (array_key_exists($strDomain, $arMapKeys))
	$MAP_KEY = $arMapKeys[$strDomain];
}
?>

<script>
$(function(){
	$('.tabber .tbs > div').click(function(){
		var me = $(this);
		if(me.hasClass('a')) return false;
		me.addClass('a').siblings().removeClass('a');
		$(this).parent().parent().find('.cntnts > div:eq('+me.index()+')').addClass('a').siblings().removeClass('a');
		if($('.bx-google-map').length){
			var gnm = $('.bx-google-map').attr('id');
			var gn = gnm.split('_')[3];
			window['init_MAP_'+gn]();
			window['BX_SetPlacemarks_MAP_'+gn]();
		}
	});
});
</script>