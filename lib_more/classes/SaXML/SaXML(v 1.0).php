<?
/**
 * SAX-загрузчик XML
 *
 * Построчный парсер XML.
 * Считывает построчно файл, и вызывает функции-обработчики: для зарегистрированных селекторов(аля jQuery, но селекторы попроще)
 * <br><br>
 * <b>Пример:</b>
 * <code>
 * // создаем парсер, который будет читать документ http://somesite.vru/test_ml.xml
 * $loader = new SaXML('http://somesite.vru/test_ml.xml');
 * // регистрируем функцию обработчик, которая будет вызвана при нахождении тега category
 * $loader->register('category', 'fn_nm', '');
 * // регистрируем функцию обработчик, которая будет вызвана при нахождении тега shop с атрибутом id=31, непосредственного потомка тега yml_catalog
 * // при этом обработчик будет вызван не после закрывающего тега shop(как это работает по умолчанию), а сразу при нахождении тега categories, вложенного в тег yml_catalog>shop[id=31]
 * $loader->register('yml_catalog>shop[id=31]', 'fn_nm', 'categories');
 * // запускаем  обработку файла
 * $loader->go();
 *
 * // В ходе обработки файла при нахождении зарегистрированных тегов shop и category будут вызваны соответствующие функции(fn_nm) с параметрами.
 *
 * // создаем зарегистрированную функцию-обработчик
 * function fn_nm(
 * 			$node                   	// массив с текущим распарсенным элементом
 * 		    ,$tg_nm                 	// имя текущего тега
 * 		    ,$ar_attr               	// атрибуты текущего тега
 * 		    ,$chain_tags            	// плоский массив $this->chain_tags хранящий вложенность тегов до текущего
 * 		    ,$chain_attr            	// плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
 * 		    ,$id                    	// идентификатор текущего тега
 * ){
 * 	print_r($node);
 * }
 * </code>
 *
 * @author matiaspub@gmail.com
 */
class SaXML {
	/**
	 * Объект - парсер XML
	 * @var object
	 */
	protected $ob_xml;
	/**
	 * Путь к xml файлу
	 * @var [type]
	 */
	protected $file;
	/**
	 * Указатель на файл
	 * @var resource
	 */
	protected $fp;
	/**
	 * Плоский массив вложенности тегов, от текущего - до совпавшего с селектором
	 * @var array
	 */
	protected $tags = array();
	/**
	 * Плоский массив вложенности тегов, от текущего - до корневого
	 * @var array
	 */
	protected $chain_tags = array();
	/**
	 * Плоский массив атрибутов вложенности тегов
	 * атрибуты $this->chain_attributes[$key] соответствуют ноде  $this->chain_tags[$key]
	 * @var array
	 */
	protected $chain_attributes = array();
	/**
	 * Предыдущий тег
	 * @var string
	 */
	protected $last_tag;
	/**
	 * Является ли значение тегов массивом
	 * @var boolean
	 */
	protected $is_array;
	/**
	 * Данные каждого тега
	 * @var string
	 */
	protected $text = '';
	/**
	 * Массив распарсенной ноды(найденной по определнному в методе $this->register() селектору, и передаваемый в соответствующую селектору функцию)
	 * @var bool
	 */
	protected $node = array();
	/**
	 * Массив селекторов, при нахождении которых будет выполнена соответствующая ключу функция из $this->functions
	 * @var array
	 */
	protected $selectors = array();
	/**
	 * Массив селекторов-стоперов,<br>
	 * При нахождении селектора $post_selectors, внутри эелемента совпадающего с $selectors ,<br>
	 * будет выполнена соответствующая функция, не дожидаясь закрывающего тега
	 * @var array
	 */
	protected $post_selectors = array();
	/**
	 * Массив вызываемых функций
	 * @var array
	 */
	protected $functions = array();
	/**
	 * Наборной идентификатор текущей ноды
	 * Пример: root_xml[date=20.12.2012]>sections>section>item[id=0001][name=Tovar]
	 * @var string
	 */
	protected $id = '';
	/**
	 * Массив флагов. Останавливать ли выборку для селектора
	 * Если установлет [selector] => true, то выборка элементов соответствующих селектору selector прекращается
	 * @var array
	 */
	protected $break = array();
	/**
	 * Флаг. Останавливать ли выборку вообще.
	 * Если установлет true, то пробег по файлу xml прекращается
	 * @var string
	 */
	protected $break_all = false;
	/**
	 * Указатель на текущий вложенный тег, для текущего $this->id.<br>
	 * Не старайся понять, тут замешены указатели.
	 * @var array of pointers
	 */
	protected $pointer=array();
	/**
	 * Массив указателей на родителя<br>
	 * Не старайся понять, тут замешены указатели.
	 * @var array array of pointers
	 */
	protected $ar_parents=array();
	/**
	 * Прочитано байт из файла $this->file
	 * @var int
	 */
	public $bytes_readied=0;
	/**
	 * Имя функции которая будет вызвана по завершении чтения документа
	 * @var string
	 */
	protected $final_fn_nm=false;
	/**
	 * Читать файл по $this->per_bytes байт
	 * @var string
	 */
	public $per_bytes=4096;


	/**
	 * Конструктор. Инициализирует SAX-парссер
	 *
	 * @param $file Файл с XML данными который будет парситься.
	 *
	 * @throws SaXMLloaderException
	 */
	public function __construct($file) {
		$this->file = $file;
		// объект парсера
		$this->ob_xml = xml_parser_create();
		xml_set_object($this->ob_xml, $this);
		// обработчики открывающего и закрывающего тегов
		xml_set_element_handler($this->ob_xml, 'start_element', 'end_element');
		// обработчик текста тегов
		xml_set_character_data_handler($this->ob_xml, 'cdata');
		// опция: приводить все в верхний регистр
		xml_parser_set_option($this->ob_xml, XML_OPTION_CASE_FOLDING, false);
		$this->fp = fopen($this->file, 'r');
		if(!$this->fp) throw new SaXMLloaderException('Ощибка чтения данных из файла: "'.$this->file.'"');
	}

	/**
	 * Запускает чтение и обработку xml.<br>
	 * В ходе выполнения метода, будут вызваны функции соответствующие селекторам, по которым будут найдены ноды.
	 *
	 * @throws SaXMLloaderException
	 */
	public function go() {
		while($data = fread($this->fp, $this->per_bytes)){
			$this->bytes_readied += $this->per_bytes;
			if($this->break_all) break;
			if(!xml_parse($this->ob_xml, $data, feof($this->fp))) throw new SaXMLloaderException(
				'Ошибка разбора XML: '
				.xml_error_string(xml_get_error_code($this->ob_xml))
				.', строка '
				.xml_get_current_line_number($this->ob_xml)
			);
		}
		call_user_func($this->final_fn_nm);
		fclose($this->fp);
		xml_parser_free($this->ob_xml);
	}

	/**
	 * Функция открывающих тегов
	 *
	 * @param object $parser Объект парсера
	 * @param string $tag Имя тега
	 * @param array $attributes Аттрибуты
	 *
	 * @return void|bool
	 */
	protected function start_element(
		/** @noinspection PhpUnusedParameterInspection */
		$parser, $tag, $attributes) {
		if($this->break_all) return false;
		// собираю теги в строку $this->id по которой буду тестить регулярки
		$this->increaseID($tag,$attributes);
		// собираю последовательность вложенных тегов в массив
		$this->chain_tags[] = $tag;
		// собираю последовательность атрибутов вложенных тегов в массив
		$this->chain_attributes[] = $attributes;

		// проверяю регулярки из $this->post_selectors, на соответствие сформированной_строке
		// при совпадении вызываю соответствующую совпавшей регулярке функцию, и помечаю чтоб не вызывать функцию после закрывающего тега
		$ke = $this->get_match($this->post_selectors);
		if($ke !== false){
			$selector = $this->selectors[ $ke ];
			if($selector){
				foreach($this->node as $id=>$nd){
					preg_match($selector, $id, $m);
					if(!empty($m)){
						$idt = $id;
						break;
					}
				}
				if(isset($idt)){
					$this->call($ke,$idt,true);
				}
			}
		}

		// проверяю регулярки из $this->selectors_post, на соответствие сформированной_строке
		// при совпадении создаю новую ноду, для сбора инфы чтоб отправить в функцию
		$key = $this->get_match();
		if($key !== false){
			// создаю ноду, в которую будет накапливаться инфа как в массив подмассвов
			$this->node[ $this->id ] = array();
			// запоминаю предыдущий тег в массив
			$this->ar_parents[ $this->id ][] =& $this->node[ $this->id ];
			// создаю ссылку на текущий тег
			$this->pointer[ $this->id ] =& $this->node[ $this->id ];
		} else{
			foreach($this->node as $id=>$nd){
				if(strpos($this->id, $id)!==false){
					// запоминаю предыдущий тег в массив
					$this->ar_parents[ $id ][] =& $this->pointer[ $id ];
					// создаю ссылку на текущий тег
					$this->pointer[ $id ] =& $this->pointer[ $id ][ $tag ];
					$this->tags[ $id ][] = $tag;
					// выясняю массив текущий элемент или нет
					if($tag == $this->last_tag){
						if(!$this->pointer[ $id ][0]){
							$this->pointer[ $id ] = array($this->pointer[ $id ]);
						}
						$this->pointer[ $id ] =& $this->pointer[ $id ][];
					}
				}
			}
		}
		return true;
	}

	/**
	 * Функция закрывающих тегов
	 *
	 * @param object $parser Объект парсера
	 * @param string $tag Имя тега
	 */
	protected function end_element(
		/** @noinspection PhpUnusedParameterInspection */
		$parser, $tag) {
		// запоминаем последний тег
		$this->last_tag = $tag;
		foreach($this->node as $id=>$nd){
			if(strpos($this->id, $id)!==false){
				// указатель на текущую ноду
				$node =& $this->pointer[ $id ];
				// атрибуты текущей ноды
				$attrs = $this->chain_attributes[ sizeof($this->chain_attributes)-1 ];
				if(!empty($attrs)){
					// пишем в ноду атрибуты
					$node['@attributes'] = $attrs;
				}
				if($this->text[ $id ]){
					// пишем в ноду текст
					$node['@text'] = $this->text[ $id ];
					$this->text[ $id ] = '';
				}
				// возвращаем указатель на позицию(тег) выше в дереве тегов
				$this->pointer[ $id ] =& $this->ar_parents[ $id ][ sizeof($this->ar_parents[ $id ])-1 ];
				// сокращаем хвост вложенных тегов
				array_pop($this->ar_parents[ $id ]);
				array_pop($this->tags[ $id ]);
			}
		}


		// проверяю регулярки из $this->selectors, на соответствие $this->id
		// при совпадении вызываю соответствующую совпавшей регулярке функцию
		$key = $this->get_match();
		if($key !== false){
			$this->call($key);
		}
		// укорачиваю строку $this->id
		$this->decreaseID();
		// укорачиваю массив вложенных тегов и атрибутов
		array_pop($this->chain_tags);
		array_pop($this->chain_attributes);
	}

	/**
	 * Функция обработки данных
	 *
	 * @param object $parser Объект парсера
	 * @param string $text Данные
	 */
	protected function cdata(
		/** @noinspection PhpUnusedParameterInspection */
		$parser, $text) {
		// если есть отслеживаемые ноды, то мы в них накапливаем данные
		foreach($this->node as $id=>$nd){
			if(strpos($this->id, $id)!==false){
				if(is_array($this->node[ $id ])){
					$this->text[ $id ] .= $text;
				}
			}
		}
	}

	/**
	 * Проверяет на соответствие идентификатору все регулярки из $this->selectors
	 * Возвращает ключ первого подходящего селектора, или false
	 *
	 * @param array|bool $selectors	Массив селекторов. $this->selectors
	 *
	 * @return bool|int
	 */
	protected function get_match($selectors=false){
		if($selectors===false) $selectors = $this->selectors;
		// проход по массиву регулярок и проверка на соответствие
		// вернет первый ключ первой совпавшей регулярки
		foreach($selectors as $k=>$reg){
			if($this->break[ $reg ] || !$reg) continue;
			preg_match($reg, $this->id, $m);
			if(!empty($m)){
				return $k;
			}
		}
		return false;
	}

	/**
	 * Наращивает идентификатор текущей нодой с атрибутами
	 * >yml_catalog>shop[id=31]>name
	 *
	 * @param $tag          Текущая нода
	 * @param $attributes   Атрибуты текущей ноды, предварительно отсортированы sort()
	 */
	protected function increaseID($tag,$attributes){
		$this->id .= '>';
		$this->id .= $tag;
		if(!empty($attributes)){
			asort($attributes);
			foreach($attributes as $k=>$v){
				if(trim($k)!=''){
					if($v==''){
						$arttr[] = $k;
					} else{
						// заменим пробелы в значениях атрибутов на _
						$v = preg_replace('#\s+#', '_', $v);
						if(strpos($v,'[')!==false){
							$v = "\"$v\"";
						}
						$arttr[] = "$k=$v";
					}
				}
			}
			$this->id .= (!empty($arttr))?'['.implode('][',$arttr).']':'';
		}
	}

	/**
	 * Укорачивает идентификатор.
	 * Вызывается при нахождении закрывающего тега.
	 * Вырезает из идентификатора последний тег(все что после последнего знака ">").
	 */
	protected function decreaseID(){
		$this->id = substr($this->id,0,strrpos($this->id, ">"));
	}

	/**
	 * Вызов функции $fn_nm зарегистрированной на селектор, при помощь метода $this->register()
	 *
	 * @param array|string $fn_key		Ключ массива $this->functions по которому находится название функции передаваемое в функцию call_user_func. Может быть массивом(класс,метод) или строкой(функция)
	 * @param bool         $id			Идентификатор ноды
	 * @param bool         $post_sel	Останавливающий селектор
	 *
	 * @return bool
	 */
	protected function call($fn_key, $id=false, $post_sel=false) {
		$fn_nm = $this->functions[ $fn_key ];
		if($id===false) $id = $this->id;
		// если функция вызвана раньше закрывающего тега - то помечаю, что уже использован
		// чтоб не вызвать функцию повторно с пустым массивом
		if($this->node[ $id ]['_used_with_post_selector_'])return false;

		// в функцию передаются следующие парамтеры:
		// имя текущего тега, атрибуты текущего тега, плоский массив $this->tags харнящий вложенность тегов до текущего
		$node = $this->node[ $id ];
		array_walk_recursive($node,array($this,'text_trimmer'));
		$tgs = $this->tags[ $id ];
		$tg_nm = (!$post_sel) ? $this->chain_tags[sizeof($this->chain_tags)-1] : $this->chain_tags[sizeof($tgs)+1];
		$ar_attr = (!$post_sel) ? $this->chain_attributes[sizeof($this->chain_attributes) - 1] : $this->chain_attributes[sizeof($tgs) - 1];
		$diff = sizeof($this->chain_tags) - sizeof($tgs);
		$chain_tags = (!$post_sel) ? $this->chain_tags : array_slice($this->chain_tags,0,$diff);
		$chain_attr = (!$post_sel) ? $this->chain_attributes : array_slice($this->chain_attributes,0,$diff);

		$ret = call_user_func($fn_nm, 	/* $this, */
			$node                   	// массив с текущим распарсенным элементом
			,$tg_nm                 	// имя текущего тега
			,$ar_attr               	// атрибуты текущего тега
			,$chain_tags            	// плоский массив $this->chain_tags хранящий вложенность тегов до текущего
			,$chain_attr            	// плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
			,$id                    	// идентификатор текущего тега
			,$this->bytes_readied      	// прочитано байт из файла
		);
		/* если функция вернула 'stop' - остановим выборку текущего селектора */
		if($ret==='break'){
			$this->break[ $this->selectors[ $fn_key ] ] = true;
		}
		/* если функция вернула 'stop_all' - остановим все выборки */
		if($ret==='break_all'){
			$this->break_all = true;
		}
		// после вызова функции массив с текущим элементом убивается и все его инструменты
		unset($this->node[ $id ],$this->tags[ $id ],$this->text[ $id ],$this->pointer[ $id ],$this->ar_parents[ $id ]);

		if($post_sel){
			$this->node[ $id ] = array('_used_with_post_selector_'=>true);
		}
		return true;
	}

	public function registerFinal($fn_callback) {
		$this->final_fn_nm = $fn_callback;
	}

	/**
	 * Регистрирует функцию обработчик, для найденных по селектору $sel элементов
	 *
	 * @param string $sel Селектор. Элементы которые при нахождении которых будет вызываться функция $fn_clbk
	 * @param string|array $fn_clbk  Функция php_callback <br>
	 * <b>Параметры передваемые в функцию:</b>
	 * <code>
	 * function(
	 * 		$node                   	// массив с текущим распарсенным элементом
	 * 		,$tg_nm                 	// имя текущего тега
	 * 		,$ar_attr               	// атрибуты текущего тега
	 * 		,$chain_tags            	// плоский массив $this->chain_tags хранящий вложенность тегов до текущего
	 * 		,$chain_attr            	// плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
	 * 		,$id                    	// идентификатор текущего тега
	 * 		,$this->bytes_readed      	// прочитано байт из файла
	 * )
	 * </code>
	 * @param bool $post_sel    Селектор, указывающий на ноду элемента найденного по селектору $sel, после которой будет выполенна функция $fn_clbk.<br>
	 * По умолчанию функция $fn_clbk выполняется при нахождении закрывающего тега ноды найденной по селектору $sel.
	 */
	public function register($sel, $fn_clbk, $post_sel = false) {
		// преобразуем селектор в регулярку
		$sel = $this->prepareSelect($sel);
		// преобразуем пост селектор в регулярку
		$post_sel = $this->prepareSelect($post_sel);
		// добавим к постселектору селектор
		if($post_sel){
			if(substr($post_sel,1,5)=='(^|>)'){
				$post_sel = str_replace('$#', '[\S\s]*'.substr($post_sel,1),$sel);
			} else{
				$post_sel = str_replace('$#', ''.substr($post_sel,1),$sel);
			}
		}
		// сохраним все в нужные массивы
		$this->selectors[] = $sel;
		$this->post_selectors[] = $post_sel;
		$this->functions[] = $fn_clbk;
	}

	/**
	 * Подготавливает Селектор.
	 * Пирмер очень сложного селектора: <b> catalog> tr > shop[a=b][attr='val[2][sok]'][v=c]  > tag[id=0] > tra>tag[vii=i] </b>
	 *
	 * @param  string $sel Селектор. Как в jQuery, но доступны только следующие типы: теги, атрибуты(множественные и без значений), вложенность и потомок.
	 *
	 * @return array      Готовая регулярка, по которой будет определяться подходит ли тег селектору.
	 */
	protected function prepareSelect($sel) {
		if(trim($sel)=='') return false;
		// массив с многократно употребляемыми паттернами
		$p = array(
			'attr_val?' => '((=[^\]"\']*)|(=["\'][\S]+\]["\']))?', // возможно значения атрибутов
		);
		// атрибуты и возможно значения атрибутов
		$p['attr+val?'] = '[^\[\]]+'.$p['attr_val?'];
		// (атрибуты и возможно значения атрибутов, с кавычками)0 или несколько
		$p['[attr+val?]*'] = '(\['.$p['attr+val?'].'\])*';

		/* убираем лишние пробелы и ставим пробелы перед > */
		$sel = str_replace('>', ' >', $sel);
		$sel = preg_replace('#[\s]+#', ' ', $sel);
		$sel = preg_replace('#^[\s]+>#', '>', $sel);
		$sel = str_replace('> ', '>', $sel);
		// заменим пробелы в значениях атрибутов на _
		$sel = preg_replace('#(\[[^\[]*)[\s]+([^\]]*])#', '$1_$2', $sel);

		// разбиваем по пробелам
		$ar = explode(' ', $sel);

		/* проход по массиву */
		foreach($ar as &$r){
			/* если есть атрибуты, то ловим их и сортируем правильно(natural)
			а затем соединяем превращая попутно в регулярку */
			if(strpos($r, '[') !== false){
				// ловим
				preg_match_all(
					'#'
					.'(?P<attr>'
					.$p['attr+val?']
					.')'
					.'\](?:\[)?'. // ] и может быть [

					'#', $r, $m
				);
				// сортируем
				sort($m['attr']);

				/* пройдемся по атрибутам, чтоб экранировать все [ и ],
				и добавим регулярку на возможность присутствия значения атрибута,
				тем атрибутам которые указаны без значения */
				foreach($m['attr'] as &$at){
					$at = str_replace(array(']', '['), array('\]', '\['), $at);
					if(strpos($at, '=') === false){
						$at .= $p['attr_val?'];
					}
				} unset($at);
				/* соединим готовые атрибуты попутно вставив между ними
				регулярку $p['[attr+val?]*'] - "(атрибуты и возможно значения атрибутов, с кавычками)0 или несколько" */
				$sel = $p['[attr+val?]*'].'\['.
					implode('\]'.$p['[attr+val?]*'].'\[', $m['attr'])
					.'\]'.$p['[attr+val?]*'];
				/* если строка начинается с тега(не с атрибута),
				то вернем в нее тег + готовые атрибуты */
				if(substr($r, 0, 1) != '['){
					$sel = str_replace(substr($r, strpos($r, '[')), $sel, $r);
				}
			}
			else{
				/* если атрибутов нет, то просто экранируем ] и [ */
				$sel = str_replace(array(']', '['), array('\]', '\['), $r);
				// после тека также могут быть "(атрибуты и возможно значения атрибутов, с кавычками)0 или несколько"
				$sel .= $p['[attr+val?]*'];
			}
			// если селектор начинается с >, значит с корня дерева
			if(substr($sel, 0, 1) != '>'){
				$r = '(^|>)'.$sel;
			} else{
				$r = $sel;
			}
		} unset($r);
		/* так как пробел в селекторе обозначает не потомка(возможное наличие тегов между указанными), то
		заменим пробелы на регулярку обозначающую возможные теги */
		$sel = implode('[\S\s]*', $ar);
		/* уберем регулярку обозначающую возможные теги перед >(символ потомства) */
		$sel = str_replace('[\S\s]*>', '>', $sel);
		// добавим конец строки
		$sel .= '$';
		// завернем в обозначение регулярки, и экранируем(на всякий случай) обозначающие символы внутри регулярки
		$sel = '#'.str_replace('#','\#',$sel).'#';

		// вернем готовую регулярку
		return $sel;
	}


	/**
	 * Подрезает все строки в массиве
	 */
	public static function text_trimmer(&$str){
		if(is_string($str)){
			$str = trim($str);
		}
	}

	/**
	 * Возвращает размер удаленного файла
	 *
	 * @param $path Путь к удаленному файлу
	 * @return int|bool
	 */
	public static function remote_filesize($path=false){
		preg_match('#(ht|f)tp(s)?://(?P<host>[a-zA-Z-_.]+.[a-zA-Z]{2,4})(?P<name>/[\S]+)#',$path,$m);
		$x=0;
		$stop=false;
		$fp = fsockopen($m['host'], 80, &$errno, &$errstr, 30);
		fputs($fp,"HEAD $m[name] HTTP/1.0\nHOST: $m[host]\n\n");
		while(!feof($fp)&&!$stop){
			$y=fgets($fp, 2048);
			if($y=="\r\n"){
				$stop=true;
			}
			$x.=$y;
		}
		fclose($fp);

		if (preg_match("#Content-Length: ([0-9]+)#",$x,$size)){
			return $size[1];
		} else{
			return false;
		}
	}

	/**
	 * Возвразает представление размера в строчном виде **Кб, **Мб, **Гб, **байт
	 *
	 * @param $bytes Размер файла в байтах
	 * @param int $dec Чисел после запятой
	 *
	 * @return string
	 */
	public static function size_name($bytes=false,$dec=2){
		$gb=1073741824;
		$mb=1048576;
		$kb=1024;
		if($bytes>$gb){
			$bytes = $bytes/$gb;
			$sn = ' Гб';
		} elseif($bytes>$mb){
			$bytes = $bytes/$mb;
			$sn = ' Мб';
		} elseif($bytes>$kb){
			$bytes = $bytes/$kb;
			$sn = ' Кб';
		} else{
			$sn = ' б';
		}
		return number_format($bytes,$dec).$sn;
	}
}


class SaXMLloaderException extends Exception {

}
