<?
/**
 * Необходимые доп. функции по сайту
 */

/**
 * Дампит переменную в браузер
 *
 * @param 	mixed 	$what переменная для дампа
 * @param 	bool 	$in_browser = true выводить ли результат на экран,
 * 					либо скрыть в HTML-комментарий
 * @param 	bool 	$check_admin = true проверять на админа, если вывод прямо в браузер
 * @return 	void
 *
 * @example
 * <pre>my_print_r($ar); //выведет только для админа, для всех остальных HTML-комментарий-заглушка
 * my_print_r($ar, false); //выведет всем в виде HTML-комментария
 * my_print_r($ar, true, false); //выведет всем на экран (не рекомендуется)</pre>
 */
function my_print_r($what, $in_browser = true, $check_admin = true)
{
	if ($in_browser && $check_admin && !$GLOBALS['USER']->IsAdmin()) {
		echo "<!-- my_print_r admin need! -->";
		return;
	}

	/*$backtrace = debug_backtrace();
	echo '<h4>' . $backtrace[0]["file"] . ', ' . $backtrace[0]["line"] . '</h4>';*/

	echo ($in_browser) ? "<pre>" : "<!--";
	if ( is_array($what) )  {
		print_r($what);
	} else {
		var_dump($what);
	}
	echo ($in_browser) ? "</pre>" : "-->";
}

/**
 * Возвращает слово с правильным суффиксом
 * @param (int) $n - количество
 * @param (array|string) $str - строка 'один|два|несколько' или 'слово|слова|слов' или массив с такойже историей
 * @version 2.0
 */
function Suffix($n, $forms)
{
	if (is_string($forms)) {
		$forms = explode('|', $forms);
	}
	if ((int)$n != $n) {
		return $forms[1];
	}

	$n = abs($n) % 100;
	$n1 = $n % 10;

	if ($n > 10 && $n < 20) {
		return $forms[2];
	}
	if ($n1 > 1 && $n1 < 5) {
		return $forms[1];
	}
	if ($n1 == 1) {
		return $forms[0];
	}
	return $forms[2];
}

/**
 * Экранирует элементы массива
 *
 * @param array $array Сам массив.
 * @param bool $orig = false Возвращать ли оригинальные элементы с '~'.
 * @return array
 */
function escapeArray($array, $orig = false)
{
	$res = false;
	foreach ($array as $k => $v) {
		if (is_array($v)) {
			$o = ($orig) ? true : false;
			$res[$k] = escapeArray($v, $o);
		} else {
			$res[$k] = htmlspecialcharsEx($v);
			if ($orig) {
				$res['~'.$k] = $v;
			}
		}
	}
	return $res;
}

/**
 * @param mixed $propIdx
 * @use GetIbEventPropValue($arFields['PROPERTY_VALUES'][107])
 * @return $arFields['PROPERTY_VALUES'][107][    <b>??? - 1259|0|n0</b>    ]['VALUE']
 */
function GetIbEventPropValue($propIdx)
{
	$k = array_keys($propIdx);

	if (is_array($k) && is_array($propIdx)) {
		return $propIdx[ $k[0] ]['VALUE'];
	}

	return false;
}
?>