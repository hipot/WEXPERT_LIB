<?
/**
 * Позволяет реализовать натуральную сортировку
 * Скрипт собирает все названия элементов (и артикулов) из каталога в массив
 * сортирует его при помощи natsort(); и индекс ячейки записывает в свойство
 */
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');
define('BX_UTF', true);
@set_time_limit(0);

$_SERVER['DOCUMENT_ROOT'] = '/var/www/ph378389/data/www/ph378389-260520151830037.www10.pagehost.ru';
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_BUFFER_USED', true);
define('BX_NO_ACCELERATOR_RESET', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$IBLOCK_ID = 24;
$sortByNameProp = 'NAME_SORT';
$sortByArtProp = 'ARTICULE_SORT';

//получаем все имена элементов
CModule::IncludeModule('iblock');
$rElem = CIBlockElement::GetList(
	array('NAME' => 'ASC'),
	array('IBLOCK_ID' => $IBLOCK_ID,'ACTIVE' => 'Y'),
	false,
	false,
	array('ID','NAME','PROPERTY_ARTICULE')
);
$arData = array();
while ($arElem = $rElem->Fetch()) {
	$id = $arElem['ID'];
	$arData['BY_NAME'][ $id ] = $arElem['NAME'];
	$arData['BY_ARTICULE'][ $id ] = $arElem['PROPERTY_ARTICULE_VALUE'];
}

natsort($arData['BY_NAME']);
natsort($arData['BY_ARTICULE']);

$arData['BY_NAME'] = array_keys($arData['BY_NAME']);
$arData['BY_NAME'] = array_flip($arData['BY_NAME']);

$arData['BY_ARTICULE'] = array_keys($arData['BY_ARTICULE']);
$arData['BY_ARTICULE'] = array_flip($arData['BY_ARTICULE']);

foreach ($arData['BY_NAME'] as $id => $value) {
	$sortByNameVal	= $value;
	$sortByArtVal	= $arData['BY_ARTICULE'][ $id ];

	CIBlockElement::SetPropertyValuesEx($id, $IBLOCK_ID, array(
		$sortByNameProp	=> $sortByNameVal,
		$sortByArtProp	=> $sortByArtVal,
	));
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
?>