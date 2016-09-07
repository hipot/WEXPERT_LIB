<?

/**
 * Записывает экранированный вариант массива $arFrom = $_POST в переданную переменную по ссылке в ключ $keyRes
 *
 * @param array $arResult переменная по ссылке
 * @param array $arFrom переменная по ссылке
 * @param string $keyRes ключ массива, в который попадут экранированные данные (т.е. $arResult[$keyRes] = ~ $arFrom;)
 */
function escape_post2result(&$arResult, $arFrom = false, $keyRes = 'POST')
{
	if (!is_array($arFrom)) {
		$arFrom = $_POST;
	}
	if (!is_array($arResult)) {
		$arResult = array();
	}
	foreach ($arFrom as $k => $v) {
		if (!is_array($v)) {
			$arResult[$keyRes][$k] = htmlspecialcharsex($v);
			$arResult[$keyRes]['~' . $k] = $v;
		} else {
			foreach ($v as $kk => $vv) {
				$arResult[$keyRes][$k][$kk] = htmlspecialcharsex($vv);
				$arResult[$keyRes]['~' . $k][$kk] = $vv;
			}
		}
	}
}

/**
 * Удаляет из массива пустые элементы с пустыми значениями рекурсивно.
 *
 * @param {array} $ar
 */
function array_trim($ar)
{
	foreach ($ar as $k => $v) {
		if (is_array($v)) {
			$res[$k] = array_trim($v);
		} else {
			if (trim($v) != '') {
				$res[$k] = $v;
			}
		}
	}
	return $res;
}

/**
 *
 * @param unknown $ar
 * @return mixed
 */
function AjaxPJSO($ar)
{
	// $echo = preg_replace("#[\r\n]+#", "", PhpToJSObject($ar));
	$echo = preg_replace("#'#", "\"", PhpToJSObject($ar));
	return $echo;
}
;

/**
 * Конвертирует элементы и ключи массива из UTF8 в cp1251
 *
 * @param {array} $array Сам массив.
 * @param {bool} $orig=false Возврощать ли оригинальные элементы с '~'.
 * @return {array}
 */
function convArray($array, $orig = false)
{
	foreach ($array as $k => $v) {
		if (is_array($v)) {
			$o = ($orig) ? true : false;
			$res[$k] = convArray($v, $o);
		} else {
			$res[$k] = iconv('UTF-8', 'WINDOWS-1251', $v);
			if ($orig)
				$res['~' . $k] = $v;
		}
	}
	return $res;
}

function CreateCatFile($path)
{
	if (!file_exists($path)) { // Создание файла если его нет
		$ar_d = explode("/", $path);
		for ($i = 5; $i < count($ar_d); $i ++) {
			$pa = false;
			for ($p = 0; $p <= $i; $p ++) {
				if ($p > 0)
					$pa .= '/';
				$pa .= $ar_d[$p];
			}
			if (strpos($pa, '.') === false) {
				if (!is_dir($pa))
					mkdir($pa);
			} else {
				fclose(fopen($pa, 'x'));
			}
		}
	}
}

/**
 * Подрезает текст
 *
 * @param string $strText Текст.
 * @param int $intLen Длина выводимого текста.
 * @param string $str Что добавить в конец.
 * @return string
 */
function Truncate($strText, $intLen, $str = '...')
{
	if (strlen($strText) >= $intLen) {
		$text = substr($strText, 0, $intLen);
		$text = substr($text, 0, strrpos($text, " "));
		$text .= $str;
		return $text;
	} else {
		return $strText;
	}
}

/**
 * Добавляет $_GET параметры в строку
 *
 * @param {string} $url
 * @param {array} $add_params
 * @param {array_assoc} $options
 * @return string
 */
function urlAddParams($url, $add_params, $options = array())
{
	if (count($add_params)) {
		$params = array();
		foreach ($add_params as $name => $value) {
			if ($options['skip_empty'] && !strlen($value))
				continue;
			if ($options['encode'])
				$params[] = urlencode($name) . '=' . urlencode($value);
			else
				$params[] = $name . '=' . $value;
		}

		if (count($params)) {
			$p1 = strpos($url, '?');
			if ($p1 === false)
				$ch = '?';
			else
				$ch = '&';

			$p2 = strpos($url, '#', $p1);
			if ($p2 === false) {
				$url = $url . $ch . implode('&', $params);
			} else {
				$url = substr($url, 0, $p2) . $ch . implode('&', $params) . substr($url, $p2);
			}
		}
	}
	return $url;
}

/**
 * no work
 * Преобразует массив параметров в GET строку
 *
 * @param $prms Массив параметров
 * @param $rtrn Возвращать или выводить
 */
function strParams($prms, $rtrn = true, $nm = array())
{
	static $arrPARAMS;
	$fst = false;
	if (empty($nm)) {
		$fst = true;
		$arrPARAMS = '';
	}
	foreach ($prms as $k => $v) {
		if (is_array($v)) {
			$nm[] = $k;
			strParams($v, $rtrn, $nm);
		} else {
			if (!empty($nm)) {
				$arn = $nm;
				$an = array_shift($arn);
				foreach ($arn as $c) {
					$an .= "[$c]";
				}
				$k = $an . "[$k]";
			}
			$p = "$k=$v";
			$arrPARAMS[] = $p;
		}
	}
	if ($fst) {
		$arrPARAMS = implode('&', $arrPARAMS);
		if ($rtrn)
			return $arrPARAMS;
		else
			echo $arrPARAMS;
	}
}

/**
 * Распечатывает массив параметров в инпуты
 *
 * @param $prms Массив параметров
 * @param $rtrn Возвращать или выводить
 */
function printParams($prms, $rtrn = false, $nm = array())
{
	// echo '<pre>'; print_r($prms); echo '</pre>';
	static $arrPARAMS;
	$fst = false;
	if (empty($nm)) {
		$fst = true;
		$arrPARAMS = '';
	}
	foreach ($prms as $k => $v) {
		if (is_array($v)) {
			$nm[] = $k;
			printParams($v, $rtrn, $nm);
			array_pop($nm);
		} else {
			if (!empty($nm)) {
				$arn = $nm;
				$an = array_shift($arn);
				foreach ($arn as $c) {
					$an .= "[$c]";
				}
				$k = $an . "[$k]";
			}
			$p = "<input type='hidden' name='$k' value='$v' />\n";
			$arrPARAMS .= $p;
		}
	}
	if ($fst)
		if ($rtrn)
			return $arrPARAMS;
		else
			echo $arrPARAMS;
}

/**
 * Конвертирует массив параметров в строку GET формата
 *
 * @static
 *
 * @param $ar Массив параметров
 * @param bool $nm Имя надмассива
 * @return string Строка в форме гет запроса без знака ?
 */
function toGetString($ar, $nm = false)
{
	foreach ($ar as $k => $v) {
		if (is_array($v)) {
			$ret[] = toGetString($v, $k);
		} elseif ($v !== false) {
			if ($nm) {
				if (is_numeric($k)) {
					$ret[] = $nm . '[]=' . $v;
				} else {
					$ret[] = $nm . '[' . $k . ']=' . $v;
				}
			} else {
				$ret[] = "$k=$v";
			}
		}
	}
	return implode('&', $ret);
}

/**
 * Заменяет переменные в тексте (Как Битрикс в почтовых шаблонах - #VARIABLE#)
 *
 * @param $txt Текст в котором необходимо произвести замену #VAR#
 * @param $arr Массив замен array('MAIL'=>'mail@mail.ru')
 * @param $r Строка замены для пустых совпадений (Если в массиве замен не указана замена на найденную переменную)
 * @return void Текст письма с заменами
 */
function varsReplacer($txt, $arr, $r = '')
{
	preg_match_all('/#([A-Z_]+)#/', $txt, $m);
	foreach ($m[1] as $v) {
		$search[] = '#' . $v . '#';
		$replace[] = isset($arr[$v]) ? $arr[$v] : $r;
	}
	return str_replace($search, $replace, $txt);
}

/**
 * Получить курсы валют из ЦБ-России (www.cbr.ru)
 *
 * @param int $DATE_RATE = time() дата юникс, для получения курсов (O)
 * @return array Массив вида:<br />
 *         ..<br />
 *         [USD] => Array([NOMINAL] => 1, [RATE] => 29.2889, [NAME] => Доллар США)<br />
 *         ..<br />
 */
function GetQueryCurrencyRatesCentralBank($DATE_RATE = false)
{
	global $APPLICATION, $DB;

	$arRates = array();

	if ($DATE_RATE === false) {
		$DATE_RATE = time();
	}

	$QUERY_STR = "date_req=" . date('d.m.Y', $DATE_RATE);
	$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);
	if (strlen($strQueryText) <= 0) {

		return false;
	} else {
		require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/xml.php");

		$charset = "windows-1251";
		if (preg_match("/<" . "\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\?" . ">/i", $strQueryText, $matches)) {
			$charset = Trim($matches[1]);
		}
		$strQueryText = preg_replace("#<!DOCTYPE[^>]+?>#i", "", $strQueryText);
		$strQueryText = preg_replace("#<" . "\\?XML[^>]+?\\?" . ">#i", "", $strQueryText);
		$strQueryText = $APPLICATION->ConvertCharset($strQueryText, $charset, SITE_CHARSET);

		$objXML = new CDataXML();
		$res = $objXML->LoadString($strQueryText);
		if ($res !== false) {
			$arData = $objXML->GetArray();
		} else {
			$arData = false;
		}

		if (!is_array($arData) || count($arData["ValCurs"]["#"]["Valute"]) <= 0) {
			return false;
		}

		for ($j1 = 0; $j1 < count($arData["ValCurs"]["#"]["Valute"]); $j1 ++) {

			$name = $arData["ValCurs"]["#"]["Valute"][$j1]["#"]['Name'][0]["#"];
			$rate_cur = $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"];

			$RATE_CNT = IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]);
			$RATE = str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"]);
			$RATE = DoubleVal($RATE);

			$arRates[$rate_cur] = array(
				'NOMINAL' => $RATE_CNT,
				'RATE' => $RATE,
				'NAME' => $name
			);
		}

		return $arRates;
	}

	return false;
}

/**
 * Сохраняет массив $array, с именем $name, в файл $path
 *
 * @param array $array Массив для сохранения
 * @param string $name Имя массива
 * @param $path Путь к файлу
 * @return bool
 */
function SaveDump($array, $name = 'array', $path)
{
	if (empty($array) || strlen($array) <= 0) {
		echo 'Нечего писать!';
		return false;
	}
	$str = "<?\r\n\$" . $name . " = " . var_export($array, true) . "\r\n?>";
	$fp = fopen($path, 'w+');
	flock($fp, LOCK_EX);
	rewind($fp);
	$fw = fwrite($fp, $str);
	fclose($fp);
	if ($fw)
		return true;
	else
		return false;
}

/*
 * Сохраняет $str в файл log.txt в текущей папке где вызвана функция
 */
function tolog($str)
{
	$fp = fopen(dirname(__FILE__) . '/log.txt', 'a');
	flock($fp, LOCK_EX);
	rewind($fp);
	$fw = fwrite($fp, date('d.m.Y H:i:s', time()) . " " . $str . "\r\n");
	fclose($fp);
	if ($fw)
		return true;
	else
		return false;
}
;

/**
 * Генерирует пароль.
 *
 * @param integer $number Число символов в пароле
 * @return string
 */
function passwordGen($number)
{
	$arr = array(
		'a',
		'b',
		'c',
		'd',
		'e',
		'f',
		'g',
		'h',
		'i',
		'j',
		'k',
		'l',
		'm',
		'n',
		'o',
		'p',
		'r',
		's',
		't',
		'u',
		'v',
		'x',
		'y',
		'z',
		'A',
		'B',
		'C',
		'D',
		'E',
		'F',
		'G',
		'H',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'R',
		'S',
		'T',
		'U',
		'V',
		'X',
		'Y',
		'Z',
		'1',
		'2',
		'3',
		'4',
		'5',
		'6',
		'7',
		'8',
		'9',
		'0'
	);
	$pass = "";
	for ($i = 0; $i < $number; $i ++) {
		$index = rand(0, count($arr) - 1);
		$pass .= $arr[$index];
	}
	return $pass;
}

/**
 * Универсальный парсер CSV, все кроме самого файла определяет автоматом
 *
 * @param $file_name Имя файла - file($file_name).
 * @return mixed
 */
function csv_parser($file_name)
{
	$csv_lines = file($file_name);
	if (is_array($csv_lines)) {
		// разбор csv
		$cnt = count($csv_lines);
		for ($i = 0; $i < $cnt; $i ++) {
			$line = $csv_lines[$i];
			$line = trim($line);
			// указатель на то, что через цикл проходит первый символ столбца
			$first_char = true;
			// номер столбца
			$col_num = 0;
			$length = strlen($line);
			for ($b = 0; $b < $length; $b ++) {
				// переменная $skip_char определяет обрабатывать ли данный символ
				if ($skip_char != true) {
					// определяет обрабатывать/не обрабатывать строку
					// /print $line[$b];
					$process = true;
					// определяем маркер окончания столбца по первому символу
					if ($first_char == true) {
						if ($line[$b] == '"') {
							$terminator = '";';
							$process = false;
						} else
							$terminator = ';';
						$first_char = false;
					}

					// просматриваем парные кавычки, опредляем их природу
					if ($line[$b] == '"') {
						$next_char = $line[$b + 1];
						// удвоенные кавычки
						if ($next_char == '"')
							$skip_char = true;
							// маркер конца столбца
						elseif ($next_char == ';') {
							if ($terminator == '";') {
								$first_char = true;
								$process = false;
								$skip_char = true;
							}
						}
					}

					// определяем природу точки с запятой
					if ($process == true) {
						if ($line[$b] == ';') {
							if ($terminator == ';') {

								$first_char = true;
								$process = false;
							}
						}
					}

					if ($process == true)
						$column .= $line[$b];

					if ($b == ($length - 1)) {
						$first_char = true;
					}

					if ($first_char == true) {

						$values[$i][$col_num] = $column;
						$column = '';
						$col_num ++;
					}
				} else
					$skip_char = false;
			}
		}
	}
	return $values;
}

/**
 * Возвращает массив таймстампов от $from до $to каждые $add
 *
 * @param $from Время в формате сайта
 * @param $to Время в формате сайта
 * @param $add Массив прибавления, как для функции AddToTimeStamp
 * @return array|bool
 */
function getIntervals($from, $to, $add)
{
	if (!is_array($add) || empty($add))
		return false;
	if (gettype($from) == 'string') {
		$from = MakeTimeStamp($from);
	}
	if (gettype($to) == 'string') {
		$to = MakeTimeStamp($to);
	}
	$me = $from;
	while ($me <= $to) {
		$arRes[] = $me;
		$me = AddToTimeStamp($add, $me);
	}
	return $arRes;
}

/**
 * Возвращает текущий календарный месяц
 *
 * @param int $m Месяц 1-12. Текущий
 * @param int $y Год YYYY. Текущий
 * @return array Массив дней месяца, [timestamp] => array('D'=>getdate(timestamp))
 */
function getCalendarMonth($m = false, $y = false)
{
	$days = 7 * 6;
	$dl = (3600 * 24);
	if ($m <= 0)
		$m = date('n');
	if ($y <= 0)
		$y = date('Y');
	$fr = mktime(0, 0, 0, $m, 1, $y);
	$w = date('w', $fr);
	if ($w != 1) {
		$w = ($w == 0) ? 7 : $w;
		$fr = mktime(0, 0, 0, $m, 1 - ($w - 1), $y);
	}
	$to = $fr + ($days * $dl) - $dl;
	for ($i = $fr; $i <= $to; $i += $dl) {
		$ar[$i] = array(
			'D' => getdate($i)
		);
	}
	return $ar;
}

/**
 * Округляет таймстамп до дня
 *
 * @param timestamp $ts Округляемый таймстамп
 * @return timestamp
 */
function dayRnd($ts)
{
	$ar = getdate($ts);
	return mktime(0, 0, 0, $ar['mon'], $ar['mday'], $ar['year']);
}

/**
 * Возвращает массив с днями текущей недели, относительно введенной даты.
 *
 * @param {str} $d Номер дня. Или Timestamp даты.
 * @param {int} $m=false Номер месяца.
 * @param {int} $y=false Номер года.
 * @param {bool} $as=false Выводить номерной или ассоциативный массив.
 * @return {array} $ar Массив с днями текущей недели, относительно введенной даты
 */
function getWeek($d, $m = false, $y = false, $as = false)
{
	if ((int) $d > 31) {
		$ts = $d;
		$gd = getdate($ts);
		$d = $gd['mday'];
		$m = $gd['mon'];
		$y = $gd['year'];
		$dw = $gd['wday'];
	} elseif ((int) $m != false && (int) $y != false) {
		$d = (int) $d;
		$m = (int) $m;
		$y = (int) $y;
		$ts = mktime(0, 0, 0, $m, $d, $y);
		$dw = date('w', $ts);
	} else {
		return false;
	}
	$dw = ($dw == 0) ? 7 : $dw;
	$f = $d - $dw + 1;
	for ($i = 0; $i < 7; $i ++) {
		if ($as) {
			$ar[mktime(0, 0, 0, $m, $f + $i, $y)] = date('d.m.Y', mktime(0, 0, 0, $m, $f + $i, $y));
		} else {
			$ar[$i + 1] = mktime(0, 0, 0, $m, $f + $i, $y);
		}
	}
	return $ar;
}
;

/**
 * Транслитирирует строку
 *
 *
 * @param $string Строка
 * @return string Строка
 */
function rus2translit($string)
{
	$converter = array(
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'e',
		'ж' => 'zh',
		'з' => 'z',
		'и' => 'i',
		'й' => 'y',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'h',
		'ц' => 'c',
		'ч' => 'ch',
		'ш' => 'sh',
		'щ' => 'sch',
		'ь' => 'i',
		'ы' => 'y',
		'ъ' => 'i',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',

		'А' => 'A',
		'Б' => 'B',
		'В' => 'V',
		'Г' => 'G',
		'Д' => 'D',
		'Е' => 'E',
		'Ё' => 'E',
		'Ж' => 'Zh',
		'З' => 'Z',
		'И' => 'I',
		'Й' => 'Y',
		'К' => 'K',
		'Л' => 'L',
		'М' => 'M',
		'Н' => 'N',
		'О' => 'O',
		'П' => 'P',
		'Р' => 'R',
		'С' => 'S',
		'Т' => 'T',
		'У' => 'U',
		'Ф' => 'F',
		'Х' => 'H',
		'Ц' => 'C',
		'Ч' => 'Ch',
		'Ш' => 'Sh',
		'Щ' => 'Sch',
		'Ь' => 'i',
		'Ы' => 'Y',
		'Ъ' => 'i',
		'Э' => 'E',
		'Ю' => 'Yu',
		'Я' => 'Ya',

		' ' => '_',
		'.' => '-',
		'&' => '_',
		';' => '_'
	);
	return strtr($string, $converter);
}

/**
 * Ввыодит период даты от $frt до $tt
 *
 * @param $frt Таймстамп от
 * @param $tt Таймстамп до
 * @return string
 */
function formaPeriod($frt, $tt)
{
	$arf = getdate($frt);
	$art = getdate($tt);
	$fromon = ToLower(GetMessage('MONTH_' . $arf['mon'] . '_S'));
	$tomon = ToLower(GetMessage('MONTH_' . $art['mon'] . '_S'));
	$day2 = $arf['mday'];
	$mon2 = $fromon;
	$year2 = $arf['year'];
	if ($arf['mday'] != $art['mday']) {
		$das = true;
		$day1 = date('d', $frt);
		$day2 = date('d', $tt);
	}
	if ($arf['mon'] != $art['mon']) {
		$mos = true;
		$mon1 = $fromon;
		$mon2 = $tomon;
	}
	if ($arf['year'] != $art['year']) {
		$yes = true;
		$year1 = date('Y', $frt);
		$year2 = date('Y', $tt);
	}
	if ($yes) {
		return "$day1 $mon1 $year1 - $day2 $mon2 $year2";
	} elseif ($mos) {
		return "$day1 $mon1 - $day2 $mon2 $year2";
	} elseif ($das) {
		return "$day1 - $day2 $mon2 $year2";
	} else {
		return "$day2 $mon2 $year2";
	}
}

if (!function_exists('transformImagesInHtml')) {

	/**
	 * трансформим картинки в html до требуемой ширины
	 *
	 * @param string $html
	 * @param int $maxImgWidth = 750
	 * @return string|mixed
	 */
	function transformImagesInHtml($html, $maxImgWidth = 750, $linkToOrigClass = ' class="lightbox" target="_blank" ')
	{
		$GLOBALS['transformImagesInHtml_maxImgWidth'] = $maxImgWidth;
		$GLOBALS['transformImagesInHtml_linkToOrigClass'] = $linkToOrigClass;

		if (!function_exists('matcherTransformImg')) {

			function matcherTransformImg($matches)
			{
				$transformSrc = $matches[2];
				$origSrc = $matches[2];

				$transformSrc = CImg::Resize($transformSrc, $GLOBALS['transformImagesInHtml_maxImgWidth'], false, CImg::M_FULL);

				return "<a href=\"" . $origSrc . "\" " . $GLOBALS['transformImagesInHtml_linkToOrigClass'] . "><img src=\"" . $transformSrc . "\" alt=\"\" border=\"0\" /></a>";
			}
		}

		$htmlEx = preg_replace('#width\s*=(["\'])?([0-9]+)(["\'])?#is', '', $html);
		$htmlEx = preg_replace('#height\s*=(["\'])?([0-9]+)(["\'])?#is', '', $htmlEx);
		$htmlEx = preg_replace_callback('#<img(.*?)src=["\']?([^"\']+)["\']?(.*?)>#is', 'matcherTransformImg', $htmlEx);

		unset($GLOBALS['transformImagesInHtml_maxImgWidth'], $GLOBALS['transformImagesInHtml_linkToOrigClass']);

		return $htmlEx;
	}
}

/**
 * Возвращает размер удаленного файла
 *
 * @param $path Путь к удаленному файлу
 */
function remote_filesize($path)
{
	preg_match('#(ht|f)tp(s)?://(?P<host>[a-zA-Z-_]+.[a-zA-Z]{2,4})(?P<name>/[\S]+)#', $path, $m);
	$x = 0;
	$stop = false;
	$fp = fsockopen($m['host'], 80, &$errno, &$errstr, 30);
	fputs($fp, "HEAD $m[name] HTTP/1.0\nHOST: $m[host]\n\n");
	while (!feof($fp) && !$stop) {
		$y = fgets($fp, 2048);
		if ($y == "\r\n") {
			$stop = true;
		}
		$x .= $y;
	}
	fclose($fp);

	if (preg_match("#Content-Length: ([0-9]+)#", $x, $size)) {
		return $size[1];
	} else {
		return false;
	}
}

/**
 * Возвразает представление размера в строчном виде **Кб, **Мб, **Гбб **байт
 *
 * @param $bytes Размер файла в байтах
 * @param int $dec Чисел после запятой
 * @return string
 */
function size_name($bytes, $dec = 2)
{
	$gb = 1073741824;
	$mb = 1048576;
	$kb = 1024;
	if ($bytes > $gb) {
		$bytes = $bytes / $gb;
		$sn = ' Гб';
	} elseif ($bytes > $mb) {
		$bytes = $bytes / $mb;
		$sn = ' Мб';
	} elseif ($bytes > $kb) {
		$bytes = $bytes / $kb;
		$sn = ' Кб';
	} else {
	}
	return number_format($bytes, $dec) . $sn;
}

/**
 * Получить точку на карте по адрессу, используя гугл
 *
 * @param string $addres адресс на карте
 *
 * @see http://code.google.com/intl/ru/apis/maps/documentation/geocoding/index.html#GeocodingRequests
 *
 * @return false|array
 */
function _GetPointByAddressGoogle($addres)
{
	if (trim($addres) == '') {
		return false;
	}

	$query = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($addres) . '&sensor=false&language=ru';

	$oResult = json_decode(file_get_contents($query));
	$pos = $oResult->results[0]->geometry->location;
	if ($pos) {
		return array(
			$pos->lat,
			$pos->lng
		);
	}

	return false;
}

/**
 * Находит расстояние между точками $arTransmitter и $arReseiver
 *
 * @param array $arTransmitter - широта и долгота передатчика в градусах (для северной широты и восточной долготы со знаком плюс, для южной широты и западной долготы со знаком минус)
 * @param array $arReseiver - широта и долгота приёмника в градусах (для северной широты и восточной долготы со знаком плюс, для южной широты и западной долготы со знаком минус)
 * @return int|false Расстояние в метрах между точками
 */
function GetDistance($arTransmitter, $arReseiver)
{
	$arTransmitter[0] = (float) $arTransmitter[0];
	$arTransmitter[1] = (float) $arTransmitter[1];
	$arReseiver[0] = (float) $arReseiver[0];
	$arReseiver[1] = (float) $arReseiver[1];
	if (is_array($arTransmitter) && is_array($arReseiver)) {
		$calcDistance = 111200 * sqrt(pow(($arTransmitter[0] - $arReseiver[0]), 2) + pow(($arTransmitter[1] - $arReseiver[1]) * cos(M_PI * $arTransmitter[0] / 180), 2));

		return $calcDistance;
	}
	return false;
}

/**
 * Добавляет фотки из ссылок, во множественное свойство типа файл
 *
 * @param $ID ID
 * @param $IBLOCK_ID ID
 * @param $arFiles Массив array('link.png'=>'description')
 * @param $prop_code Код
 *
 * @return bool|string
 */
function AddMultipleFileValue($ID, $IBLOCK_ID, $arFiles, $prop_code)
{
	CModule::IncludeModule('iblock');
	$result = true;
	$rsPr = CIBlockElement::GetProperty($IBLOCK_ID, $ID, array(
		'sort',
		'asc'
	), array(
		'CODE' => $prop_code,
		'EMPTY' => 'N'
	));
	$k = -1;
	while ($arPr = $rsPr->GetNext()) {
		$k ++;
		$far = CFile::GetFileArray($arPr['VALUE']);
		$far = CFile::MakeFileArray($far['SRC']);
		// $far['name']
		$ar[] = array(
			'VALUE' => $far,
			'DESCRIPTION' => $arPr['DESCRIPTION']
		);
	}

	foreach ($arFiles as $l => $d) {
		$k ++;
		$art = false;
		$gn = 0;
		while (!$art || $art['type'] == 'unknown' && $gn < 10) {
			$gn ++;
			$art = CFile::MakeFileArray($l);
		}
		if ($art['tmp_name']) {
			$ar[] = array(
				'VALUE' => $art,
				'DESCRIPTION' => $d
			);
		} else {
			$result = 'partial';
		}
	}

	CIBlockElement::SetPropertyValuesEx($ID, $IBLOCK_ID, array(
		$prop_code => $ar
	));

	return $result;
}

/**
 * Выводит строку для инпута
 * name="input_name" value="input_value"
 *
 * @param $key Ключ. Использовать точечную нотацию
 * @param null $array Массив. По умолчанию $_REQUEST
 */
function input_val_name($key, $array = null)
{
	$array = $array ?  : $_REQUEST;
	$kar = explode('.', $key);
	echo 'name="' . array_shift($kar) . '[' . inplode('][', $kar) . ']' . '" value="' . array_get($array, $key) . '"';
}

/**
 * Возвращает элемент подмассива используя точечную нотацию item.sub_item
 *
 * @param array $array Массив
 * @param string $key Ключ
 * @param mixed $default Значение "по умалчанию"
 *
 * @return mixed
 */
function array_get($array, $key, $default = null)
{
	if (is_null($key))
		return $array;

	if (isset($array[$key]))
		return $array[$key];

	foreach (explode('.', $key) as $segment) {
		if (!is_array($array) or !array_key_exists($segment, $array)) {
			return value($default);
		}

		$array = $array[$segment];
	}

	return $array;
}

/**
 * Функция определения кодировки строки
 * Удобно для автоматического определения кодировки csv-файла
 *
 * Почему не mb_detect_encoding()? Если кратко — он не работает.
 *
 * @param string $string строка в неизвестной кодировке
 * @param number $pattern_size = 50
 *        если строка больше этого размера, то определение кодировки будет
 *        производиться по шаблону из $pattern_size символов, взятых из середины
 *        переданной строки. Это сделано для увеличения производительности на больших текстах.
 * @return string 'cp1251' 'utf-8' 'ascii' '855' 'KOI8R' 'ISO-IR-111' 'CP866' 'KOI8U'
 *
 * @see http://habrahabr.ru/post/107945/
 * @see http://forum.dklab.ru/viewtopic.php?t=37833
 * @see http://forum.dklab.ru/viewtopic.php?t=37830
 */
function detect_encoding($string, $pattern_size = 50)
{
	$list = array(
		'cp1251',
		'utf-8',
		'ascii',
		'855',
		'KOI8R',
		'ISO-IR-111',
		'CP866',
		'KOI8U'
	);
	$c = strlen($string);
	if ($c > $pattern_size) {
		$string = substr($string, floor(($c - $pattern_size) / 2), $pattern_size);
		$c = $pattern_size;
	}

	$reg1 = '/(\xE0|\xE5|\xE8|\xEE|\xF3|\xFB|\xFD|\xFE|\xFF)/i';
	$reg2 = '/(\xE1|\xE2|\xE3|\xE4|\xE6|\xE7|\xE9|\xEA|\xEB|\xEC|\xED|\xEF|\xF0|\xF1|\xF2|\xF4|\xF5|\xF6|\xF7|\xF8|\xF9|\xFA|\xFC)/i';

	$mk = 10000;
	$enc = 'ascii';
	foreach ($list as $item) {
		$sample1 = @iconv($item, 'cp1251', $string);
		$gl = @preg_match_all($reg1, $sample1, $arr);
		$sl = @preg_match_all($reg2, $sample1, $arr);
		if (!$gl || !$sl) {
			continue;
		}
		$k = abs(3 - ($sl / $gl));
		$k += $c - $gl - $sl;
		if ($k < $mk) {
			$enc = $item;
			$mk = $k;
		}
	}
	return $enc;
}

// в PHP 5.5 такая фукнция будет по дефолту
if (!function_exists('array_column')) {

	/**
	 * Returns the values from a single column of the input array, identified by the columnKey.
	 *
	 * Optionally, you may provide an indexKey to index the values in the returned array by the values from the
	 * indexKey column in the input array.
	 *
	 * @param array[] $input A multi-dimensional array (record set) from which to pull a column of values.
	 * @param int|string $columnKey The column of values to return. This value may be the
	 *        integer key of the column you wish to retrieve, or it may be the string key name for an associative array.
	 * @param int|string $indexKey The column to use as the index/keys for the returned array.
	 *        This value may be the integer key of the column, or it may be the string key name.
	 *
	 * @return mixed[]
	 *
	 * @see http://habrahabr.ru/post/173943/
	 */
	function array_column($input, $columnKey, $indexKey = null)
	{
		if (!is_array($input)) {
			return false;
		}
		if ($indexKey === null) {
			foreach ($input as $i => &$in) {
				if (is_array($in) && isset($in[$columnKey])) {
					$in = $in[$columnKey];
				} else {
					unset($input[$i]);
				}
			}
		} else {
			$result = array();
			foreach ($input as $i => $in) {
				if (is_array($in) && isset($in[$columnKey])) {
					if (isset($in[$indexKey])) {
						$result[$in[$indexKey]] = $in[$columnKey];
					} else {
						$result[] = $in[$columnKey];
					}
					unset($input[$i]);
				}
			}
			$input = &$result;
		}
		return $input;
	}
}

/**
 * Выполняет команду в OS в фоне и без получения ответа
 *
 * @see exec()
 * @return NULL
 */
function execInBackground($cmd)
{
	if (substr(php_uname(), 0, 7) == "Windows") {
		pclose(popen("start /B " . $cmd, "r"));
	} else {
		exec($cmd . " > /dev/null &");
	}
}

/**
 * трансформим картинки в html до требуемой ширины.
 * Каждая картинка становиться ссылкой на оригинал.
 *
 * @param string $html входной html с большими картинками
 * @param int $maxImgWidth = 750 макс. ширина картинок
 * @param string $linkToOrigClass = ' class="lightbox" target="_blank" ' класс у ссылки, для лайтбокса
 * @return string|mixed
 */
function transformImagesInHtml($html, $maxImgWidth = 750, $linkToOrigClass = ' class="lightbox" target="_blank" ')
{
	$GLOBALS['transformImagesInHtml_maxImgWidth'] = $maxImgWidth;
	$GLOBALS['transformImagesInHtml_linkToOrigClass'] = $linkToOrigClass;

	if (!function_exists('matcherTransformImg')) {

		function matcherTransformImg($matches)
		{
			$transformSrc = $matches[2];
			$origSrc = $matches[2];

			$transformSrc = CImg::Resize($transformSrc, $GLOBALS['transformImagesInHtml_maxImgWidth'], false, CImg::M_FULL);

			return "<a href=\"" . $origSrc . "\" " . $GLOBALS['transformImagesInHtml_linkToOrigClass'] . "><img src=\"" . $transformSrc . "\" alt=\"\" border=\"0\" /></a>";
		}
	}

	$htmlEx = preg_replace('#width\s*=(["\'])?([0-9]+)(["\'])?#is', '', $html);
	$htmlEx = preg_replace('#height\s*=(["\'])?([0-9]+)(["\'])?#is', '', $htmlEx);
	$htmlEx = preg_replace_callback('#<img(.*?)src=["\']?([^"\']+)["\']?(.*?)>#is', 'matcherTransformImg', $htmlEx);

	unset($GLOBALS['transformImagesInHtml_maxImgWidth'], $GLOBALS['transformImagesInHtml_linkToOrigClass']);

	return $htmlEx;
}

/**
 * Пересекаются ли времена заданные unix-таймштампами.
 *
 * Решение сводится к проверке границ одного отрезка на принадлежность другому отрезку
 * и наоборот. Достаточно попадания одной точки.
 *
 * @param int $left1_ts
 * @param int $right1_ts
 * @param int $left2_ts
 * @param int $right2_ts
 * @return boolean
 */
function IsIntervalsTsIncl($left1_ts, $right1_ts, $left2_ts, $right2_ts)
{
	// echo $left1_ts . ' ' . $right1_ts . ' ' . $left2_ts . ' ' . $right2_ts . '<br />';
	if ($left1_ts <= $left2_ts) {
		return $right1_ts >= $left2_ts;
	} else {
		return $left1_ts <= $right2_ts;
	}
}

/**
 * Определить OS по $_SERVER['HTTP_USER_AGENT']
 *
 * @param string $userAgent = $_SERVER['HTTP_USER_AGENT']
 * @return string
 */
function getOS($userAgent = '')
{
	if (trim($userAgent) == '') {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
	}

	$oses = array(
		// Mircrosoft Windows Operating Systems
		'Windows 3.11' => '(Win16)',
		'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
		'Windows 98' => '(Windows 98)|(Win98)',
		'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
		'Windows 2000 Service Pack 1' => '(Windows NT 5.01)',
		'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
		'Windows Server 2003' => '(Windows NT 5.2)',
		'Windows Vista' => '(Windows NT 6.0)|(Windows Vista)',
		'Windows 7' => '(Windows NT 6.1)|(Windows 7)',
		'Windows 8' => '(Windows NT 6.2)|(Windows 8)',
		'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
		'Windows ME' => '(Windows ME)|(Windows 98; Win 9x 4.90 )',
		'Windows CE' => '(Windows CE)',
		// UNIX Like Operating Systems
		'Mac OS X Kodiak (beta)' => '(Mac OS X beta)',
		'Mac OS X Cheetah' => '(Mac OS X 10.0)',
		'Mac OS X Puma' => '(Mac OS X 10.1)',
		'Mac OS X Jaguar' => '(Mac OS X 10.2)',
		'Mac OS X Panther' => '(Mac OS X 10.3)',
		'Mac OS X Tiger' => '(Mac OS X 10.4)',
		'Mac OS X Leopard' => '(Mac OS X 10.5)',
		'Mac OS X Snow Leopard' => '(Mac OS X 10.6)',
		'Mac OS X Lion' => '(Mac OS X 10.7)',
		'Mac OS X' => '(Mac OS X)',
		'Mac OS' => '(Mac_PowerPC)|(PowerPC)|(Macintosh)',
		'Open BSD' => '(OpenBSD)',
		'SunOS' => '(SunOS)',
		'Solaris 11' => '(Solaris/11)|(Solaris11)',
		'Solaris 10' => '((Solaris/10)|(Solaris10))',
		'Solaris 9' => '((Solaris/9)|(Solaris9))',
		'CentOS' => '(CentOS)',
		'QNX' => '(QNX)',
		// Kernels
		'UNIX' => '(UNIX)',
		// Linux Operating Systems
		'Ubuntu 12.10' => '(Ubuntu/12.10)|(Ubuntu 12.10)',
		'Ubuntu 12.04 LTS' => '(Ubuntu/12.04)|(Ubuntu 12.04)',
		'Ubuntu 11.10' => '(Ubuntu/11.10)|(Ubuntu 11.10)',
		'Ubuntu 11.04' => '(Ubuntu/11.04)|(Ubuntu 11.04)',
		'Ubuntu 10.10' => '(Ubuntu/10.10)|(Ubuntu 10.10)',
		'Ubuntu 10.04 LTS' => '(Ubuntu/10.04)|(Ubuntu 10.04)',
		'Ubuntu 9.10' => '(Ubuntu/9.10)|(Ubuntu 9.10)',
		'Ubuntu 9.04' => '(Ubuntu/9.04)|(Ubuntu 9.04)',
		'Ubuntu 8.10' => '(Ubuntu/8.10)|(Ubuntu 8.10)',
		'Ubuntu 8.04 LTS' => '(Ubuntu/8.04)|(Ubuntu 8.04)',
		'Ubuntu 6.06 LTS' => '(Ubuntu/6.06)|(Ubuntu 6.06)',
		'Red Hat Linux' => '(Red Hat)',
		'Red Hat Enterprise Linux' => '(Red Hat Enterprise)',
		'Fedora 17' => '(Fedora/17)|(Fedora 17)',
		'Fedora 16' => '(Fedora/16)|(Fedora 16)',
		'Fedora 15' => '(Fedora/15)|(Fedora 15)',
		'Fedora 14' => '(Fedora/14)|(Fedora 14)',
		'Chromium OS' => '(ChromiumOS)',
		'Google Chrome OS' => '(ChromeOS)',
		// Kernel
		'Linux' => '(Linux)|(X11)',
		// BSD Operating Systems
		'OpenBSD' => '(OpenBSD)',
		'FreeBSD' => '(FreeBSD)',
		'NetBSD' => '(NetBSD)',
		// Mobile Devices
		'Andriod' => '(Android)',
		'iPod' => '(iPod)',
		'iPhone' => '(iPhone)',
		'iPad' => '(iPad)',
		// DEC Operating Systems
		'OS/8' => '(OS/8)|(OS8)',
		'Older DEC OS' => '(DEC)|(RSTS)|(RSTS/E)',
		'WPS-8' => '(WPS-8)|(WPS8)',
		// BeOS Like Operating Systems
		'BeOS' => '(BeOS)|(BeOS r5)',
		'BeIA' => '(BeIA)',
		// OS/2 Operating Systems
		'OS/2 2.0' => '(OS/220)|(OS/2 2.0)',
		'OS/2' => '(OS/2)|(OS2)',
		// Search engines
		'Search engine or robot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(msnbot)|(Ask Jeeves/Teoma)|(ia_archiver)'
	);

	foreach ($oses as $os => $pattern) {
		if (preg_match("/$pattern/i", $userAgent)) {
			return $os;
		}
	}
	return 'Unknown';
}

function isMac()
{
	return preg_match('#(Mac)|(iPod)|(iPhone)|(iPad)#is', getOS());
}


/**
 * Получить операционную систему и версию браузера
 *
 * @return array[userAgent|name|version|platform|pattern]
 *
 * @example <pre>// now try it
 * $ua=getBrowser();
 * $yourbrowser= "Your browser: " . $ua['name'] . " " . $ua['version'] . " on " .$ua['platform'] . " reports: <br >" . $ua['userAgent'];
 * print_r($yourbrowser);</pre>
 */
function getBrowser()
{
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version = "";

	// First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	} elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	} elseif (preg_match('/Firefox/i', $u_agent)) {
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	} elseif (preg_match('/Chrome/i', $u_agent)) {
		$bname = 'Google Chrome';
		$ub = "Chrome";
	} elseif (preg_match('/Safari/i', $u_agent)) {
		$bname = 'Apple Safari';
		$ub = "Safari";
	} elseif (preg_match('/Opera/i', $u_agent)) {
		$bname = 'Opera';
		$ub = "Opera";
	} elseif (preg_match('/Netscape/i', $u_agent)) {
		$bname = 'Netscape';
		$ub = "Netscape";
	}

	// finally get the correct version number
	$known = array(
		'Version',
		$ub,
		'other'
	);
	$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		// we will have two since we are not using 'other' argument yet
		// see if version is before or after the name
		if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
			$version = $matches['version'][0];
		} else {
			$version = $matches['version'][1];
		}
	} else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if ($version == null || $version == "") {
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name' => $bname,
		'version' => $version,
		'platform' => $platform,
		'pattern' => $pattern
	);
}



/**
 * Переводим текст до 10Кб через яндекс-переводчик
 * https://tech.yandex.ru/translate/doc/dg/reference/translate-docpage/#JSON
 *
 * Требуется указать ключ в настройках главного модуля
 *
 * @param string $text
 * @param string $to_lang = 'en' в какой язык нужно перевести
 * @return boolean|string
 */
function TranslateYaString($text, $to_lang = 'en')
{
	$text = trim($text);

	$data = array(
		'key' 	=> COption::GetOptionString('main', 'translate_key_yandex'),
		'text'	=> urlencode($text),
		'lang'	=> $to_lang,
	);
	$q = '';
	foreach ($data as $k => $v) {
		$q .= $k . '=' . $v . '&';
	}
	$q = trim($q, '& ');

	$json = QueryGetData('translate.yandex.net', 443, '/api/v1.5/tr.json/translate', $q, $errno, $errstr, "GET", "tls://");
	$json = json_decode($json);

	if (is_object($json) && isset($json->text) && isset($json->code) && $json->code == 200) {
		return $json->text[0];
	} else {
		return false;
	}
}


?>