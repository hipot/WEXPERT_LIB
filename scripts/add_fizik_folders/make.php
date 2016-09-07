<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

include_once"cfg.php";
include_once"page_template.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Создание каталогов меню</title>
</head>
<body bgcolor="Cornsilk">
<?

function СatalogName( $f_name, $name) {
	
	switch($f_name) {
		case "#TRANSLIT#" :
		$params = Array(
				"max_len" => "100", // обрезает символьный код до 100 символов
				"change_case" => "L", // буквы преобразуются к нижнему регистру
				"replace_space" => "-", // меняем пробелы на нижнее подчеркивание
				"replace_other" => "-", // меняем левые символы на нижнее подчеркивание
				"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
				"use_google" => "true", // отключаем использование google
		);
		
		return CUtil::translit($name, "ru" , $params);
		break;
		
	case"#TRANSLATE_EN#" :
		$name = str_replace(" ", "+", $name);
		$content = file_get_contents("http://translate.yandex.net/api/v1/tr/translate?lang=ru-en&text=".$name);
		
		$dom = new DOMDocument();
		$dom->loadXML($content);
		
		$enName = $dom->getElementsByTagName('text')->item(0)->nodeValue;
		echo $enName;
		
		return ToLower(str_replace(array(" ", "_", "\t", "\n", "*", "%", "&", "#", "$"), array("-"), trim($enName)));
		break;
	
	case"#TRANSLATE_DE#" :
		$name = str_replace(" ", "+", $name);
		$content = file_get_contents("http://translate.yandex.net/api/v1/tr/translate?lang=ru-de&text=".$name);
		
		$dom = new DOMDocument();
		$dom->loadXML($content);
		
		$deName = $dom->getElementsByTagName('text')->item(0)->nodeValue;
		
		return ToLower(str_replace(array("- ", " ", "_", "\t", "\n", "*", "%", "&", "#", "$"), array("", "-"), trim($deName)));
		break;
	
	default:
		return ToLower(str_replace(array(" ", "_", "\t", "\n", "*", "%", "&", "#", "$"), array("-"), trim($f_name)));
	}
}


foreach ($folders as $v) {
	$folder = $_SERVER["DOCUMENT_ROOT"].$v["F_FOLDER"].СatalogName( $v["F_NAME"], $v["NAME"] )."/";
	echo $folder."<br>";
	mkdir($folder, 0700, true);
	
	$file = str_replace(array("#PAGE_TITLE#"), array($v["TITLE"]), file_get_contents("page_template.php"));
	
	file_put_contents($folder.".section.php", "<?\n\$sSectionName = \"".$v["NAME"]."\";\n\$arDirProperties = Array();\n?>");
	file_put_contents($folder."index.php", $file);
	
	if(count($v["MENU"]) > 0) {
		foreach($v["MENU"] as $k => $v) {
			$menuText = "<?\n\$aMenuLinks = Array(\n";
			foreach($v as $val) {
				$menuText .= "\tArray(\n\t\t\"".$val['NAME']."\",\n\t\t \""
			.str_replace(array("#TRANSLIT#", "#TRANSLATE_EN#", "#TRANSLATE_DE#"), array(СatalogName( "#TRANSLIT#", $val['NAME']),
					СatalogName( "#TRANSLATE_EN#", $val['NAME']), СatalogName( "#TRANSLATE_DE#", $val['NAME'])), $val['LINK'])
				."\",\n\t\t Array(),\n\t\t Array(),\n\t\t \"\"\n \t),";
			}
			
			$menuText .= "\n);\n?>";
			
			file_put_contents($folder.".".$k.".menu.php", $menuText);
		}
	}
}
?>
</body>
</html>
