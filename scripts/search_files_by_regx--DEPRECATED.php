<?
/**
 * Поиск подстрок в файлах
 *
 *
 * @author wexpert
 * @version 0.002 beta
 *
 */
set_time_limit(0);


header('Content-Type: text/html; charset=UTF-8');

$regExp = '~wexpert:~i';
$badUnderCat = array(
	'lang',
	'lang/ru',
	'lang/en',
	'lang/de',
);

$badCat = array(
	'xml_exchange',
	'bitrix/stack_cache',
	'bitrix/js',
	'bitrix/help',
	'bitrix/modules',
	'bitrix/tools',
	'bitrix/managed_cache',
	'bitrix/image_uploade',
	'bitrix/images',
	'bitrix/wizards',
	'bitrix/gadgets',
	'bitrix/themes',
	'bitrix/cache',
	'bitrix/components/bitrix',
	'bitrix/tmp',
	'upload',
);


$isOk = false;

if ($_POST) {
	$searchCat = sampleGetArray($_POST['searchCat']);
	$badCat = sampleGetArray($_POST['badCat']);
	$badUnderCat = sampleGetArray($_POST['badUnderCat']);

	$regExp = trim($_POST['regExp']);
	$beginTime = time();
	$flagSearchEnd = false;
	$isOk = true;

	$searchStr = trim($_POST['searchStr']);
}

?>
<style>
	body, table {font-family:Verdana; font-size:11px; font-weight:bold;}
	.text-area {float: left; width: 420px;}
	.text-area textarea {width: 350px; height: 250px;}
	.search-result {width: 1000px; height: 400px;}
	.red-message {color:red;}
	.inputs {font-family:Consolas, monospace;}
</style>
<form action="" method="post">
	Регулярное выражение: <input type="text" name="regExp" value="<?=$regExp?>" size="60" class="inputs" />
	ищем строку: <input type="checkbox" value="Y" name="searchStr" <?if ($searchStr == 'Y') {?>checked="checked"<?}?> />
	<br /><br />
	<div class="text-area">
		Искать только в этих директориях:<br />
		<textarea name="searchCat" class="inputs"><?samplePrint($searchCat)?></textarea>
	</div>
	<div class="text-area">
		Игнорировать директории:<br />
		<textarea name="badCat" class="inputs"><?samplePrint($badCat)?></textarea>
	</div>
	<div class="text-area">
		Игорировать поддиретории:<br />
		<textarea name="badUnderCat" class="inputs"><?samplePrint($badUnderCat)?></textarea>
	</div>
	<div style="clear:both;"></div>
	<br />
	<input type="submit" value="Искать" />
</form>
<br /><br />
<?if ($isOk) {?><textarea class="search-result inputs"><?
if (is_array($searchCat) && ! empty($searchCat)) {
	foreach ($searchCat as $val) {
		Search($_SERVER['DOCUMENT_ROOT'] . '/' . $val);
	}
} else {
	Search($_SERVER['DOCUMENT_ROOT']);
}
?></textarea><?}?>


<?
/**
 * Функция рекурсивного поиска
 *
 * @param string $path - путь
 * @return boolean
 */
function Search($path)
{
	global $beginTime, $regExp, $flagSearchEnd, $searchStr;
	if ($flagSearchEnd) {
		return false;
	}
	if ($regExp == '') {
		echo "\tERROR: Ошибка в регулярном выражении\n";
		$flagSearchEnd = true;
		return false;
	}

	if (time() - $beginTime > 20) {
		echo "\tERROR: Лимит времени исчерпан\n";
		$flagSearchEnd = true;
		return false;
	}

	if (is_dir($path) && ! checkIsBadDir($path)) {// dir
		$totalCou++;
		$dir = opendir($path);
		while($item = readdir($dir)) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			Search($path.'/'.$item);
		}
		closedir($dir);
	} else {// file
		if (substr($path, -4) == '.php') {
			$str = file_get_contents($path);
			$str = preg_replace('~[ \s\n\r\t]+~', ' ', $str);

			$searchOk = false;
			if ($searchStr == 'Y') {
				if (strpos($str, $regExp) !== false) {
					$searchOk = true;
				}
			} elseif(preg_match($regExp, $str)) {
				$searchOk = true;
			}
			if ($searchOk) {
				echo preg_replace('~^' . $_SERVER['DOCUMENT_ROOT'] . '~i', '', $path) . "\n";
			}

			unset($str);
		}
	}
}

/**
 * Проверяем текущую директорию
 *
 * @param string $dir
 * @return boolean
 */
function checkIsBadDir($dir)
{
	global $badCat, $badUnderCat;

	$dir = trim($dir);

	foreach ($badCat as $val) {
		$val = $_SERVER['DOCUMENT_ROOT'] . '/' . trim(trim($val, '/ '));
		if (strpos($dir, $val) === 0) {
			return true;
		}
	}

	foreach ($badUnderCat as $val) {
		$val = '/' . trim(trim($val, '/ '));
		if (preg_match('~' . $val . '$~i', $dir)) {
			return true;
		}
	}

	return false;
}

/**
 * Печатаем массив
 *
 * @param array $ar - массив на печать
 * @return boolean
 */
function samplePrint($ar) {
	if (! is_array($ar) || empty($ar)) {
		return false;
	}
	foreach ($ar as $val) {
		echo $val . "\n";
	}
	return true;
}

/**
 * Делаем из строки массив
 *
 * @param string $str
 * @return array
 */
function sampleGetArray($str)
{
	$arR = array();
	$str = trim($str);
	if ($str == '') {
		return $arR;
	}

	$arR = explode("\n", $str);

	foreach ($arR as &$val) {
		$val = trim(trim($val, '/ '));
	}

	$arR = array_filter(array_unique($arR));

	return $arR;
}

?>