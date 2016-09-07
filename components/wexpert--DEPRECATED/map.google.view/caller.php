<?
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
);
?>