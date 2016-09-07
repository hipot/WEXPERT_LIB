<?
$FILE_NAME = $_SERVER["DOCUMENT_ROOT"] . "/input/Export.xml";
//tolog('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
$POSTDATA = file_get_contents($FILE_NAME);
if($POSTDATA==''){
	echo 'EMPTY';
} else{

	$xmlObj = simplexml_load_string($POSTDATA);
	$arXML = ObjToArray($xmlObj);
	echo '<pre>'; print_r($arXML); echo '</pre>';
};

/**
 * Конвертирует объект в массив
 * @param $obj Объект
 * @param array $skip Прпопускать индексы
 * @return array Возвращает массив
 */
function ObjToArray($obj, $skip = array())
{
    $arrData = array();

    // if input is object, convert into array
    if (is_object($obj)) {
        $obj = get_object_vars($obj);
    }

    if (is_array($obj)) {
        foreach ($obj as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = ObjToArray($value, $skip); // recursive call
            }
            if (in_array($index, $skip)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
    return $arrData;
}
?>
