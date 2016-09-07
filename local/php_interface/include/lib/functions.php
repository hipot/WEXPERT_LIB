<?
/**
 * Функции для первого уровня. Наиболее удобные в работе.
 *
 * @version 1.0
 * @author Wexpert Framework
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
 * Удобная обертка для получения в разных местах параметров в произвольных настройкаx сайта
 *
 * Важно! Сперва необходимо установить параметры через
 * Wexpert\BitrixUtils\HiBlock::installCustomSettingsHiBlock();
 *
 * @param string $paramCode
 * @return mixed
 */
function __cs($paramCode)
{
	$params = Wexpert\BitrixUtils\HiBlock::getCustomSettingsList();
	return $params[ $paramCode ];
}

/**
 * Получить сущность DataManager HL-инфоблока
 *
 * @param int|string $hiBlockName числовой или символьный код HL-инфоблока
 * @param bool $staticCache = true сохранять в локальном кеше функции возвращаемые сущности
 * @return \Bitrix\Main\Entity\DataManager
 */
function __getHl($hiBlockName, $staticCache = true)
{
	static $addedBlocks;

	$hiBlockName = trim($hiBlockName);
	if ($hiBlockName == '') {
		return false;
	}

	if (! isset($addedBlocks[$hiBlockName]) || !$staticCache) {
		if (is_numeric($hiBlockName)) {
			$addedBlocks[$hiBlockName] = Wexpert\BitrixUtils\HiBlock::getDataManagerByHiId($hiBlockName);
		} else {
			$addedBlocks[$hiBlockName] = Wexpert\BitrixUtils\HiBlock::getDataManagerByHiCode($hiBlockName);
		}
	}

	return $addedBlocks[$hiBlockName];
}

?>