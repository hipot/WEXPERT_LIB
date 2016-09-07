<?
// выбрать дни: начало активности сегодня или конец активности сегодня или активный период сегодня

// все кроме будущих
$arFilter = array('<=DATE_ACTIVE_FROM'=>ConvertTimeStamp(time()));

$eFilter = array('IBLOCK_ID'=>3, 'ACTIVE'=>'Y',
	array(
		'LOGIC'=>'OR',
		array(
			'>=DATE_ACTIVE_FROM'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 00:00:00" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
			'<=DATE_ACTIVE_FROM'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 23:59:59" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
		),
		array(
			'>=DATE_ACTIVE_TO'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 00:00:00" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
			'<=DATE_ACTIVE_TO'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 23:59:59" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
		),
		array(
			'<=DATE_ACTIVE_FROM'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 00:00:00" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
			'>=DATE_ACTIVE_TO'=>ConvertTimeStamp(MakeTimeStamp($arParams['GET']['date']." 23:59:59" ,"DD.MM.YYYY HH:MI:SS"), "FULL"),
		),
	),
);


// hl-блоки
function ShowPoster()
{
	$dm			= __getHl('SupportPoster');
	$arPosters 	= $dm::getList(array(
		'select' 	=> array('*'),
		'filter' 	=> array(
			">=UF_DATE_TO" 				=> array(date('d.m.Y H:i:s'), false),
			"<=UF_DATE_FROM"			=> array(date('d.m.Y H:i:s'), false)
		),
		'order'		=> array('ID' => 'DESC'),
		'limit'		=> 1
	))->fetchAll();

	if (count($arPosters) > 0) {
		echo '<div class="global_alert_message">';
	}
	foreach ($arPosters as $poster) {
		echo $poster['UF_MESSAGE'];
	}
	if (count($arPosters) > 0) {
		echo '</div>';
	}
}


?>