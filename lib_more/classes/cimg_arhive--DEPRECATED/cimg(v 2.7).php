<?
/**
 * -=Обработка изображений=-<br><br><b>Пример вызова</b><br>CImg::Resize($arItem['PREVIEW_PICTURE'], $width, $height)<br><i>все остальное за вас сделаю я</i><br><br><b>Methods</b><br>
 * Resize() - ресайзит изображений в папках битрикс<br>
 * AResize() -  то же что и Resize, но взовращает массив <br>
 * Overlay() - накладывает изображение (watermark)<br>
 * ResizeOverlay() - ресайзит и накладывает изображение (watermark)<br>
 * <i>Любые изменения согласуются с автором!!!</i>
 * @link http://hipot.wexpert.ru/Codex/cimg-constantly-integrable-modifier-of-graphics/
 * @author WebExpert, (c)Матяш Сергей
 * @version 2.7, 1.10.2011
 */
class CImg{
	/**
	 * @var {const} Метод ресайза CROP
	 */
	const M_CROP = 'CROP';
	/**
	 * @var {const} Метод ресайза PROPORTIONAL
	 */
	const M_PROPORTIONAL = 'PROPORTIONAL';
	/**
	 * @var {const} Метод ресайза CROP_TOP
	 */
	const M_CROP_TOP = 'CROP_TOP';
	/**
	 * @var {const} Метод ресайза FULL
	 */
	const M_FULL = 'FULL';
	/**
	 * @var {const} Метод ресайза STRETCH
	 */
	const M_STRETCH = 'STRETCH';
	/**
	 * @var {const} Позиция наложения - 'lt';
	 */
	const P_LEFT_TOP = 'lt';
	/**
	 * @var {const} Позиция наложения - 'rt';
	 */
	const P_RIGHT_TOP = 'rt';
	/**
	 * @var {const} Позиция наложения - 'rb';
	 */
	const P_RIGHT_BOTTOM = 'rb';
	/**
	 * @var {const} Позиция наложения - 'lb';
	 */
	const P_LEFT_BOTTOM = 'lb';
	/**
	 * @var {resource} Ресурс входящего изображения GD
	 */
	public $res;
	/**
	 * @var {resource} Ресурс результирующего изображения
	 */
	public $des;
	/**
	 * @var {string} Абсолютный путь к картинке на сервере
	 */
	public $src;
	/**
	 * @var {string} Путь к картинке относительно корня сайта
	 */
	public $r_src;
	/**
	 * @var {string = resource|bxid|abs|rel} Тип пути изображения. <br/>
	 * Путь к изображению относительно корня сайта, либо относительно корня документов, либо битрикс ID, либо ресурс изображения GD.<br>
	 * Если входящее изо - ресурс GD, то необходимо указать $this->src и $this->r_src
	 */
	public $path_type;
	/**
	 * @var {string} Путь для сохранения изображения
	 */
	public $path;
	/** {string}
	 * @var Путь для сохранения изображения относительно корня сайта
	 */
	public $r_path;
	/**
	 * @var {string} Метод обрезания ( CROP | FULL | STRETCH )
	 */
	public $method=self::M_CROP;
	/**
	 * @var {string} Постфикс для измененного изображения. <br/>Заполняется при любой трансформации - как идентификатор для кеша.
	 */
	public $postfix = '';
	/**
	 * @var {string} Графический формат изображения.
	 */
	public $raw;
	/**
	 * @var {int} Ширина изображения
	 */
	public $w = 0;
	/**
	 * @var {int} Высота изображения
	 */
	public $h = 0;
	/**
	 * @var {int} Ширина результируюшего изображения (что нужно получить)
	 */
	public $w_out = 0;
	/**
	 * @var {int} Высота результируюшего изображения (что нужно получить)
	 */
	public $h_out = 0;
	/**
	 * @var {int} Фактическая ширина для ресемплирования (destination width)
	 */
	public $w_fact = 0;
	/**
	 * @var {int} Фактическая высота для ресемплирования (destination height)
	 */
	public $h_fact = 0;
	/**
	 * @var {int} Destination-x (resampler)
	 */
	public $dx = 0;
	/**
	 * @var {int} Destination-y (resampler)
	 */
	public $dy = 0;
	/**
	 * @var {int} Source-x (resampler)
	 */
	public $sx = 0;
	/**
	 * @var {int} Source-y (resampler)
	 */
	public $sy = 0;
	/**
	 * @var {array} Массив ошибок по всему объекту.
	 */
	public $ERROR = array();
	/**
	 * @var bool Устанавливается если скрипты уже были добавлены на страницу
	 */
	static $scripts_putted = false;



	/**
	 * Загружает картинку
	 * @param $img Путь к картинке относительно корня сайта,<br> либо относительно корня документов, либо битрикс ID, либо ресурс изображения GD.<br>
	 * Если РЕСУРС, то необходимо указать $src путь к псевдо-картинке.
	 * @param $src Путь к псевдо-картинке. Абсолютный путь на диске либо относительно корня сайта.
	 * @return void
	 */
	public function load($img, $src=false){
		if(is_resource($img)){					// если входит ресурс изображения GD
			$this->path_type = 'resource';
			$this->res = $img;
			$this->w = imagesx($this->res);
			$this->h = imagesy($this->res);
			if(!$src){
				$this->ERROR['path_not_set'] = 'Y';
			}
			if(strpos($src, $_SERVER['DOCUMENT_ROOT'])==false){
				$this->r_src = $src;
				$this->src = $_SERVER['DOCUMENT_ROOT'].$this->r_src;
			} else{
				$this->src = $src;
				$this->r_src = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->src);
			}
		} elseif(is_numeric($img)){				// если входит БитриксID картинки
			$this->path_type = 'bxid';
			$this->r_src = CFile::GetPath($img);
			$this->src = $_SERVER['DOCUMENT_ROOT'].$this->r_src;
		} elseif(strpos($img, $_SERVER['DOCUMENT_ROOT'])!==false){	// если входит абсолютный путь к картинке на диске
			if(!is_file($img)){
				$this->ERROR['wrong_input_img_type']='Y';
			}
			$this->path_type = 'abs';
			$this->src = $img;
			$this->r_src = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->src);
		} elseif(is_file($_SERVER['DOCUMENT_ROOT'].$img)){
			$this->path_type = 'rel';			// если входит путь к картинке относительно корня сайта
			$this->r_src = $img;
			$this->src = $_SERVER['DOCUMENT_ROOT'].$this->r_src;
		} else{
			$this->ERROR['wrong_input_img_type']='Y';
		}

		if($this->path_type!='resource'){			// если путь к картинке указан, берем параметры картинки
			$r_size = getimagesize($this->src);
			$this->raw = (int)$r_size[2];
			$this->w = (int)$r_size[0];
			$this->h = (int)$r_size[1];
			if($this->raw > 3){
				$this->ERROR['wrong_format']='Y';
			}
		}
	}

	/**
	 * Проверяет размеры изображения(чтоб не увеличивать картинку), если исохдная (ширина|высота) меньше нужной.
	 * @param $w {bool} Проверять ширину
	 * @param $h {bool} Проверять высоту
	 * @return void Присваивает ошибки (small_width|small_height)
	 */
	public function checkSz($w = true, $h = true){
		if ($w || $h) {
			if($w && $this->w_out > $this->w){
				$this->ERROR['small_width']='Y';
			}
			if($h && $this->h_out > $this->h){
				$this->ERROR['small_height']='Y';
			}
		}
	}

	/**
	 * Создает ресурс изображения основываясь на формате входного изо.
	 */
	public function imagecreatefrom(){
		switch($this->raw) {
			case 1:
				$this->res = imagecreatefromgif($this->src);
				break;
			case 2:
				$this->res = imagecreatefromjpeg($this->src);
				break;
			case 3:
				$this->res = imagecreatefrompng($this->src);
				break;
			default:
				return false;
		}
	}

	/**
	 * Метод ресайза картинки CROP (обрезает, и размещает по центру)
	 */
	public function methodCROP(){
		$this->h_fact = $this->h_out;
		$this->w_fact = round($this->h_out * $this->w / $this->h);
		if ($this->w_fact < $this->w_out) {
			$this->w_fact = $this->w_out;
			$this->h_fact = round($this->w_out * $this->h / $this->w);
		}
		$this->dy = ($this->h_out - $this->h_fact)/2;
		$this->dx = ($this->w_out - $this->w_fact)/2;
	}

	/**
	 * Метод ресайза картинки PROPORTIONAL (уменьшает пропорционально)
	 */
	public function methodPROPORTIONAL(){
		$x = $this->w / $this->h;
		if($x>0){
			$this->h_fact = $this->h_out;
			$this->w_fact = round($this->h_out * $x);
		} else{
			$this->w_fact = $this->w_out;
			$this->h_fact = round($this->w_out * $x);
		}
		$this->w_out = $this->w_fact;
		$this->h_out = $this->h_fact;
	}

	/**
	 * Метод ресайза картинки CROP_TOP (обрезает и размещает вверху)
	 */
	public function methodCROP_TOP(){
		$this->h_fact = $this->h_out;
		$this->w_fact = round($this->h_out * $this->w / $this->h);
		if ($this->w_fact < $this->w_out) {
			$this->w_fact = $this->w_out;
			$this->h_fact = round($this->w_out * $this->h / $this->w);
		}
		$this->dy = 0;
		$this->dx = ($this->w_out - $this->w_fact)/2;
	}

	/**
	 * Метод ресайза картинки FULL (вмещает)
	 */
	public function methodFULL(){
		$this->raw = 3;
		$this->w_fact = $this->w_out;
		$this->h_fact = round($this->w_out * $this->h / $this->w);
		if ($this->h_fact > $this->h_out) {
			$this->h_fact = $this->h_out;
			$this->w_fact = round($this->h_out * $this->w / $this->h);
		}
		$this->dy = ($this->h_out - $this->h_fact)/2;
		$this->dx = ($this->w_out - $this->w_fact)/2;
	}

	/**
	 * Метод ресайза картинки STRETCH (растягивает)
	 */
	public function methodSTRETCH(){
		$this->w_fact = $this->w_out;
		$this->h_fact = $this->h_out;
	}

	/**
	 * Проверяет закешированность картинки
	 * @param bool $path Путь к файлу, не указывается, берется из префикса
	 * @return bool Если картинка закеширована, то возвращает true, иначе false
	 */
	public function wasCached($path=false){
		if($path){
			return file_exists($path);
		} else{
			return file_exists($this->path);
		}
	}

	/**
	 * Ресайзит изображение используя метод $method. <br>Можно задать свой новый метод <br>создав в классе новый метод <br>с названием - method[МЕТОД_ТРАНСФОРМАЦИИ] - methodMYMETHOD
	 */
	public function do_resize(){
		if(!$this->method){
			$this->method = self::M_CROP;
		}
		$method = 'method'.$this->method;
		if(!method_exists($this, $method)){
			$this->ERROR['undefined_method'] = 'Y';
		} else{
			if ($this->w_out == 0) {
				$this->w_out = round($this->w * ($this->h_out / $this->h));
			}
			if ($this->h_out == 0) {
				$this->h_out = round($this->h * ($this->w_out / $this->w));
			}
			$this->$method(); // метод ресайза(подготавливает параметры для ресемплера)
			$this->des = imagecreatetruecolor($this->w_out, $this->h_out);
			if ($this->raw == 3 || $this->raw == 1) {
				// PNG24 Alpha
				imagefill($this->des, 0, 0, imagecolorallocatealpha($this->des, 0, 0, 0, 127));
			}
			// ресемплируем
			imagecopyresampled($this->des, $this->res, $this->dx, $this->dy, $this->sx, $this->sy, $this->w_fact, $this->h_fact, $this->w, $this->h);
		}
	}

	/**
	 * as Watermark. Накладывает изображение $img_src на загруженное изображение.
	 * @param $img_src - накладываемое изображение
	 * @param $pos(false-центр) - параметры наложения:<br>
	 * (str)lt: left top<br>
	 * (str)lb: left bottom<br>
	 * (str)rt: right top<br>
	 * (str)rb: right bottom<br>
	 * array($x, $y) - массив со значениями в пикселях для отступов слева и сверху. Допускаются процентные данные.<br>
	 * array('rb' => array(10, 10)) - массив значений в пикселях, rb - отталкиваясь, соответственно, справа и снизу
	 */
	public function do_overlay($img_src, $pos=false){
		if(is_numeric($img_src)){
			$img_src = CFile::GetPath($img_src);
		} elseif(strpos($img_src, $_SERVER['DOCUMENT_ROOT'])!==false){
			if(!is_file($img_src)){
				$this->ERROR['wrong_over_file']='Y';
			}
			$img_src = $img_src;
		} elseif(is_file($_SERVER['DOCUMENT_ROOT'].$img_src)){
			$img_src = $_SERVER['DOCUMENT_ROOT'].$img_src;
		} else{
			$this->ERROR['wrong_over_file'] = 'Y';
		}

		if($this->des){
			$w_dest = ($this->w_out)?$this->w_out:$this->w;
			$h_dest = ($this->h_out)?$this->h_out:$this->h;
		} else{
			$this->des = $this->res;
			$w_dest = ($this->w_out)?$this->w_out:$this->w;
			$h_dest = ($this->h_out)?$this->h_out:$this->h;
		}

		$r_size = getimagesize($img_src);
		$w_img = $r_size[0];
		$h_img = $r_size[1];
		$rRaw = $r_size[2];
		switch($rRaw) {
			case 1:
				$img = imagecreatefromgif($img_src);
				break;
			case 2:
				$img = imagecreatefromjpeg($img_src);
				break;
			case 3:
				$img = imagecreatefrompng($img_src);
				break;
			default:
				return false;
		}
		imagealphablending($img, true);

		if(!$pos){
			$dst_x = floor($w_dest/2 - $w_img/2);
			$dst_y = floor($h_dest/2 - $h_img/2);
		} else{
			if(is_array($pos)){
				$kp = key($pos);
				if(is_numeric($pos[ $kp ]) || strpos($pos[ $kp ], '%')!==false){
					$dst_x = (strpos($pos[0], '%')!==false)?$w_dest*(str_replace('%', '', $pos[0])*1)/100:$pos[0];
					$dst_y = (strpos($pos[1], '%')!==false)?$h_dest*(str_replace('%', '', $pos[1])*1)/100:$pos[1];
				} else{
					if(!is_array($pos[ $kp ])){
						$pos = array($pos[ $kp ] => array(0,0));
						$kp = key($pos);
					}
					$pos[ $kp ][0] = (strpos($pos[ $kp ][0], '%')!==false)?$w_dest*(str_replace('%', '', $pos[ $kp ][0])*1)/100:$pos[ $kp ][0];
					$pos[ $kp ][1] = (strpos($pos[ $kp ][1], '%')!==false)?$h_dest*(str_replace('%', '', $pos[ $kp ][1])*1)/100:$pos[ $kp ][1];
					switch ($kp) {
						case ('lt'):
							$dst_x = $pos[ $kp ][0];
							$dst_y = $pos[ $kp ][1];
							break;
						case ('lb'):
							$dst_x = $pos[ $kp ][0];
							$dst_y = $h_dest - $h_img - $pos[ $kp ][1];
							break;
						case ('rt'):
							$dst_x = ($w_dest - $w_img) - $pos[ $kp ][0];
							$dst_y = $pos[ $kp ][1];
							break;
						case ('rb'):
							$dst_x = ($w_dest - $w_img) - $pos[ $kp ][0];
							$dst_y = $h_dest - $h_img - $pos[ $kp ][1];
							break;
						case (''):
							$dst_x = $w_dest/2 - $w_img/2;
							$dst_y = $h_dest/2 - $h_img/2;
					}
				}
			} else{
				switch ($pos) {
					case ('lt'):
						$dst_x = 0;
						$dst_y = 0;
						break;
					case ('lb'):
						$dst_x = 0;
						$dst_y = $h_dest - $h_img;
						break;
					case ('rt'):
						$dst_x = $w_dest - $w_img;
						$dst_y = 0;
						break;
					case ('rb'):
						$dst_x = $w_dest - $w_img;
						$dst_y = $h_dest - $h_img;
						break;
					case (''):
						$dst_x = $w_dest/2 - $w_img/2;
						$dst_y = $h_dest/2 - $h_img/2;
				}
			}
		}

		imagecopy($this->des, $img, $dst_x, $dst_y, 0, 0, $w_img, $h_img);
		imagedestroy($img);
	}

	/**
	 * Формирует путь для сохранения изображения в дереве директорий Битрикс.
	 * @param bool $ssid ID сайта указывается при многосайтовости
	 */
	public function pathforbx($ssid=false){
		// учет многосайтовости
		if ($ssid) {
			require_once ($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/mainpage.php");
			$this->postfix = CMainPage::GetSiteByHost().'_'.$this->postfix;
		}

		if(strpos($this->src, 'iblock') !== false){
			// в структуре инфоблоков?
			$this->path = str_replace("iblock", "iblock_".$this->postfix, $this->src);
		} else{
			// иначе в папке upload
			// $this->path = dirname($this->src).'/'.$this->postfix.'/'.basename($this->src);
			$this->path = $_SERVER['DOCUMENT_ROOT'].'/upload'.str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(str_replace('bitrix', 'bx', $this->src))).'/'.$this->postfix.'/'.basename($this->src);
		}
		$this->r_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->path);
		if($this->method == self::M_FULL){
			$this->r_path = str_replace(strrchr($this->r_path, "."), '.png', $this->r_path);
			$this->path = str_replace(strrchr($this->path, "."), '.png', $this->path);
		}
	}

	/**
	 * Формирует путь для сохранения
	 */
	public function pathfor(){
		// если путь назначения пустой, то заполняем его
		$curDir = dirname($this->r_src);
		if(strpos($curDir, '\\')!=false)
			$delim = '\\';
		else
			$delim = '/';
		if(trim($this->r_path)=='' || $this->r_path==false){
			$arn = explode('.', basename($this->src));
			$this->r_path = $curDir.$delim.$arn[0].$this->postfix.'.'.$arn[1];
			$this->path = $_SERVER['DOCUMENT_ROOT'].$this->r_path;
		} else{
			$this->path = $_SERVER['DOCUMENT_ROOT'].$this->r_path;
		}

		if($this->method == self::M_FULL){
			$this->r_path = str_replace(strrchr($this->r_path, "."), '.png', $this->r_path);
			$this->path = str_replace(strrchr($this->path, "."), '.png', $this->path);
		}
	}

	/**
	 * Сохраняет изображение
	 * @param $q= 100 Качество для jpeg.
	 */
	public function imagesave($q=100){
		// итог - сохраняем либо png, либо jpeg
		if(function_exists('CheckDirPath')){
			CheckDirPath($this->path);
		}
		if($this->raw == 3 || $this->raw == 1){
			imagesavealpha($this->des, true);
			imagepng($this->des,  $this->path);
		} else{
			imagejpeg($this->des, $this->path, $q);
		}
	}

	/**
	 * @return void Почистит ресурсы изображений (освобождает память)
	 */
	public function destroyme(){
		imagedestroy($this->des);
		imagedestroy($this->res);
	}

	/**
	 * Ресайзит изображение
	 * @param $f Картинка (rel_p|abs_p|bitrix_id)
	 * @param $w Ширина
	 * @param bool $h Высота
	 * @param string $m= CROP Метод трансвформации.( CROP | CROP_TOP | FULL | STRETCH )<br>
	 * Можно задать свой новый метод <br>создав в классе новый метод <br>с названием - method[МЕТОД_ТРАНСФОРМАЦИИ] - methodMYMETHOD
	 * @param array $ch Проверять array(ширину, высоту) входящей картинки<br> чтоб не увеличивать маленькую.
	 * @param bool $sid= false Идентификатор сайта (указывается при многосайтовости)
	 * @param bool $ar= false Возврацать массив или строку. По умолчанию строку
	 * @return (array|str) Путь к картинке или массив (шир, выс, путь)
	 */
	public static function Resize($f, $w, $h=false, $m=self::M_CROP, $ch=array(true, true), $sid=false, $ar=false){
		$mi = new self();
		$mi->load($f);
		$mi->w_out = $w;
		$mi->h_out = $h;
		$mi->method = $m;
		$mi->postfix = (string)$w.'_'.(string)$h.'_'.(string)$m.'_'.implode('-',$ch).'_'.$sid.'_'.$ar;
		$mi->pathforbx($sid);
		if(!$mi->wasCached()){
			if(!empty($ch)){
				$mi->checkSz($ch[0], $ch[1]);
			}

			if($mi->ERROR['small_width']!='Y' && $mi->ERROR['small_height']!='Y'){
				$mi->imagecreatefrom();

				$mi->do_resize();

				$mi->imagesave();
				$mi->destroyme();
			} else{
				$mi->w_out = $mi->w;
				$mi->h_out = $mi->h;
				$mi->path = $mi->src;
				$mi->r_path = $mi->r_src;
			}
		}
		if($ar === 'this'){
			return $mi;
		} elseif($ar){
			$par = getimagesize($mi->path);
			$return = array(
				'SRC' => $mi->r_path,
				'WIDTH' => $par[0],
				'HEIGHT' => $par[1],
			);
		} else{
			$return = $mi->r_path;
		}
		unset($mi);
		return $return;
	}

	/**
	 * То-же что и Resize, но возвращает массив
	 */
	public static function AResize($f, $w, $h=false, $m=self::M_CROP, $ch=array(true, true), $sid=false, $me=false){
		return self::Resize($f, $w, $h, $m, $ch, $sid, true);
	}

	/**
	 * Возвращает HTML с картинкой пропорщионально уменьшеной и вмещенной в дивку по центру с нужной шириной и высотой и overflow:hidden.
	 * При движении мыши в такой дивке картинка ездит для отображения скрытых частей.
	 * Для такого поведения необходимо задавать и ШИРИНУ И ВЫСОТУ
	 * @param $f Картинка (rel_p|abs_p|bitrix_id)
	 * @param $w Ширина
	 * @param bool $h Высота
	 * @param $alt='' атрибут ALT картинки
	 * @param $title=$alt атрибут TITLE картинки
	 * @param array $ch=array(false, false) Проверять array(ширину, высоту) входящей картинки<br> чтоб не увеличивать маленькую.
	 * @param bool $sid= false Идентификатор сайта (указывается при многосайтовости)
	 * @return (HTML & JS) код для вставки.
	 */
	public static function ResizeWithHide($f, $w, $h=false, $alt='', $title=false, $ch=array(true, true), $sid=false){
		$mg = self::AResize($f, $w, $h, self::M_PROPORTIONAL, $ch, $sid, true, 'this');
		ob_start();?>
			<div class="_img_moover_" style="position:relative; width:<?=$w?>px; height:<?=$h?>px; overflow:hidden;">
				<div style="position:absolute; left:0; top:0; width:<?=$mg->w_out?>px; height:<?=$mg->h_out?>px;">
					<img src="<?=$mg->r_path?>" alt="<?=$alt?>" title="<?=($title)?$title:$alt?>" style="position:absolute; left:<?=-(($mg->w_out - $w)/2)?>px; top:<?=-(($mg->h_out - $h)/2)?>px; width:<?=$mg->w_out?>px; height:<?=$mg->h_out?>px;">
				</div>
			</div>
		<?$out = ob_get_contents();
		ob_end_clean();

		if(!self::$scripts_putted){
			ob_start();?>
			<script type="text/javascript">
				$(function(){
					$('._img_moover_ img').each(function(){
						var pos = $(this).position();
						$(this).data({
							left: pos.left,
							top: pos.top
						});
					});
					$('._img_moover_').off('mousemove mouseout');
					$('._img_moover_').on({
						mousemove: function(e){
							$(this).data('move', true);
							var th = $(this);
							var img = th.find('img');
							var imgd = img.data();
							var pos = th.position();
							var X = e.pageX - pos.left;
							var Y = e.pageY - pos.top;
							var left = ((X*100)/th.width()/2);
							var top = ((Y*100)/th.height()/2);

							if(imgd.top < 0){
								img.css('top', '-'+top+'%');
							}
							if(imgd.left < 0){
								img.css('left', '-'+left+'%');
							}
						},
						mouseout: function(){
							var me = $(this);
							$(this).data('move', false);
							setTimeout(function(){
								if(!me.data('move')){
									var im = me.find('img');
									var dt = im.data();
									me.find('img').animate({
										left: (dt.left*100)/im.width()+'%',
										top: (dt.top*100)/im.height()+'%'
									}, '100');
								}
							}, 300);
						}
					});
				});
			</script>
			<?$HEAD_STR = ob_get_contents();
			ob_end_clean();

			if(isset($APPLICATION)){
				$APPLICATION->AddHeadString($HEAD_STR, true);
			} else{
				$out = $out.$HEAD_STR;
			}
			self::$scripts_putted=true;
		}
		return $out;
	}

	/**
	 * Накладывает изображение $f на изображение $to
	 * @param $f Изображение(rel_p|abs_p|bitrix_id) на которое будет накладываться.
	 * @param $to Изображение которое будет накладываться(rel_p|abs_p|bitrix_id)
	 * @param (bool|array) $p= false - центр Позиция наложения<br>
	 * (str)lt: left top<br>
	 * (str)lb: left bottom<br>
	 * (str)rt: right top<br>
	 * (str)rb: right bottom<br>
	 * array($x, $y) - массив со значениями в пикселях для отступов слева и сверху. Допускаются процентные данные.<br>
	 * array('rb' => array(10, 10)) - массив значений в пикселях, rb - отталкиваясь, соответственно, справа и снизу
	 * @param (bool) $sid= false Идентификатор сайта (указывается при многосайтовости)
	 * @param (bool) $ar= false Возвращать ли массив или строку.
	 * @return Путь к картинке
	 */
	public static function Overlay($f, $to, $p=false, $sid=false, $ar=false){
		$mi = new self();
		$mi->load($f);
		if(is_array($p)){
			$po = str_replace('%', '~', (is_array($p[key($p)]))?key($p).'('.implode('-',$p[key($p)]).')':implode('_',$p));
		} else{
			$po = $p;
		}
		if($po==''){
			$po = 'center';
		}
		$mi->postfix = 	md5($f.$to).'_'.(string)$po.'_'.(string)$sid;
		$mi->pathforbx($sid);
		if(!$mi->wasCached()){
			$mi->imagecreatefrom();

			$mi->do_overlay($to, $p);

			$mi->imagesave();
			$mi->destroyme();
		}
		if($ar){
			$par = getimagesize($mi->path);
			$return = array(
				'SRC' => $mi->r_path,
				'WIDTH' => $par[0],
				'HEIGHT' => $par[1],
			);
		} else{
			$return = $mi->r_path;
		}
		unset($mi);
		return $return;
	}

	/**
	 * Ресайзит изображение
	 * @param $f Картинка (rel_p|abs_p|bitrix_id) на которую будет накладываться
	 * @param $to Картинка (rel_p|abs_p|bitrix_id) которая будет накладываться
	 * @param bool $pos= false - центр. Позиция наложения<br>
	 * (str)lt: left top<br>
	 * (str)lb: left bottom<br>
	 * (str)rt: right top<br>
	 * (str)rb: right bottom<br>
	 * array($x, $y) - массив со значениями в пикселях для отступов слева и сверху. Допускаются процентные данные.<br>
	 * array('rb' => array(10, 10)) - массив значений в пикселях, rb - отталкиваясь, соответственно, справа и снизу
	 * @param $w Ширина
	 * @param bool $h Высота
	 * @param string $m = CROP Метод трансвформации.( CROP | CROP_TOP | FULL | STRETCH )<br>
	 * Можно задать свой новый метод <br>создав в классе новый метод <br>с названием - method[МЕТОД_ТРАНСФОРМАЦИИ] - methodMYMETHOD
	 * @param array $ch Проверять array(ширину, высоту) входящей картинки<br> чтоб не увеличивать маленькую.
	 * @param bool $sid = false Идентификатор сайта (указывается при многосайтовости)
	 * @param bool $ar = false Возврацать массив или строку. По умолчанию строку
	 * @return (array|str) Путь к картинке или массив (шир, выс, путь)
	 */
	public static function ResizeOverlay($f, $to, $pos, $w, $h=false, $m=self::M_CROP, $ch=array(true, true), $sid=false, $ar=false){
		$mi = new self();
		$mi->load($f);
		$mi->w_out = $w;
		$mi->h_out = $h;
		$mi->method = $m;
		if(is_array($pos)){
			$po = str_replace('%', '~', (is_array($pos[key($pos)]))?key($pos).'('.implode('-',$pos[key($pos)]).')':implode('_',$pos));
		} else{
			$po = $pos;
		}
		if($po==''){
			$po = 'center';
		}
		$mi->postfix = '_'.md5($f.$to).'_'.(string)$po.'_'.(string)$w.'_'.(string)$h.'_'.(string)$m.'_'.implode('-',$ch).'_'.(string)$sid.'_'.(string)$ar;
		$mi->pathforbx($sid);
		if(!$mi->wasCached()){
			if($ch){
				$mi->checkSz($ch[0], $ch[1]);
			}
			if($mi->ERROR['small_width']!='Y' && $mi->ERROR['small_height']!='Y'){
				$mi->imagecreatefrom();

				$mi->do_resize();
				$mi->do_overlay($to, $pos);

				$mi->imagesave();
				$mi->destroyme();
			}
		}
		if($ar){
			$par = getimagesize($mi->path);
			$return = array(
				'SRC' => $mi->r_path,
				'WIDTH' => $par[0],
				'HEIGHT' => $par[1],
			);
		} else{
			$return = $mi->r_path;
		}
		unset($mi);
		return $return;
	}

}
?>
