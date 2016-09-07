<?
/**
 * eclipse PDT component params code hints
 * You can import these templates (xml below) to your IDE via Preferences > PHP > Editor > Templates > Import...
 * @version 2.0
 * @author hipot AT wexpert.ru, 2012
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$folder = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/bitrix/';
$dir = opendir($folder);
if (! $dir) {
	die("Cant open dir");
}

header("Content-type: text/xml");

echo
'<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\r" .
'<templates>' . "\r";


while (($component = readdir($dir)) !== false) {
	if (
		$component == '.' || $component == '..'
		|| filetype($folder . $component) != 'dir'
		|| !is_file($folder . $component . '/.parameters.php')
		|| !is_file($folder . $component . '/lang/ru/.parameters.php')
	) {
		continue;
	}

	__IncludeLang($folder . $component . '/lang/ru/.parameters.php');
	__IncludeLang($folder . $component . '/lang/ru/.description.php');
	require_once($folder . $component . '/.parameters.php');
	@include_once($folder . $component . '/.description.php');

	$arComponentParameters = CComponentUtil::GetComponentProps("bitrix:" . $component);

	if (count($arComponentParameters['PARAMETERS']) == 0) {
		continue;
	}


	//concat comment
	$outString = '/**' . "\r";

	if ($arComponentDescription['NAME'] == '') {
		$arComponentDescription['NAME'] = 'bitrix:' . $component;
	} else {
		$arComponentDescription['NAME'] .= ' ( bitrix:' . $component . ' )';
	}
	$outString .= ' * Компонент: ' . $arComponentDescription['NAME'] . "\r";
	if ($arComponentDescription['DESCRIPTION'] != '') {
		$outString .= ' * ' . $arComponentDescription['DESCRIPTION'] . "\r";
	}
	$outString .= ' * '. "\r" . ' * ' . "Параметры: " . str_repeat('*', 70) . "\r" . ' * '. "\r";





	// writeOneGroup
	if (! function_exists('writePropsToOut')) {
		function writePropsToOut($props, &$outString)
		{
			$maxParamLen = 25;
			foreach ($props as $paramName => $param) {
				$arP[] = strlen($paramName);
			}
			$maxParamLen = max($arP) + 1;

			foreach ($props as $paramName => $param) {
				if (trim($param['DEFAULT']) != '') {
					$def = ' (по-умолчанию - "' . str_replace('&', '&amp;', $param['DEFAULT']) . '")';
				}

				$outString .= ' * ' . sprintf('%-'.$maxParamLen.'s', $paramName) . '=&gt; ' . $param['NAME'] . ' ' . $param['TYPE'] . $def . "\r";
			}
		}
	}

	if (count($arComponentParameters['GROUPS']) == 0) {
		writePropsToOut($arComponentParameters['PARAMETERS'], $maxParamLen, $outString);
	} else {
		foreach ($arComponentParameters['GROUPS'] as $groupName => $group) {
			$propsGroup = array();
			foreach ($arComponentParameters['PARAMETERS'] as $paramName => $param) {
				if ($param['PARENT'] == $groupName) {
					$propsGroup[$paramName] = $param;
				}
			}

			$outString .= ' * '. "\r" . ' * [' . $group["NAME"] . "] " . "\r" . ' * '. "\r";

			writePropsToOut($propsGroup, $outString);
		}
	}


	$outString .= " */";
	$outString = str_replace("\r", "&#13;", $outString);

	echo '<template autoinsert="true" context="php" deleted="false" description="Компонент: '. htmlspecialchars($arComponentDescription['NAME']) .'" enabled="true" name="'. 'bitrix:' .  $component .'">' . $outString . '</template>' . "\r";
}
closedir($dh);

echo '</templates>';




require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>