<?
/**
 * Библиотека трансформации изображений с кэшем результатов (plot)
 * @copyright 2011, WebExpert
 * @version 2.0
 */

/**
 * Базовый класс трансформации,
 * модель "контроллер"
 */
class PlotBase
{
	
	/**
	 * Проверяет, является ли картинка больше переданной
	 *
	 * @param string $fullFilePath полный путь к файлу картинки на диске
	 * @param int $needWidth необходимая ширина в пикселях
	 * @param int $needHeight необходимая высота в пикселях
	 * @return bool
	 */
	function isImageBigger($fullFilePath, $needWidth, $needHeight)
	{
		$bBigger = false;
		
		$real_size  = getimagesize($fullFilePath);
		$RealWidth  = $real_size[0];
		$RealHeight = $real_size[1];
		if (($needWidth > 0 && $RealWidth > $needWidth) || ($needHeight > 0 && $RealHeight > $needHeight)) {
			$bBigger = true;
		}
		
		return $bBigger;
	}
	
	
	/**
	 * Функция по уменьшению изображений и сохранению их в папке upload
	 *
	 * @param string $file путь к файлу изображения от DOCUMENT_ROOT
	 * @param string $type тип, для добавленя к имени папки iblock. например little, middle, width125
	 * @param int $width ширина нужного изображения
	 * @param int $height = false высота нужного [optional]
	 * @param bool $set_site_id = false устанавливать ли в имя директорий имя сайта, т.е. iblock_{SITE_ID}_{TYPE} [optional]
	 * @param bool|callback $imgFilterCallback = false применить ли фильтр к уменьшенному изображению, должна быть пользовательская функция, куда передается
	 * ссылка на изображение-ресурс непосредственно перед сохранением уменьшенной фотографии на диск [optional]
	 * @param bool $checkBigger проверять, больше ли картинка необходимой перед трансформацией
	 * @return bool|array массив с данными уменьшенного фото, или false в случае ошибки
	 *
	 * @uses CheckDirPath(), /bitrix/modules/main/include/mainpage.php, CMainPage::GetSiteByHost() *
	 */
	function Transform($file, $type, $width, $height = 0, $set_site_id = false, $imgFilterCallback = false, $checkBigger = true)
	{
		// полный путь к файлу
		$fileOld = $_SERVER['DOCUMENT_ROOT'] . $file;
		
		// если нет параметров, то нечего и делать
		if (trim($file) == '' || trim($type) == '' || ($width == 0 && $height == 0) || !is_file($fileOld)) {
			return false;
		}
		
				
		// учет многосайтовости
		if ($set_site_id) {
			require_once ($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/mainpage.php");
			$site_id = CMainPage::GetSiteByHost();
			$site_id = $site_id . "_";
		} else {
			$site_id = '';
		}
		
		// флаг - файл внутри структуры инфоблоков или нет
		$insideIblocksFile = false;
		if (! preg_match("#iblock#", $file)) {
			$insideIblocksFile  = true;
		}
		
		if ($insideIblocksFile) {
			// находим последнюю директорию, в которой лежит изображение (изображение не в структуре iblock)
			$dir = trim(dirname($file),     '/');
			$dir = trim(strrchr($dir, "/"), '/');
			$fileNew = "/upload/pictures_" . $site_id . $type . "/" . $dir . "/" . basename($file);
			$file_2_parse_dirs = "/upload/pictures/" . $dir . "/" . basename($file);
		} else {
			$fileNew = str_replace("iblock", "iblock_" . $site_id . $type, $file); // новый путь к файлу
		}
		
		$real_size  = getimagesize($fileOld);
		$RealWidth  = $real_size[0];
		$RealHeight = $real_size[1];
		// тип картинки
		$rawir      = $real_size[2];
		
		// проверка, не меньше ли переданная картинка, чем нужно
		if (! PlotBase::isImageBigger($fileOld, $width, $height) && $checkBigger) {
			return array("WIDTH"  => $RealWidth,
						 "HEIGHT" => $RealHeight,
						 "SRC"    => $file);
		}
		
		// усечение до переданных размеров
		if (intval($width) > 0 && intval($height) > 0) {
			$work_height = $height;
			$work_width = round($height * $RealWidth / $RealHeight);
			if ($work_width < $width) {
				$work_width = $width;
				$work_height = round($width * $RealHeight / $RealWidth);
			}
		} else {
			// заполнение недостающих размеров, если нужно
			if (intval($width) == 0) {
				$width = round($RealWidth * ($height / $RealHeight));
			}
			if (intval($height) == 0) {
				$height = round($RealHeight * ($width / $RealWidth));
			}
			$work_width = $width;
			$work_height = $height;
		}
			
		if (! is_file($_SERVER['DOCUMENT_ROOT'] . $fileNew)) {
				
			if ($insideIblocksFile) {
				$arDir = explode("/", $file_2_parse_dirs); // папки до картинки
			} else {
				$arDir = explode("/", $file); // папки до картинки
			}
			
			CheckDirPath($_SERVER['DOCUMENT_ROOT'] . "/upload/" . $arDir[2] . "_" . $site_id . $type . "/" . $arDir[3] . "/", true);
				
			switch($rawir) {
				case 1:
					$src = imagecreatefromgif($fileOld);
					break;
				case 2:
					$src = imagecreatefromjpeg($fileOld);
					break;
				case 3:
					$src = imagecreatefrompng($fileOld);
					break;
				default:
					return false;
			}
			
			$desc = imagecreatetruecolor($width, $height);
			if ($rawir == 1 || $rawir == 3) {
				// PNG24 Alpha
				imagefill($desc, 0, 0, imagecolorallocatealpha($desc, 0, 0, 0, 127));
			}
			
			imagecopyresampled($desc, $src, 0, 0, 0, 0, $work_width, $work_height, $RealWidth, $RealHeight);
			
			/* применяем фильтр если передан callback статичный метод или "плавающая" функция */
			if ($imgFilterCallback !== false && is_callable($imgFilterCallback)) {
				call_user_func_array($imgFilterCallback, array(&$desc));
			}
			
			/* итог - сохраняем либо png, либо jpeg */
			switch($rawir) {
				case 1:
					imagesavealpha($desc, true);
					imagegif($desc,  $_SERVER['DOCUMENT_ROOT'] . $fileNew);
				case 3:
					imagesavealpha($desc, true);
					imagepng($desc,  $_SERVER['DOCUMENT_ROOT'] . $fileNew);
					break;
				default:
					imagejpeg($desc, $_SERVER['DOCUMENT_ROOT'] . $fileNew, 100);
					break;
			}
			imagedestroy($desc);
			return array("WIDTH"  => $width,
						 "HEIGHT" => $height,
						 "SRC"    => $fileNew);
		} else {
			return array("WIDTH"  => $width,
						 "HEIGHT" => $height,
						 "SRC"    => $fileNew);
		}
	}
	
}
?>