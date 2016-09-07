<?
class CConsole{
	/**
	 * console.log()
	 */
	const LOG = '>>log';
	/**
	 * console.log()
	 */
	const CLEAR = '>>clear';
	/**
	 * console.info()
	 */
	const INFO = '>>info';
	/**
	 * console.warn()
	 */
	const WARN = '>>warn';
	/**
	 * console.error()
	 */
	const ERROR = '>>error';
	/**
	 * console.dir()
	 */
	const DIR = '>>dir';
	/**
	 * console.dirxml()
	 */
	const DIRXML = '>>dirxml';
	/**
	 * console.group()
	 */
	const GROUP = '>>group';
	/**
	 * console.groupCollapsed()
	 */
	const GROUP_COLLAPSED = '>>groupCollapsed';
	/**
	 * console.groupEnd()
	 */
	const GROUP_END = '>>groupEnd';
	/**
	 * Максимальная глубина вложенности
	 */
	const MAX_DEPTH_LEVEL = 10;
	/**
	 * @var bool Выводить только для админа
	 */
	static $admin_only = true;
	/**
	 * Отслеживать файл и строку в которых выводится сообщение.
	 */
	static $TRACE = true;
	/**
	 * @var array Массив для набора всех таймеров
	 */
	static $TIMERS = array();
	/**
	 * @var bool Был ли выведен dummy-текст
	 */
	static $DUMMY = false;
	/**
	 * @var int Посчитывает сколько раз была очищена конслоь
	 */
	static $clear_cnt = 0;

	protected function dummy(){
		if(!self::$DUMMY){
			global $APPLICATION;
			$str = '<script type="text/javascript">try{console.log("*********************************************************************************\n* \\\ \\\/ /                                                                        *\n*  \\\/\\\/ Как только ты пойдешь своей дорогой, тебе откроются все сокровища мира. *\n*********************************************************************************");}catch(e){}</script>';
			if(isset($APPLICATION)){
				$APPLICATION->AddHeadString($str, true);
			} else{
				echo $str;
			}
			self::$DUMMY=true;
		}
	}

	/**
	 * @protected Основной метод вывода в консоль.
	 * @param string $m - Используемый метод вывода в консоль. Константа класса CConsole
	 * @param array $args - Параметры метода
	 * @param $prev_code{string} - Предварительный js код.
	 */
	protected function out($m=self::LOG, $args=array(), $prev_code=''){
		global $USER;
		global $APPLICATION;
		if(self::$admin_only && isset($USER) && !$USER->IsAdmin()){
			self::dummy();
			return;
		}
		if($m[0]=='>' && $m[1]=='>'){
			$m = str_replace('>>', '', $m);
		} else{
			$m = 'log';
		}
		foreach($args as $k=>$a){
			if(!isset($a)){
				$a = '';
			}
			if(is_object($a) || is_array($a)){
				$a = self::encoder($a);
			} elseif(is_string($a) && '>>'.$m != self::DIR && '>>'.$m != self::DIRXML){
				$a = '"'.str_replace('"',"'",$a).'"';
			} elseif($a === true){
				$a = 'true';
			} elseif($a === false){
				$a = 'false';
			}
			$args[ $k ] = $a;
		}
		$STR = '<script type="text/javascript">try{'.$prev_code.' console.'.$m.'('.implode(', ', $args).');}catch(e){}</script>';
		if(self::$TRACE){
			$back_trace_ar = debug_backtrace();
			$STR .= '<script type="text/javascript">try{console.log("FILE: '.str_replace("\\", "/", $back_trace_ar[1]["file"]).'  LINE: '.$back_trace_ar[1]['line'].'");}catch(e){}</script>';
		}
		if(isset($APPLICATION)){
			$APPLICATION->AddHeadString($STR, true);
		} else{
			echo $STR;
		}
		self::$admin_only = true;
	}

	public function encoder($arr){
		if(function_exists('json_encode')) return json_encode($arr); //Не паримся если есть json_encode (PHP >= 5.2.0)
		/*if(method_exists('CUtil','PhpToJSObject')){
			return CUtil::PhpToJSObject($arr, false);
		}*/
		$is_ob = is_object($arr);
		if($is_ob){
			$arr = (array)$arr;
		}
		$parts = array();
		$is_list = false;

		// Узнаем числовой ли это массив
		$keys = array_keys($arr);
		$max_length = count($arr)-1;
		if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {// если первый ключ равен 0, а последний ключ длина - 1
			$is_list = true;
			for($i=0; $i<count($keys); $i++) { //если каждый ключ соответствует своей позиции
				if($i != $keys[$i]) { // если позиция ключа не верная
					$is_list = false; // то массив ассоциативный
					break;
				}
			}
		}
		foreach($arr as $key=>$value) {
			if(is_array($value) || is_object($value)) { // если массив
				if($is_list) $parts[] = self::encoder($value); /* :РЕКУРСИЯ: */
				else $parts[] = '"' . $key . '":' . self::encoder($value); /* :РЕКУРСИЯ: */
			} else {
				$str = '';
				if(!$is_list) $str = '"' . $key . '":';

				// разные типы данных
				if(is_numeric($value)) $str .= $value; // числа
				elseif($value === false) $str .= 'false'; // булев
				elseif($value === true) $str .= 'true'; // булев
				elseif(!$value && $is_ob) $str .= 'null'; // незаполненое свойство объекта
				else $str .= '"' . addslashes($value) . '"'; // другие
				// TODO: Можно еще чего нибудь добавить

				$parts[] = $str;
			}
		}
		$json = implode(',',$parts);

		if($is_list) return '[' . $json . ']';// Возвращаю номерной массив JSON
		return '{' . $json . '}';// Возвращаю ассоциативный массив JSON
	}

	/**
	 * Очищает консоль.
	 */
	static function clear(){
		$args = func_get_args();
		self::out(self::CLEAR, $args);
		self::$clear_cnt++;
		$str = 'очищено '.self::$clear_cnt.' раз';
		self::out(self::LOG, array($str));
	}

	/**
	 * Выводит сообщение в консоль.
	 */
	static function log(){
		$args = func_get_args();
		self::out(self::LOG, $args);
	}

	/**
	 * Выводит сообщение, типа информация, в консоль
	 */
	static function info(){
		$args = func_get_args();
		self::out(self::INFO, $args);
	}

	/**
	 * Выводит сообщение, типа предупреждение, в консоль
	 */
	static function warn(){
		$args = func_get_args();
		self::out(self::WARN, $args);
	}

	/**
	 * Выводит сообщение, типа ошибка, в консоль
	 */
	static function error(){
		$args = func_get_args();
		self::out(self::ERROR, $args);
	}


	/**
	 * Выводит html дерево в консоль
	 * @param $html{string} Строка сформированного html дерева
	 */
	static function dir($html){
		$prev = 'var co = document.createElement("div");co.innerHTML = "'.$html.'";';
		$ar[] = 'co';
		self::out(self::DIR, $ar, $prev);
	}

	/**
	 * Выводит html дерево в виде объекта в консоль
	 * @param $html{string} Строка сформированного html дерева
	 */
	static function dirxml($html){
		$prev = 'var co = document.createElement("div");co.innerHTML = "'.$html.'";';
		$ar[] = 'co';
		self::out(self::DIRXML, $ar, $prev);
	}

	/**
	 * Создает группу в консоли.
	 * @param $name{string} Имя группы
	 */
	static function group($name){
		$ar[] = $name;
		self::out(self::GROUP, $ar);
	}

	/**
	 * Создает закрытую группу в консоли.
	 * @param $name{string} Имя группы
	 */
	static function groupCollapsed($name){
		$ar[] = $name;
		self::out(self::GROUP_COLLAPSED, $ar);
	}

	/**
	 * Закрывает последнюю открытую группу в консоли.
	 */
	static function groupEnd(){
		self::out(self::GROUP_END);
	}

	/**
	 * Выводит в консоль сообщение, с PHP типом параметра
	 * [type]: [param1] | [type]: [param2]
	 */
	static function dump(){
		$args = func_get_args();
		foreach($args as $k => $a){
			if($k>0){
				$ar[] = '|';
			}
			$ar[] = gettype($a).':';
			$ar[] = $a;
		}
		self::out(self::LOG, $ar);
	}

	/**
	 * Начинает отсчет времени с именем $name.
	 * @param $name{bool|string} Имя таймера
	 */
	static function time($name=false){
		if(!$name){
			$name = sizeof(self::$TIMERS);
		}
		self::$TIMERS[ $name ] = microtime(true);
	}

	/**
	 * Останавливает отсчет с именем $name и выводит таймер.
	 * @param $name{bool|string} Имя таймера
	 */
	static function timeEnd($name=false){
		if(!$name){
			$name = sizeof(self::$TIMERS);
		}
		$t = microtime(true) - self::$TIMERS[ $name ];
		self::out(self::LOG, array($name.':', $t));
		return $t;
	}

	/**
	 * Выводит в браузер $var, при наличии jQuery появляются всякие вкусности.
	 * @param $var Выводимая переменная
	 * @param bool $dump Выводить ли тип и размер переменной <br/>
	 * Оставльные параметры не устанавливать, они используются для служебных целей.
	 * @return string
	 */
	static function prind($var, $dump=false, $return=false, $depth=0){
		global $USER;
		global $APPLICATION;
		global $DEBUG_STR_ADDED;
		if(self::$admin_only && isset($USER) && !$USER->IsAdmin() && (!$return && $depth == 0)){
			return;
		}
		if($depth < self::MAX_DEPTH_LEVEL){
			$type = gettype($var);
			$ret = '';
			$t = gettype($var);
			$cl = (is_object($var))?' '.get_class($var):'';
			if($dump){
				if(is_object($var) || is_array($var)){
					$sz = (sizeof((array)$var)>0)?' '.sizeof((array)$var):'';
				} else{
					$sz = (strlen($var)>0)?' '.strlen($var):'';
				}
				$suf = '<span class="suff">'. $t . $cl . $sz .'</span>';
			} else{
				$suf = '';
			}
			if($type=='array' || $type=='object'){
				$ret .= $suf;
				$ret .= '<ul class="'.$type.'">';
				foreach($var as $k => $v){
					$t = gettype($v);
					$empty = (empty($v))?' empty':'';
					$ret .= '<li class="'. $t.$empty .'">';
					$ret = $ret.'<span class="k">'.$k.'</span>:';
					$kd = $depth+1;
					$ret .= self::prind($v, $dump, true, $kd);
					$ret .= '</li>';
				}
				$ret .= '</ul>';
			} else{
				if(is_string($var)){ $var = '"'.$var.'"';}
				elseif($var === true){ $var = 'true';}
				elseif($var === false){ $var = 'false';}
				$ret = $ret.$suf.'<span class="v">'.$var.'</span>';
			}
		}
		$out = '<div class="_debugger">'.$ret.'</div>';
		if(!$return && $depth == 0){
			self::$admin_only = true;
			ob_start();?>
				<style type='text/css'>
					._debugger {position: relative; width:95%; outline:1px solid red; background:#fff; padding:30px 10px 15px 25px; z-index:1000000;}
					._debugger > .suff {position: absolute; left: 0; top: 0; display: block; padding: 3px 10px; padding-left: 28px; line-height:19px;}
					._debugger ul {list-style:none; margin:0!important; padding:0!important; color:#000; padding:0 0 0 30px!important; margin-left:-10px!important;}
					._debugger > ul {border-left:0 !important; padding:10px!important; margin:0!important; padding:0!important;}
					._debugger ul li { line-height:150%; background:none !important; margin:0!important; padding:0!important;}
					._debugger ul li .k { padding-right:2px; color:#8A0F86;}
					._debugger ul li .suff {border: 1px solid #666; border-top: 0; border-bottom: 0; padding: 0px 2px; margin: 0 2px 0 4px; border-radius: 3px; color: #b3b3b3; font-size: 0.9em;}
					._debugger .suff {color: #666; border: 1px solid red; border-left:0; border-top:0;}
					._debugger ul li .v { padding-left:5px;}
					._debugger ul li .closure {padding-left: 10px; color: #FF7F00;}
					._debugger ul .boolean .v { color:#c92308;}
					._debugger ul .integer .v, ._debugger ul .double .v { color:#2915a8;}
					._debugger .array, ._debugger .object { position:relative;}
					._debugger .array > span, ._debugger .object > span { cursor:pointer;}
					._debugger ul.array {border-left:1px solid #ccc}
					._debugger ul.object {border-left:1px solid #0046ff}
					._debugger .toggler {position: absolute; left: -15px; top: 6px; width: 0; height: 0; border-style: solid;  border-color:#999 transparent transparent transparent; border-width: 10px 5px 10px 5px; cursor:pointer;}
					._debugger .col { top:5px; left:-14px; border-color: transparent transparent transparent #999; border-width: 5px 10px 5px 10px;}
					._debugger ._dbg_toggler {position: absolute; left: 3px; top: 3px; font-size: 20px; font-weight: bold; border: 1px solid #CCC; width: 10px; height: 8px; line-height: 3px; padding: 4px; border-radius: 3px; cursor: pointer; z-index:10;}
					._debugger ._dbg_toggler._tt {font-size:11px!important;}
				</style>

				<script type='text/javascript'>
				var super_puper_was_started = false;
				function startMe(){
					setTimeout(function(){
						try{
							if($){
								super_puper_was_started = true;
								$(function(){
									$('._debugger').each(function(){
										$(this).prepend('<div class="_dbg_toggler" title="Свернуть дебаггер">&raquo;</div>');
										$(this).data('height', $(this).height())/*.css('height', $(this).height()+'px')*/.css('overflow', 'hidden');
									});
									$('._debugger ._dbg_toggler').click(function(e){
										if(!$(this).hasClass('_tt')){
											var me = $(this);
											if(e.ctrlKey){
												me = me.add($(this).parents('._debugger').siblings().find('._dbg_toggler'));
											}
											$(me).html('&hellip;').addClass('_tt');
											$(me).parents('._debugger').animate({
												height: '10px'
											}, 100);
										} else{
											var me = $(this);
											if(e.ctrlKey){
												me = me.add($(this).parents('._debugger').siblings().find('._dbg_toggler'));
											}
											$(me).html('&raquo;').removeClass('_tt');
											$(me).parents('._debugger').animate({
												height: $(this).parents('._debugger').data('height')+'px'
											}, 100, function(){
												$(this).css('height', 'auto');
											});
										}
									});

									$('._debugger li.array, ._debugger li.object').not('.empty').prepend('<div class="toggler"></div>').find('.toggler').attr('title', 'Cкрыть');
									$('._debugger li.array:not(":visible") .toggler, ._debugger li.object:not(":visible") .toggler').addClass('col');
									$('._debugger li.array .toggler, ._debugger li.array > span, ' +
									'._debugger li.object .toggler, ._debugger li.object > span').click(function(e){
										var trig = $(this).parent().children('.toggler');
										if(e.ctrlKey){
											trig = trig.add(trig.parent('li').siblings(':has("ul")').children('.toggler'));
										}

										if($(this).siblings('ul').is(':visible')){
											trig.siblings('ul').slideUp('fast');
											trig.addClass('col').attr('title', 'Раскрыть');
										} else{
											trig.siblings('ul').slideDown('fast');
											trig.removeClass('col').attr('title', 'Скрыть');
										}
									});

									$('.k').click(function(e){
										var th = $(this);
										var me = $(this);
										if(e.ctrlKey){
											th = $(th).add($(this).parent().parent().find('.k'));
										}

										$(th).each(function(){
											var lh = $(this).siblings('.v').css('line-height') ? $(this).siblings('.v').css('line-height') : '0' ;

											if(lh.split('px')[0] >= Number($(this).siblings('.v').height())){
												return true;
											}
											if($(me).hasClass('hidden')){
												$(this).parent().children('.v').fadeIn('fast');
												$(this).siblings('.closure').fadeOut('fast').remove();
											} else{
												$(this).parent().children('.v').fadeOut('fast');
												if($(this).siblings('.closure').length < 1){
													$(this).parent().append('<span class="closure">"  ...  "</span>');
												}
											}
										});
										$(th).each(function(){
											if($(this).hasClass('hidden')){
												$(this).removeClass('hidden');
											} else{
												$(this).addClass('hidden');
											}
										});
									});
								});
							}
						} catch(e){};
						if(!super_puper_was_started){
							startMe();
						}
					}, 0);
				}
				startMe();
				</script>
			<?
			$HEAD_STR = ob_get_contents();
			ob_end_clean();
			if(isset($APPLICATION)){
				$APPLICATION->AddHeadString($HEAD_STR, true);
			} elseif(!$DEBUG_STR_ADDED){
				$DEBUG_STR_ADDED = true;
				$out = $HEAD_STR.$out;
			}
		}
		if($return){
			return $ret;
		} else{
			echo $out;
		}
	}
}




?>
