<?
/**
 * Генератор кодов инфоблока
 *
 * @author WebExpert, 2015
 */
AddEventHandler("iblock", "OnAfterIBlockAdd",			"IblockBidsGenerateHandler");
AddEventHandler("iblock", "OnAfterIBlockUpdate",		"IblockBidsGenerateHandler");
AddEventHandler("iblock", "OnIBlockDelete",				"IblockBidsGenerateNotDeleteHandler");

function IblockBidsGenerateNotDeleteHandler($ID)
{
	IblockBidsGenerateHandler(array('TYPE' => 'DELETE', 'ID' => $ID));
}

function IblockBidsGenerateHandler($arFields)
{
	global $USER;
	if (is_object($USER) && !$USER->IsAdmin()) {
		return;
	}

	CModule::IncludeModule("iblock");

	if ($arFields['TYPE'] == 'DELETE') {
		$arFilter = array("!ID" => $arFields["ID"], "ACTIVE" => "Y", "INDEX_ELEMENT" => "Y");
	} else {
		$arFilter = array("ACTIVE" => "Y", "INDEX_ELEMENT" => "Y");
	}

	$dbIb = CIBlock::GetList(array("SORT" => "ASC"), $arFilter, false);

	$arResult = array();
	$arParams = array("replace_space" => "_", "replace_other" => "_");
	/*
	 * Выбираем нужные данные по инфоблокам
	 */
	while ($arRes = $dbIb->Fetch()) {
		$dbIblockType         = CIBlockType::GetByIDLang($arRes['IBLOCK_TYPE_ID'], LANG);
		$arRes['IBLOCK_TYPE'] = $dbIblockType;
		$arRes['BIDS_NAME']   = CUtil::translit($arRes['NAME'], "ru", $arParams);;
		if (empty( $arRes['CODE'] )) {
			// поскольку имена могут меняться решено - указан код, значит можно работать с
			// инфоблоком через BIDS
			continue;
			//$arRes['BIDS_NAME'] = strtoupper(Cutil::translit($arRes['NAME'], "ru", $arParams));
		} else {
			$arRes['BIDS_NAME'] = strtoupper($arRes['CODE']);
		}
		if ($arResult[ $arRes['BIDS_NAME'] ]) {
			$arRes['BIDS_NAME']   = explode('_', $arRes['BIDS_NAME']);
			$arRes['BIDS_NAME'][] = $arRes['ID'];
			$arRes['BIDS_NAME']   = implode('_', $arRes['BIDS_NAME']);
		}
		$arResult[ $arRes['BIDS_NAME'] ] = $arRes;
	}
	/*
	 * Выбираем нужные данные по инфоблокам END
	 */

	/*
	 * создаем структуру файла
	 */
	$file = '<?
/**
 * Коды инфоблоков ' . $_SERVER['HTTP_HOST'] . '
 */
class BIDS
{';
	foreach ($arResult as $val) {
		$file .= "
	/**
	 * тип и.б. {$val['IBLOCK_TYPE']['NAME']} и.б. {$val['NAME']}
	 * @var int
	 */
	const {$val['BIDS_NAME']} = {$val['ID']};
	";
	}
	$file .= '
}
';
	/*
	 * создаем структуру файла END
	 */

	/*
	 * пишем файл
	 */
	$path = $_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/include/lib/classes/bids.php';
	//file_put_contents($path, $file, FILE_APPEND | LOCK_EX);

	$fp = fopen($path, 'w');
	flock($fp, LOCK_EX); // Блокирование файла для записи
	fwrite($fp, $file);
	flock($fp, LOCK_UN); // Снятие блокировки
	fclose($fp);

	return "Файл: bids.php <br>Путь к файлу: {$path} <br>Сформирован!";
}

