<?
/**
 * класс для работы с формами
 * @author Сереженька WEXPERT
 * @uses CFTypes
 * @uses CFValidators
 * @link Z:\server\WEXPERT_LIB\classes\cform.php
 */
class CFormBuilder {
	public $cfg = array();
	public $raw = array();
	public $js = array();
	public $errors = array();
	public $valid = true;
	public $Ready = false;
	public $Submitted = false;
	static $jsHeadShowed = false;

	/**
	 * Конструктор. Инициализирует $this->cfg
	 * @param array $cfg Массив конфигурации формы
	 * @param array|false $data_from Массив данных формы
	 */
	public function  __construct($cfg=array(), $data_from=false){
		if(empty($cfg) || !is_array($cfg)){
			$this->errors['no_cfg'] = $GLOBALS['MESS']['CFB_no_cfg'];
		} else{
			$this->cfg = $cfg;
			foreach($this->cfg['f'] as $k => &$c){
				if(!$c['name']){
					$c['name'] = $k;
				}
				preg_match('/^\[?([\w]+)?\]?((\[?([\w]+)?\]?)+)$/i', $c['name'], $m);
				if($this->cfg['name']){
					$c['full_name'] = $this->cfg['name'].'['.$m[1].']'.$m[2];
				} else{
					$c['full_name'] = $m[1].$m[2];
				}
				$mt = '';
				if($c['multiple']){
					$mt = '[]';
				}
				if($this->cfg['name']){
					$c['full_name'] = $this->cfg['name'].'['.$c['name'].']'.$mt;
				} else{
					$c['full_name'] = $c['name'].$mt;
				}
			}
			unset($c);
		}

		if($_SESSION[$this->cfg['name']]['READY_STATE'] == 'Y'){
			$this->Ready = true;
		}
		if($data_from){
			$this->copyFrom($data_from);
		}
	}

	/**
	 * Устанавливает флаг готовности. Была ли отправлена форма.
	 * @param bool $redirect=false Очищать ли посты.
	 */
	public function setReady($redirect=false){
		$_SESSION[$this->cfg['name']]['READY_STATE'] = 'Y';
		$this->Ready = true;
		if($redirect && function_exists('LocalRedirect')){
			LocalRedirect();
		}
	}

	/**
	 * Экранирует элементы массива
	 * @param array|string $val Переменная.
	 * @param bool $orig=false Возврощать ли оригинальные элементы с '~'.
	 * @return array
	 */
	public function escape($val, $orig=false){
		$res = false;
		if(is_array($val)){
			foreach ($val as $k=>$v){
				if(is_array($v)){
					$o = ($orig)?true:false;
					$res[$k] = self::escape($v, $o);
				} else {
					if(function_exists('htmlspecialcharsEx')){
						$res[$k] = htmlspecialcharsEx($v);
					} else{
						$res[$k] = htmlspecialchars($v);
					}
					if($orig) $res['~'.$k] = $v;
				}
			}
			return $res;
		} else{
			if(function_exists('htmlspecialcharsEx')){
				return htmlspecialcharsEx($val);
			} else{
				return htmlspecialchars($val);
			}
		}
	}

	/**
	 * Копирует данные формы в $this->raw, и устанавливает $this->cfg[ $n ]['value']
	 * @param string|array $from Строка ключа в массиве $GLOBALS (напр. "REQUEST"), или массив (напр. $_REQUEST)
	 */
	public function copyFrom($from){
		if(is_string($from)){
			if($this->cfg['name']){
				$fls = $_FILES[ $this->cfg['name'] ];
				$dt = $this->escape($GLOBALS[ $from ][ $this->cfg['name'] ]);
			} else{
				$fls = $_FILES;
				$dt = $this->escape($GLOBALS[ $from ]);
			}
		} else{
			$dt = $this->escape($from);
		}
		if(!$dt){
			return false;
		}

		foreach($this->cfg['f'] as $k=>$v){
			unset($this->cfg['f'][$k]['value']);
			$this->cfg['f'][$k]['value'] = $dt[ $k ];
			if($v['type'] == 'file'){
				if($v['multiple']){
					foreach($fls['error'][ $v['name'] ] as $ki=>$vi){
						if($vi <= 0){
							$not_set_error = true;
						}
						if($vi >= 0){
							$far[] = $vi;
						}
						$kark[ $ki ] = array(
							'name' => $fls['name'][ $v['name'] ][ $ki ],
							'type' => $fls['type'][ $v['name'] ][ $ki ],
							'tmp_name' => $fls['tmp_name'][ $v['name'] ][ $ki ],
							'error' => $fls['error'][ $v['name'] ][ $ki ],
							'size' => $fls['size'][ $v['name'] ][ $ki ],
						);
					}
					if(!$not_set_error && $v['validate']){
						foreach($far as $f){
							$ob = new CFValidators($v);
							$ob->fileError($f);
							$ob->put($this->cfg['f'][$k]);
						}
					}
					$this->cfg['f'][$k]['value'] = $kark;
				} else{
					if($fls['error'][ $v['name'] ] >= 0){
						$ob = new CFValidators($v);
						$ob->fileError($fls['error'][ $v['name'] ]);
						$ob->put($this->cfg['f'][$k]);
					}
					$this->cfg['f'][$k]['value'] = array(
						'name' => $fls['name'][ $v['name'] ],
						'type' => $fls['type'][ $v['name'] ],
						'tmp_name' => $fls['tmp_name'][ $v['name'] ],
						'error' => $fls['error'][ $v['name'] ],
						'size' => $fls['size'][ $v['name'] ],
					);
				}
			}
		}

		$this->raw = $dt;

		if(empty($this->raw) && $this->Submitted){
			$this->errors['no_data'] = $GLOBALS['MESS']['CFB_no_data'];
		}
		if(!empty($this->raw)){
			$this->Submitted = true;
		}
	}

	/**
	 * Валидирует все поля, устанавливает $this->valid
	 * @return bool. Возвращает фалг присутствия ошибок
	 */
	public function Validate(){
		foreach($this->cfg['f'] as &$ar){
			if(!$ar['validate']) continue;
			if(is_array($ar['validate'])){
				foreach($ar['validate'] as $k => $v){
					$this->setVldtr($ar, $v, $k);
				}
			} else{
				$this->setVldtr($ar);
			}
			if(!empty($ar['errors'])){
				$this->valid = false;
			}
		}
		unset($ar);
		return $this->valid;
	}

	/**
	 * Устанавливает валидатор
	 * @param $ar Массив параметров конфигурации
	 * @param bool $v Значение текущего валидатора, если валидаторов массив
	 */
	private function setVldtr(&$ar, $v=false, $key=false){
		$ob = $this->getOb($ar, $cl, $mt, $args, $v, $key);
		if(!empty($args)){
			$ob->$mt($args);
		} else{
			$ob->$mt();

		}
		$ob->put($ar);
	}

	/**
	 * Возвращает объект, устанавливает имя класса $cl, устанавливает имя метода $mt, из строки типа ("Class::method")
	 * @param array $fld конфигурация поля
	 * @param string $cl сюда устанавливается имя класса
	 * @param $mt сюда устанавливается имя метода
	 * @param bool $v=false тип текущей валидации если в массиве $fld поле 'validate' является массивом
	 * @param bool $key=false тип текущей валидации если в массиве $fld поле 'validate' является массивом с подмассивом параметров
	 * @return object
	 */
	protected function getOb($fld, &$cl, &$mt, &$args, $v=false, $key=false){
		if(!$v){
			$v = $fld['validate'];
		}
		if(is_array($v)){
			list($cl, $mt) = explode('::', $key);
			$args = $v;
		} else{
			list($cl, $mt) = explode('::', $v);
		}
		if(!$cl || !$mt){
			return false;
		}
		return new $cl($fld);
	}

	/**
	 * Выводит label для поля с name'ом $code, без тега <label>
	 * @param $code Имя(симв. код) поля
	 * @return bool|string HTML код
	 */
	public function Name($code){
		if(!isset($this->cfg['f'][ $code ])){
			return false;
		}
		if($this->cfg['f'][ $code ]['validate']){
			$req = '<span class="req">*</span>';
		}
		return $this->cfg['f'][ $code ]['label'].$req;
	}

	/**
	 * Выводит label для поля с name'ом $code
	 * @param $code Имя(симв. код) поля
	 * @return bool|string HTML код label
	 */
	public function Label($code){
		$cl = $this->cfg['f'][ $code ]['class'];
		if(strlen($cl)>0)
			$class = "class='$cl'";
		$id = $this->cfg['f'][ $code ]['id'];
		$id = ($id)?' for="'.$id.'"':'';
		return '<label'.$id.' '.$class.'>'
				.$this->Name($code)
				.'</label>';
	}

	/**
	 * Выводит поле с кодом $code
	 * @param $code Имя(симв. код) поля
	 * @return bool HTML код поля
	 */
	public function Field($code){
		if(!isset($this->cfg['f'][ $code ])){
			return false;
		}
		$fld = $this->cfg['f'][ $code ];
		if(strpos($fld['type'], '::')!==false){
			$ar = explode('::', ($fld['type'])?$fld['type']:'CFTypes::text');
		} else{
			$ar = array('CFTypes', $fld['type']);
		}
		$ob = new $ar[0]($fld);
		if(!method_exists($ar[0],$ar[1])){
			$ar[1] = 'text';
		}
		$ob->$ar[1]();
		return $ob->output();
	}

	/**
	 * Выводит системные ошибки CFormBuilder'а
	 */
	public function myErrors(){
		if(!empty($this->errors)){
			echo '<span style="color:red;">'.implode('<br/>', $this->errors).'</span>';
		}
	}

	/**
	 * Выводит начало формы. <form ...>
	 */
	public function Start(){
		foreach($this->cfg['f'] as $f){
			if($f['type'] == 'file'){
				$this->cfg['enctype'] = 'multipart/form-data';
				break;
			}
		}
		$name = ($this->cfg['name'])?' name="'.$this->cfg['name'].'"':'';
		$method = ($this->cfg['method'])?' method="'.$this->cfg['method'].'"':'';
		$action = ($this->cfg['action'])?' action="'.$this->cfg['action'].'"':'';
		$enctype = ($this->cfg['enctype'])?' enctype="'.$this->cfg['enctype'].'"':'';

		echo '<form ' . $name . $method . $action . $enctype . ' >';
	}

	/**
	 * Выводит конец формы и скрипты. </form> <script ...>
	 */
	public function End(){
		unset($_SESSION[$this->cfg['name']]);
		$out = '</form><br/>';
		$out .= $this->putHeadScripts();
		$out .= $this->putScripts();
		echo $out;
	}

	/**
	 * Отображает ошибки заполнения формы
	 */
	public function ShowErrors(){
		$errors = array();
		foreach($this->cfg['f'] as  $f){
			if(empty($f['errors'])) continue;
			foreach($f['errors'] as $k=>$e){
				$id = str_replace('[','', str_replace('[','', $e));
				$id = $k;
				$errors[] = "<li id='".$id."'>".$e."</li>";
			}
		}
		if(!empty($errors)){
			echo '<ul class="errors">'.implode('', $errors).'</ul>';
		}
	}

	/**
	 * Возвращает скрипты с классами валидации. Проверяет на неповторение.
	 * @return string
	 */
	protected function putHeadScripts(){
		$out='';
		global $APPLICATION;
		if(!self::$jsHeadShowed){
			ob_start();?>
		<script type="text/javascript">
			if(CFValidators == undefined){
				var CFValidators = {};
			}
			CFValidators.set_error = function(th, cv){
				$th = $(th);
				var $frm = $th.parents('form');
				var fdt = $frm.data('settings');
				$th.addClass('fail');
				$frm.find('label[for='+$th.attr('id')+']').addClass('fail');

				if(fdt.js_error_list || fdt.js_error_tooltips){
//					var cv = $th.data('current_validator');
					var ers = $th.data('errors');
					var er = ers[cv[0]+'.'+cv[1]];
					var id = cv[0]+cv[1]+($th.attr('name')).replace('[','').replace(']','').replace('[]','');
				}
				if(fdt.js_error_list){
					var erul = $frm.find('ul.errors');
					if(!erul.length){
						$frm.prepend('<ul class="errors"></ul>');
						erul = $frm.find('ul.errors');
					}
					var li = '<li id="'+id+'">'+er+'</li>';
					
					if(!erul.find('#'+id).length){
						erul.append(li);
					} else{
						erul.find('#'+id).show();
					}
				}
				if(fdt.js_error_tooltips){
					var ttl = '';
					var erst = $th.data('er_title');
					if(erst == undefined){
						erst = {};
					}
					if(!erst[id]){
						erst[id] = er;
					}
					$th.data('er_title', erst);
					if(typeof erst == 'object'){
						for(var v in erst){
							if(erst[v])
								ttl = ttl+erst[v]+"\n";
						}
					}
					if(!$th.data('title')){
						$th.data('title', $th.attr('title'));
					}
					if(ttl.length){
						$th.attr('title', ttl);
					} else if($th.data('title')){
						$th.attr('title', $th.data('title'));
					}
				}
			};
			CFValidators.unset_error = function(th, cv){
				$th = $(th);
				var $frm = $th.parents('form');
				var fdt = $frm.data('settings');
				$th.add($frm.find('label[for='+$th.attr('id')+']')).removeClass('fail');

				if(fdt.js_error_list){
					var erul = $frm.find('ul.errors');
					if(erul.length){
						var cv = $th.data('current_validator');
						var id = cv[0]+cv[1]+($th.attr('name')).replace('[','').replace(']','').replace('[]','');
						erul.find('"#'+id+'"').hide();
					}
				}

				if(fdt.js_error_tooltips){
					var ttl = '';
//					var cv = $th.data('current_validator');
					var ers = $th.data('errors');
					var er = ers[cv[0]+'.'+cv[1]];
					var id = cv[0]+cv[1]+($th.attr('name')).replace('[','').replace(']','');

					var erst = $th.data('er_title');
					if(erst != undefined){
						if(erst[id] != undefined){
							erst[id] = false;
						}
					}
					$th.data('er_title', erst);
					if(typeof erst == 'object'){
						for(var v in erst){
							if(erst[v])
								ttl = ttl+erst[v]+"\n";
						}
					}

					if(ttl.length > 0){
						$th.attr('title', ttl);
					} else if($th.data('title')!=undefined){
						$th.attr('title', $th.data('title'));
					} else{
						$th.attr('title', '');
					}
				}
			};
			CFValidators.filled = function () {
				var me = this;
				$(this).parents('form').submit(function () {
					if($.trim($(me).val()).length <= 0){
						CFValidators.set_error(me, ['CFValidators', 'filled']);
						return false;
					} else{
						CFValidators.unset_error(me, ['CFValidators', 'filled']);
					}
				});
				return true;
			};
			CFValidators.mail = function () {
				var me = this;
				$(this).parents('form').submit(function () {
					if(/^[=_.0-9a-z+~-]+@(([-0-9a-z_]+\.)+)([a-z]{2,10})$/i.test($(this).val()) || $.trim($(me).val()).length <= 0){
						CFValidators.set_error(me, ['CFValidators', 'mail']);
						return false;
					} else{
						CFValidators.unset_error(me, ['CFValidators', 'mail']);
					}
				});
			};
			CFValidators.phone = function () {
				$(this).keypress(function (e) {
					var c = e.which;
					return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8 || c == 32 || c == 43 || c == 45 || c == 41 || c == 40;
				});
			};
			CFValidators.number = function () {
				$(this).keypress(function (e) {
					var c = e.which;
					return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8;
				});
			};

			(function ($) {
				$.fn.validate = function (method) {
					var argnts = arguments;
					return this.each(function(){
						if(typeof method[0] == 'object'){
							for(var i in method){
								if(window[ method[ i ][0] ][ method[ i ][1] ] !== undefined){
									var args = argnts;
									$(this).data('current_validator', method[ i ]).each(function(){
										window[ method[ i ][0] ][ method[ i ][1] ].apply(this, Array.prototype.slice.call(args[0], 0)[ i ]);
									});
								}
							}
							return this;
						} else if(typeof method[0] == 'string'){
							if(window[ method[0] ][ method[1] ] !== undefined){
								var args = argnts;
								$(this).data('current_validator', method).each(function(){
									window[ method[0] ][ method[1] ].apply(this, Array.prototype.slice.call(args[0], 1));
								});
							}
						} else{
							alert('<?=$GLOBALS['MESS']['CFB_error_js_validators']?>');
						}
					});
				};
			})(jQuery);
		</script>
		<?
			$put = ob_get_contents();
			ob_end_clean();
			$out .= $put;
			self::$jsHeadShowed = true;
		}

		if($APPLICATION && method_exists($APPLICATION, 'AddHeadString')){
			if(!$APPLICATION->AddHeadString($out, true)){
				return $out;
			}
		} else{
			return $out;
		}
	}

	/**
	 * Выводит скрипты текущей формы. Настройки формы и настройки валидирования.
	 * @return string
	 */
	protected function putScripts(){
		$out = '';
		$out .= "<script type='text/javascript'>\n$(function(){\n";
		if(!empty($this->cfg['settings'])){
			$set = '{';
			$c=0;foreach($this->cfg['settings'] as $ks=>$vs){
				if($c>0)
					$set .= ', ';
				$set .= "$ks:";
				$set .= "'$vs'";
			$c++;}
			$set .= '}';
			$ot = "$('form[name=" . $this->cfg['name'] . "]').data('settings', $set);\n";
			$out .= "\t";
			$out .= $ot;
		}
		foreach($this->cfg['f'] as $fld){
			if((!$fld['js_validators'] && isset($fld['js_validators'])) || !$this->cfg['settings']['js_validators'] || !isset($fld['validate'])) continue;
			$vdtr = array();
			$me = '[name="' . $fld['full_name'] . '"]';
			$ob = "$('form[name=" . $this->cfg['name'] . "] " . $me . "')";
			if(is_array($fld['validate'])){
				$t=array();
				foreach($fld['validate'] as $vld){
					$omm = $this->getOb($fld, $cl, $mt, $args, $vld);
					if($omm){
						$met = $mt.'_error';
						$er = $omm->$met();
						$vdtr[] = "'$cl.$mt':'".$er."'";
						if(!empty($args)){
							$t[] = "['$cl', '$mt', ['".implode(', ', $args)."']]";
						} else{
							$t[] = "['$cl', '$mt']";
						}
					}
				}
				if(!empty($t)){
					$out .= "\t";
					$out .= "$ob.data('errors', {".implode(', ', $vdtr)."}).validate([".implode(', ', $t)."]);\n";
				}
			} else{
				$omm = $this->getOb($fld, $cl, $mt, $args);
				if($omm){
					$met = $mt.'_error';
					$er = $omm->$met();
					$vdtr[] = "'$cl.$mt':'".$er."'";
					if(!empty($args)){
						$var = "['$cl', '$mt', ['".implode(', ', $args)."']]";
					} else{
						$var = "['$cl', '$mt']";
					}
					$t = "$ob.data('errors', {".implode(', ', $vdtr)."}).validate($var);\n";
					$out .= "\t";
					$out .= $t;
				}
			}
		};
		$out .= "});\n</script>";
		return $out;
	}
}

/**
 * класс для описания типов полей, для CFormBuilder
 * При добавлении нового типа необходимо добавить: <br>
 * 1. константу const type = 'CFTypes::type' <br>
 * 2. метод который устанавливает в $this->out HTML для нового типа поля
 */
class CFTypes{
	/**
	 * Тип поля textarea
	 */
	const textarea = 'CFTypes::textarea';
	/**
	 * Тип поля text
	 */
	const text = 'CFTypes::text';
	/**
	 * Тип поля select
	 */
	const select = 'CFTypes::select';
	/**
	 * Тип поля radio
	 */
	const radio = 'CFTypes::radio';
	/**
	 * Тип поля checkbox
	 */
	const checkbox = 'CFTypes::checkbox';
	/**
	 * Тип поля file
	 */
	const file = 'CFTypes::file';

	/**
	 * @var array $cfg Массив конфигурации текущего поля
	 */
	protected $cfg;
	/**
	 * @var string $out Строка в которую собирается HTML код описывающий вид поля
	 */
	protected $out;

	/**
	 * Конструктор. Устанавливает $this->cfg.
	 * @param $fld Массив конфигурации текущего поля
	 */
	public function  __construct($fld){
		$this->cfg = $fld;
	}

	/**
	 * Показывает поле типа text
	 */
	public function text(){
		$this->template('<input type="#type#" name="#full_name#" value="#value#" id="#id#" class="#class#" #attr# />');
	}

	/**
	 * Показывает поле типа textarea
	 */
	public function textarea(){
		$this->template('<textarea name="#full_name#" id="#id#" class="#class#" #attr# >#value#</textarea>');
	}

	/**
	 * Показывает поле типа select
	 */
	public function select(){
		$options = '';
		if($this->cfg['multiple']){
			$this->cfg['attr'] .= ' multiple="multiple"';
		}
		if(!empty($this->cfg['groups'])){
			foreach($this->cfg['groups'] as $gc=>$gn){
				$t = '<optgroup label="'.$gn.'" code="'.$gc.'" >';
				$options .= $t;
				foreach($this->cfg['options'] as $o){
					$op = array();
					if(!$o['name']){
						list($op['value'], $op['name'], $op['attr'], $op['group']) = $o;
					} else{
						$op = $o;
					}
					if($this->cfg['value'] == $op['value'] || in_array($op['value'], $this->cfg['value'])){
						$op['attr'] = 'selected';
					}
					if($op['group']!=$gc)continue;
					$options .= $this->varsReplacer('<option value="#value#" #attr# >#name#</option>', $op);
				}
				$options .= '</optgroup>';
			}
		} else{
			foreach($this->cfg['options'] as $o){
				if(!$o['name']){
					list($op['value'], $op['name'], $op['attr'], $op['group']) = $o;
				} else{
					$op = $o;
				}
				$options .= $this->varsReplacer('<option value="#value#" #attr# >#name#</option>', $op);
			}
		}

		$this->out = $this->varsReplacer('<select name="#full_name#" id="#id#" class="#class#" #attr# >', $this->cfg).$options.'</select>';
	}

	/**
	 * Заменяет в строке $html заглушки на соответствующие значения элементов массива $this->cfg, и добавляет все в $this->out. Например #var# замнеится на значение $this->cfg['var']
	 * @param bool $html=false Форматированная строка
	 */
	public function template($html=false){
		if(!$html){
			$html = $this->cfg['template'];
		}
		$this->out = $this->varsReplacer($html, $this->cfg);
	}

	/**
	 * Заменяет переменные в тексте
	 * @param $txt Текст в котором необходимо произвести замену #VAR#
	 * @param $arr Массив замен array('MAIL'=>'mail@mail.ru')
	 * @param $r Строка замены для пустых совпадений (Если в массиве замен не указана замена на найденную переменную)
	 * @return void Замененный текст
	 */
	static public function varsReplacer($txt, $arr, $r=''){
		preg_match_all('/#([a-zA-Z_]+)#/', $txt, $m);
		if(!empty($m[1])){
			foreach($m[1] as $v){
				$search[] = '#'.$v.'#';
				$replace[] = isset($arr[ $v ])?$arr[ $v ]:$r;
			}
			$ret = str_replace($search, $replace, $txt);
			if(strpos(str_replace(' ', '', $ret), '=""')!==false){
				$ret = preg_replace('/(^value)+\s?=\s?["|\']\s?["|\']/', '', $ret);
			}
			return $ret;
		}
		return $txt;
	}

	/**
	 * Возвращает HTML строку описывающую текущее поле
	 * @return string HTML строка описывающая текущее поле
	 */
	public function output(){
		return $this->out;
	}
}

/**
 * класс валидаторов для CFormBuilder
 * для добавления нового валидатора необходимо добавить:<br>
 * 1. Константу vldtr = 'CFValidators::vldtr'<br>
 * 2. Метод vldtr который реализует логику новго валидатора.<br>
 * 3. Метод 'vldtr'.'_error' который вернет текст ошибки валидации<br>
 * Внутри методов будет доступен массив $this->cfg конфигурации валидируемого поля, а также метод $this->set_error() - который необходимо вызвать в случае ошибки валидации
 */
class CFValidators{

	/**
	 * Валидатор filled (Заполненость, непустота)
	 */
	const filled = 'CFValidators::filled';
	/**
	 * Валидатор mail (Правильность e-mail'а)
	 */
	const mail = 'CFValidators::mail';
	/**
	 * Валидатор phone (дост.символы +0-9(0-9)0-9) можно и без + и без скобокб а так же с "-" но не сразу после скобки
	 */
	const phone = 'CFValidators::phone';
	/**
	 * Валидатор number (int или строка в виде int)
	 */
	const number = 'CFValidators::number';
	/**
	 * Валидатор filesize(bytes) (1Mb, 10Kb) <br>
	 * CFValidators::filesize => array('10Mb')
	 */
	const filesize = 'CFValidators::filesize';


	/**
	 * @var array Массив конфигурации валидируемого поля
	 */
	protected $cfg;
	/**
	 * @var array Коды ошибок $_FILES
	 */
	protected $ferrors = array();

	/**
	 * Конструктор. Инициализирует $this->cfg
	 * @param array $fld Массив конфигурации валидируемого поля
	 */
	public function __construct($fld){
		$this->cfg = $fld;
		$this->ferrors = array(
			'1' => $GLOBALS['MESS']['CFB_upper_size'],
			'2' => $GLOBALS['MESS']['CFB_upper_max_f_size'],
			'3' => $GLOBALS['MESS']['CFB_was_giving_partly'],
			'4' => $GLOBALS['MESS']['CFB_was_not_dnld'],
		);
	}

	/**
	 * Валидатор filled (Заполненость, непустота)
	 */
	public function filled(){
		$v = $this->cfg['value'];
		if((is_array($v) && empty($v)) || (is_string($v) && strlen($v)<=0) || (is_numeric($v) && $v<=0) || is_nan($v) || is_null($v)){
			$this->set_error();
		}
	}
	/**
	 * Ошибка валидатора filled
	 * @return string
	 */
	public function filled_error(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_empty'], array('label' => $this->cfg['label']));
	}

	/**
	 * Валидатор mail (Правильность e-mail'а)
	 */
	public function mail(){
		if(function_exists('filter_var')){
			if(!filter_var($this->cfg['value'], FILTER_VALIDATE_EMAIL)){
				$this->set_error();
			}
		} else{
			$rgx="/(\w[-._\w]*\w@\w[-._\w]*\w\.\w{2,3})/i";
			if(preg_match($rgx, $this->cfg['value'])){
				$this->set_error();
			}
		}
	}
	/**
	 * Ошибка валидатора mail
	 * @return string
	 */
	public function mail_error(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_mail'], array('label' => $this->cfg['label']));
	}

	/**
	 * Валидатор phone (дост.символы +(0-9)0-9) можно и без + и без скобокб а так же с "-" но не сразу после скобки
	 */
	public function phone(){
		if(!preg_match('/^[+]?[0-9]{0,3}?([(][0-9]{3,3}[)])?[^-][0-9-]+$/i', $this->cfg['value'])){
			$this->set_error();
		}
	}
	/**
	 * Ошибка валидатора phone
	 * @return string
	 */
	public function phone_error(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_phone'], array('label' => $this->cfg['label']));
	}

	/**
	 * Валидатор number (int или строка в виде int)
	 */
	public function number(){
		if(!is_numeric($this->cfg['value'])){
			$this->set_error();
		}
	}
	/**
	 * Ошибка валидатора number
	 * @return string
	 */
	public function number_error(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_number'], array('label' => $this->cfg['label']));
	}

	static function makeBytes($str){
		if(strpos($str, 'Kb')){
			$ms = ($str+0) * pow(2, 10);
		} elseif(strpos($str, 'Mb')){
			$ms = ($str+0) * pow(2, 20);
		} elseif(strpos($str, 'Gb')){
			$ms = ($str+0) * pow(2, 30);
		} else{
			$ms = $str+0;
		}
		return $ms;
	}

	/**
	 * Валидатор filesize
	 * @param $ar Массив параметров
	 */
	public function filesize($ar){

		if(!isset($ar[1])){
			$ar[1] = $ar[0];
			$ar[0] = 0;
		}
		$ms0 = self::makeBytes($ar[0]);
		$ms1 = self::makeBytes($ar[1]);

		if(!isset($this->cfg['value']['size'])){
			foreach($this->cfg['value'] as $arval){
				if($arval['size'] <= 0){
					return true;
				}
				if($arval['size'] > $ms1){
					$this->set_error();
				}
				if($arval['size'] < $ms0){
					$this->set_error(false, false, '_min');
				}
			}
		} else{

			if($this->cfg['value']['size'] <= 0){
				return true;
			}
			if($this->cfg['value']['size'] > $ms1){
				$this->set_error();
			}
			if($this->cfg['value']['size'] < $ms0){
				$this->set_error(false, false, '_min');
			}
		}

	}
	/**
	 * Ошибка валидатора filesize
	 * @return string
	 */
	public function filesize_error(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_file_max_sz'], array('label' => $this->cfg['label']));
	}
	/**
	 * Ошибка валидатора filesize
	 * @return string
	 */
	public function filesize_error_min(){
		return CFTypes::varsReplacer($GLOBALS['MESS']['CFB_fld_file_min_sz'], array('label' => $this->cfg['label']));
	}

	/**
	 * Устанавливает ошибку валидатора в котором(методе) был вызван.
	 * @param string|bool $str Строка ошибки. Если не задана то берется из метода [validator]_error()
	 * @param string|bool $id ID ошибки в текцщей форме
	 * @param string|bool $sfx Суффикс для доп метода. [validator]_error[suffix]()
	 */
	protected function set_error($str=false, $id=false, $sfx=''){
		if(!$str){
			$bgt = debug_backtrace();
			$m = $bgt[1]['function'].'_error'.$sfx;
			$str = $this->$m();
		}
		if(!$id){
			$id = $bgt[1]['class'].$bgt[1]['function'].$this->cfg['full_name'];
		}
		$this->cfg['class'] .= ' fail';
		$this->cfg['errors'][ $id ] = $str;
	}

	/**
	 * Записывает в &$cfg новый массив конфигурации
	 * @param $cfg Ссылка на переменную в которую будет записан новый массив конфигурации
	 */
	public function put(&$cfg){
		$cfg = $this->cfg;
	}

	/**
	 * Системные ошибки $_FILES
	 * @param int $n Код ошибки
	 */
	public function fileError($n){
		$er = $GLOBALS['MESS']['CFB_file'].' "'.$this->cfg['label'].'" ';
		$er .= $this->ferrors[ $n ];
		$id = __CLASS__.__FUNCTION__.$this->cfg['full_name'];
		$this->set_error($er, $id);
	}
}
?>
