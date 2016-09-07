<?
/**
 * -=Обработка изображений (ресайз)=-<br><br><b>Пример вызова</b><br>CImg::BResize($arItem['PREVIEW_PICTURE'], $width, $height)<br><i>все остальное за вас сделаю я</i><br><br><b>Methods</b><br>  Resize() - ресайз изображений<br>BResize() -  ресайз в папках битрикса (обертка для Resize())<br>BAResize() - (обертка для BResize()) возвращает массив<br><i>Любые изменения согласуются с автором!!!</i>
 * @author WebExpert, (c)Матяш Сергей
 * @version 1.0, 10.04.2011
 */
class CImg{
	/**
	 * Дескриптор картинки
	 */
	var $desc;
	/**
	 * <b>-Ресайз изображений-</b><br>Обёртка над Resize(), которая возвращает массив.<br>
	 * @param $f_img - Путь к исходному изображению (далее исх. изо).
	 * @param $width - Ширина. Если не указана, то вычеслется исходя из высота и пропорций исх. изо.
	 * @param $height - Высота. Емли не указана, то вычисляется исходя из высоты и пропорций исх. изо.
	 * @param $method - Метод обрезания. Все по центру.
	 * @param $check_s - Проверять размеры исх. изо. По умолчанию - проверять высоту и ширину(если заданы). Если меньше, то не резать.
	 * @param $postfix - Добавочное название к новой папке. Можно передать объект CBitrixComponent
	 * @param $imgFilterCallback - Дополнительный фильтр
	 * @param $fcParams - Параметры фильтра
	 * @param $ret_arr - Возвращать строку или массив. По умолчанию строку.
	 * @tutorial <b>Методы</b><br> CROP - Уменьшает и обрезает исходное изображение по меньшей стороне<br>
	 *   FULL - Уменьшает и вмещает исходное изображение в размеры нового сохраняя пропорции. При этом делает прозрачными не заполненые места, и сохраняет изо.PNG<br>
	 *   Если изо. находится в структуре инфоблока, то сохраняется структура, а к папке добавляются параметры обрезки(bitrix_(сайт ID)_(ширина)x(высота)_(доп. функция)_(параметры доп. функции))<br>
	 *   Усли изо. не в структуре инф. блока, то рядом с изо. создается папка с названием (параметры обрезки), а в папке с именем оригинала лежит обрез. изо.
	 * @return {array()} Массив array('SRC', 'WIDTH', 'HEIGHT')
	 */
	function BAResize($img, $width, $height=0, $method='CROP', $set_site_id=false, $postfix='', $check_s=array('WIDTH'=>'Y', 'HEIGHT'=>'Y'), $imgFilterCallback=false, $fcParams=array()){
		return CImg::BResize($img, $width, $height, $method, $set_site_id, $postfix, $check_s, $imgFilterCallback, $fcParams, true);
	}

	/**
	 * <b>-Ресайз изображений-</b><br>Обёртка над Resize(), с предустановленным параметром $des_img (путь к обрезанному изо)<br>Сохраняет структуру папок, добавляя к папке /upload/bitrix/ параметры обрезанного изображения.<br><i>Исходное изображение может задаваться через ID</i>
	 * @param $f_img - Путь к исходному изображению (далее исх. изо).
	 * @param $width - Ширина. Если не указана, то вычеслется исходя из высота и пропорций исх. изо.
	 * @param $height - Высота. Емли не указана, то вычисляется исходя из высоты и пропорций исх. изо.
	 * @param $method - Метод обрезания. Все по центру.
	 * @param $check_s - Проверять размеры исх. изо. По умолчанию - проверять высоту и ширину(если заданы). Если меньше, то не резать.
	 * @param $postfix - Добавочное название к новой папке. Можно передать объект CBitrixComponent
	 * @param $imgFilterCallback - Дополнительный фильтр
	 * @param $fcParams - Параметры фильтра
	 * @param $ret_arr - Возвращать строку или массив. По умолчанию строку.
	 * @tutorial <b>Методы</b><br> CROP - Уменьшает и обрезает исходное изображение по меньшей стороне<br>
	 *   FULL - Уменьшает и вмещает исходное изображение в размеры нового сохраняя пропорции. При этом делает прозрачными не заполненые места, и сохраняет изо.PNG<br>
	 *   Если изо. находится в структуре инфоблока, то сохраняется структура, а к папке добавляются параметры обрезки(bitrix_(сайт ID)_(ширина)x(высота)_(доп. функция)_(параметры доп. функции))<br>
	 *   Усли изо. не в структуре инф. блока, то рядом с изо. создается папка с названием (параметры обрезки), а в папке с именем оригинала лежит обрез. изо.
	 * @return {str|array()} Путь к обрез. изо, относительно корня сайта. Либо массив array('SRC', 'WIDTH', 'HEIGHT')
	 */
	function BResize($img, $width, $height=0, $method='CROP', $set_site_id=false, $postfix='', $check_s=array('WIDTH'=>'Y', 'HEIGHT'=>'Y'), $imgFilterCallback=false, $fcParams=array(), $ret_arr=false){
		if(is_numeric($img)){
			$img = CFile::GetPath($img);
		}

		// учет многосайтовости
		if ($set_site_id) {
			require_once ($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/mainpage.php");
			$site_id = '_'.CMainPage::GetSiteByHost();
		} else {
			$site_id = '';
		}

		// заполняем постфиксы к папке
		if(get_class($postfix) == 'CBitrixComponent'){
			$postfix = $site_id.'_'.$postfix->__name.'('.$postfix->__template.')';
		} elseif(trim($postfix)=='' || $postfix==false){
			$postfix = $site_id.'_'.$width.'x'.$height.'_'.ToLower($method);
			if ($imgFilterCallback !== false && is_callable($imgFilterCallback)) {
				$p = '_'.$imgFilterCallback.'('.implode(',', $fcParams).')';
				$postfix += $p;
			}
		}

		// в структуре инфоблоков?
		if(strpos($img, 'iblock') !== false){
			$fNew = str_replace("iblock", "iblock".$postfix, $img);
		} else{
			$fNew = dirname($img).'/'.$postfix.'/'.basename($img);
//			$dir = trim(dirname($file),     '/');
//			$dir = trim(strrchr($dir, "/"), '/');
//			$fNew = "/upload/pictures_".$site_id.'_'.$postfix."/".$dir."/".basename($img);
		}

		// нет картинки? создем. иначе возвращаем ее.
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].$fNew)){
			// а если структура папок отсутствует?
			CheckDirPath(str_replace(basename($fNew), '', $_SERVER['DOCUMENT_ROOT'].$fNew), true);

			return CImg::Resize($img, $fNew, $width, $height, $method, $check_s, $imgFilterCallback, $fcParams, $ret_arr);
		} else{
			// возвращаем массив либо путь относительно корня сайта
			if($ret_arr){
				return array(
					"SRC"    => $fNew,
					"WIDTH"  => imagesx($_SERVER['DOCUMENT_ROOT'].$fNew),
					"HEIGHT" => imagesy($_SERVER['DOCUMENT_ROOT'].$fNew)
				);
			} else{
				return $fNew;
			}
		}
	}

	/**
	 * <b>-Ресайз изображений-</b><br>
	 * @param $f_img - Путь к исходному изображению (далее исх. изо).
	 * @param $dest_img - Путь к обрезанному изображению. Если не установлен, то создает изо рядом с исходным добавляя к имени параметры обрезания.
	 * @param $width - Ширина. Если не указана, то вычеслется исходя из высота и пропорций исх. изо.
	 * @param $height - Высота. Емли не указана, то вычисляется исходя из высоты и пропорций исх. изо.
	 * @param $method - Метод обрезания. Все по центру.
	 * @param $check_s - Проверять размеры исх. изо. По умолчанию - проверять высоту и ширину(если заданы). Если меньше, то не резать.
	 * @param $imgFilterCallback - Дополнительный фильтр
	 * @param $fcParams - Параметры фильтра
	 * @param $ret_arr - Возвращать строку или массив. По умолчанию строку.
	 * @tutorial <b>Методы</b><br> CROP - Уменьшает и обрезает исходное изображение по меньшей стороне<br>
	 *   FULL - Уменьшает и вмещает исходное изображение в размеры нового сохраняя пропорции. При этом делает прозрачными не заполненые места, и сохраняет изо.PNG
	 * @return {str|array()} Путь к обрез. изо, относительно корня сайта. Либо массив array('SRC', 'WIDTH', 'HEIGHT')
	 */
	function Resize($f_img, $dest_img, $width, $height=0, $method='CROP', $check_s=array('WIDTH'=>'Y', 'HEIGHT'=>'Y'), $imgFilterCallback=false, $fcParams=array(), $ret_arr=false){
		if(trim($f_img)=='' || ((int)$width <= 0 && (int)$height <= 0)){
			return false;
		}
		if($method == false){
			$method = 'CROP';
		}

		$img = $_SERVER['DOCUMENT_ROOT'].$f_img;

		// размеры входного изо
		$r_size = getimagesize($img);
		$rRaw = $r_size[2];
		$rWidth = $r_size[0];
		$rHeight = $r_size[1];
		if($rRaw > 3){
			return false;
		}

		$width = (int)$width;
		$height = (int)$height;

		if($ret_arr){
			$ret_false = array(
				"SRC" => $f_img,
				"WIDTH" => $rWidth,
				"HEIGHT" => $rHeight
			);
		} else{
			$ret_false = $f_img;
		}

		// а если исходное изображение меньше чем нужно?
		if ($check_size['WIDTH'] == 'Y' || $check_size['HEIGHT'] == 'Y') {
			if($check_size['WIDTH'] == 'Y' && $rWidth < $width){
				return $ret_false;
			}
			if($check_size['HEIGHT'] == 'Y' && $rHeight < $height){
				return $ret_false;
			}
		}

		// достаем недостающие размеры к обрезанию
		if ($width == 0) {
			$Swidth = round($rWidth * ($height / $rHeight));
		} else{
			$Swidth = $width;
		}
		if ($height == 0) {
			$Sheight = round($rHeight * ($width / $rWidth));
		} else{
			$Sheight = $height;
		}

		$dx = 0;
		$dy = 0;
		$sx = 0;
		$sy = 0;

		// создаем картину из имеющегося формата
		switch($rRaw) {
			case 1:
				$src = imagecreatefromgif($img);
				break;
			case 2:
				$src = imagecreatefromjpeg($img);
				break;
			case 3:
				$src = imagecreatefrompng($img);
				break;
			default:
				return false;
		}

		// основная магия преоброзования
		if($width>0 && $height>0){

			switch ($method):
				case 'CROP':
					if($width>$height){
						$_height = round($rHeight * $width/$rWidth);
						$_width = $width;
						$dy = round(($height - $_height)/2);
					} elseif($width<$height){
						$_width = round($rWidth * $height/$rHeight);
						$_height = $height;
						$dx = round(($width - $_width)/2);
					} else{
						$_width = $width;
						$_height = $height;
						if($rWidth>$rHeight){
							$_width = round($rWidth * $height/$rHeight);
						} elseif($rHeight>$rWidth){
							$_height = round($rHeight * $width/$rWidth);
						}
					}
				break;
				case 'FULL':
					$rRaw = 3;
					if($width>$height){
						$_width = round($rWidth * $height/$rHeight);
						$_height = $height;
						$dx = round(($width - $_width)/2);
					} elseif($width<$height){
						$_height = round($rHeight * $width/$rWidth);
						$_width = $width;
						$dy = round(($height - $_height)/2);
					} else{
						$_width = $width;
						$_height = $height;
						if($rWidth>$rHeight){
							$_height = round($rHeight * $width/$rWidth);
							$dy = round(($height - $_height)/2);
						} elseif($rHeight>$rWidth){
							$_width = round($rWidth * $height/$rHeight);
							$dx = round(($width - $_width)/2);
						}
					}
				break;
				default:

			endswitch;

		} else{
			if ($width == 0) {
				$_width = round($rWidth * ($height / $rHeight));
			} else{
				$_width = $width;
			}
			if ($height == 0) {
				$_height = round($rHeight * ($width / $rWidth));
			} else{
				$_height = $height;
			}
		}

		$desc = imagecreatetruecolor($Swidth, $Sheight);
		if ($rRaw == 3) {
			// PNG24 Alpha
			imagefill($desc, 0, 0, imagecolorallocatealpha($desc, 0, 0, 0, 127));
		}

		// ресемплируем
		imagecopyresampled($desc, $src, $dx, $dy, $sx, $sy, $_width, $_height, $rWidth, $rHeight);

		$postfix = '_'.$Swidth.'x'.$Sheight.'_'.ToLower($method);

		// применяем фильтр если передан callback статичный метод или "плавающая" функция
		if ($imgFilterCallback !== false && is_callable($imgFilterCallback)) {
			$c_u_f_params = array(&$desc);
			if(!empty($fcParams))
				$c_u_f_params = array_merge($c_u_f_params, $fcParams);

			call_user_func_array($imgFilterCallback, $c_u_f_params);
			$postfix += '_'.$imgFilterCallback.'('.implode(',', $c_u_f_params).')';
		}

		// если путь назначения пустой, то заполняем его
		$curDir = dirname($f_img);
		if(strpos($curDir, '\\')!=false)
			$delimiter = '\\';
		else
			$delimiter = '/';
		if(trim($dest_img)=='' || $dest_img==false){
			$arn = explode('.', basename($img));
			$return_path = $curDir.$delimiter.$arn[0].$postfix.'.'.$arn[1];
			$dest_img = $_SERVER['DOCUMENT_ROOT'].$return_path;
		} else{
			$return_path = $dest_img;
			$dest_img = $_SERVER['DOCUMENT_ROOT'].$return_path;
		}

		if($rRaw == 3){
			$return_path = str_replace(strrchr($return_path, "."), '.png', $return_path);
			$dest_img = str_replace(strrchr($dest_img, "."), '.png', $dest_img);
		}

		$this->desc = $desc;

		// итог - сохраняем либо png, либо jpeg
		switch($rRaw) {
			case 3:
				imagesavealpha($desc, true);
				imagepng($desc,  $dest_img);
				break;
			default:
				imagejpeg($desc, $dest_img, 100);
				break;
		}
		imagedestroy($desc);

		// возвращаем массив либо путь относительно корня сайта
		if($ret_arr){
			return array(
				"SRC"    => $return_path,
				"WIDTH"  => $_width,
				"HEIGHT" => $_height
			);
		} else{
			return $return_path;
		}
	}
}
?>