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
 *            $node                    // массив с текущим распарсенным элементом
 *            ,$tg_nm                    // имя текущего тега
 *            ,$ar_attr                // атрибуты текущего тега
 *            ,$chain_tags                // плоский массив $this->chain_tags хранящий вложенность тегов до текущего
 *            ,$chain_attr                // плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
 *            ,$id                        // идентификатор текущего тега
 * ){
 *    print_r($node);
 * }
 * </code>
 *
 * @author matiaspub@gmail.com
 */
class SaXML
{
	/**
	 * Прочитано байт из файла $this->file
	 * @var int
	 */
	public $bytes_readed = 0;
	/**
	 * Читать файл по $this->per_bytes байт
	 * @var string
	 */
	public $per_bytes = 4096;
	/**
	 * Название элемента массива содержащего аттрибуты ноды
	 * @var object
	 */
	protected $attr_name = '@attributes';
	/**
	 * Название элемента массива содержащего текст ноды
	 * @var object
	 */
	protected $text_name = '@text';
	/**
	 * Объект - парсер XML
	 * @var object
	 */
	protected $ob_xml;
	/**
	 * Путь к xml файлу
	 * @var string
	 */
	protected $file;
	/**
	 * Строка в виде xml
	 * @var string
	 */
	protected $xmlstring;
	/**
	 * Длина строки в виде xml
	 * @var string
	 */
	protected $xmlstring_len;
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
	protected $text = array();
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
	 * Айдишники нод - которые использовали post_selector
	 * @var array
	 */
	public $node_used_post_selector = array();
	/**
	 * Массив вызываемых функций
	 * @var array
	 */
	protected $functions = array();
	/**
	 * Наборной идентификатор текущей ноды
	 * Пример: >root_xml[date=20.12.2012]>sections>section>item[id=0001][name=Tovar]
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
	protected $pointer = array();
	/**
	 * Массив указателей на родителя<br>
	 * Не старайся понять, тут замешены указатели.
	 * @var array array of pointers
	 */
	protected $ar_parents = array();
	/**
	 * Имя функции которая будет вызвана по завершении чтения документа
	 * @var string
	 */
	protected $final_fn_nm = false;

	/**
	 * Конструктор. Инициализирует SAX-парссер
	 *
	 * @param string $file      Файл/строка с XML данными который будет парситься
	 * @param bool   $attr_name Название элемента массива содержащего аттрибуты ноды
	 * @param bool   $text_name Название элемента массива содержащего текст ноды
	 *
	 * @throws SaXMLloaderException
	 */
	public function __construct($file, $attr_name = false, $text_name = false)
	{
		if ($attr_name) {
			$this->attr_name = $attr_name;
		}
		if ($text_name) {
			$this->text_name = $text_name;
		}

		if (strlen(@file_get_contents($file,false,null,0,5))>0) {
			$this->file = $file;
		} else {
			$this->xmlstring = $file;
			$this->xmlstring_len = strlen($file);
		}
		// объект парсера
		$this->ob_xml = xml_parser_create();
		xml_set_object($this->ob_xml, $this);
		// обработчики открывающего и закрывающего тегов
		xml_set_element_handler($this->ob_xml, 'start_element', 'end_element');
		// обработчик текста тегов
		xml_set_character_data_handler($this->ob_xml, 'cdata');
		// опция: приводить все в верхний регистр - убираем
		$this->xml_parser_set_option(XML_OPTION_CASE_FOLDING, false);

		if (!$this->xmlstring) {
			$this->fp = fopen($this->file, 'r');
			if (!$this->fp) {
				throw new SaXMLloaderException('Ощибка чтения данных из файла: "'.$this->file.'"', 0);
			}
		}
	}

	/**
	 * (PHP 4, PHP 5)<br/>
	 * Set options in an XML parser
	 * @link http://php.net/manual/en/function.xml-parser-set-option.php
	 *
	 * @param int   $option           <p>
	 *                                Which option to set. See below.
	 *                                </p>
	 *                                <p>
	 *                                The following options are available:
	 *                                <table>
	 *                                XML parser options
	 *                                <tr valign="top">
	 *                                <td>Option constant</td>
	 *                                <td>Data type</td>
	 *                                <td>Description</td>
	 *                                </tr>
	 *                                <tr valign="top">
	 *                                <td><b>XML_OPTION_CASE_FOLDING</b></td>
	 *                                <td>integer</td>
	 *                                <td>
	 *                                Controls whether case-folding is enabled for this
	 *                                XML parser. Enabled by default.
	 *                                </td>
	 *                                </tr>
	 *                                <tr valign="top">
	 *                                <td><b>XML_OPTION_SKIP_TAGSTART</b></td>
	 *                                <td>integer</td>
	 *                                <td>
	 *                                Specify how many characters should be skipped in the beginning of a
	 *                                tag name.
	 *                                </td>
	 *                                </tr>
	 *                                <tr valign="top">
	 *                                <td><b>XML_OPTION_SKIP_WHITE</b></td>
	 *                                <td>integer</td>
	 *                                <td>
	 *                                Whether to skip values consisting of whitespace characters.
	 *                                </td>
	 *                                </tr>
	 *                                <tr valign="top">
	 *                                <td><b>XML_OPTION_TARGET_ENCODING</b></td>
	 *                                <td>string</td>
	 *                                <td>
	 *                                Sets which target encoding to
	 *                                use in this XML parser.By default, it is set to the same as the
	 *                                source encoding used by <b>xml_parser_create</b>.
	 *                                Supported target encodings are ISO-8859-1,
	 *                                US-ASCII and UTF-8.
	 *                                </td>
	 *                                </tr>
	 *                                </table>
	 *                                </p>
	 * @param mixed $value            <p>
	 *                                The option's new value.
	 *                                </p>
	 *
	 * @return bool This function returns <b>FALSE</b> if <i>parser</i> does not
	 *       refer to a valid parser, or if the option could not be set. Else the
	 *       option is set and <b>TRUE</b> is returned.
	 */
	protected function xml_parser_set_option($option, $value)
	{
		xml_parser_set_option($this->ob_xml, $option, $value);
	}

	/**
	 * Запускает чтение и обработку xml.<br>
	 * В ходе выполнения метода, будут вызваны функции соответствующие селекторам, по которым будут найдены ноды.
	 *
	 * @throws SaXMLloaderException
	 */
	public function go()
	{
		while ($data = $this->read()) {
			if ($this->break_all) {
				break;
			}
			if (!xml_parse($this->ob_xml, $data, $this->is_final())) {
				throw new SaXMLloaderException(
					'Ошибка разбора XML: '
					.xml_error_string(xml_get_error_code($this->ob_xml))
					.', строка '
					.xml_get_current_line_number($this->ob_xml)
					.". Строка содержит:\n".htmlentities($data)."\n"
					, 1);
			}
		}

		if ($this->final_fn_nm) {
			call_user_func($this->final_fn_nm, $this->bytes_readed);
		}
		if (!$this->xmlstring) {
			fclose($this->fp);
		}
		xml_parser_free($this->ob_xml);
	}

	/**
	 * Возвращает кусок файла/строки длиной $this->per_bytes
	 *
	 * @return string
	 */
	protected function read()
	{
		if ($this->xmlstring) {
			$s = substr($this->xmlstring, $this->bytes_readed, $this->per_bytes);
		} else {
			$s = fread($this->fp, $this->per_bytes);
		}
		$this->bytes_readed += $this->per_bytes;
		return $s;
	}

	/**
	 * Проверяет не конец ли файла/строки
	 *
	 * @return bool
	 */
	protected function is_final()
	{
		if ($this->xmlstring) {
			return $this->bytes_readed >= $this->xmlstring_len;
		} else {
			return feof($this->fp);
		}
	}

	/**
	 * Регистрирует функцию обработчик которая будет вызвана после завершения работы парсера
	 *
	 * @param $fn_callback Функция php_callback
	 *
	 * @return $this
	 */
	public function registerFinal($fn_callback)
	{
		$this->final_fn_nm = $fn_callback;
		return $this;
	}

	/**
	 * Регистрирует функцию обработчик, для найденных по селектору $sel элементов
	 *
	 * @param string       $sel         Селектор. Элементы которые при нахождении которых будет вызываться функция $fn_clbk
	 * @param string|array $fn_clbk     Функция php_callback <br>
	 *                                  <b>Параметры передваемые в функцию:</b>
	 *                                  <code>
	 *                                  function(
	 *                                  $node                    // массив с текущим распарсенным элементом
	 *                                  ,$tg_nm                    // имя текущего тега
	 *                                  ,$ar_attr                // атрибуты текущего тега
	 *                                  ,$chain_tags                // плоский массив $this->chain_tags хранящий вложенность тегов до текущего
	 *                                  ,$chain_attr                // плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
	 *                                  ,$id                        // идентификатор текущего тега
	 *                                  ,$this->bytes_readed        // прочитано байт из файла
	 *                                  )
	 *                                  </code>
	 * @param bool         $post_sel    Селектор, указывающий на ноду элемента найденного по селектору $sel, после которой будет выполенна функция $fn_clbk.<br>
	 *                                  По умолчанию функция $fn_clbk выполняется при нахождении закрывающего тега ноды найденной по селектору $sel.
	 *
	 * @return $this
	 */
	public function register($sel, $fn_clbk, $post_sel = false)
	{
		// преобразуем селектор в регулярку
		$sel = $this->prepareSelect($sel);
		// селектор ">" - выбирает корневой элемент
		if (substr($sel, 0, 2) == '#>') {
			$sel = str_replace('#>', '#'.'^>[^>\s]*', $sel);
		}
		// преобразуем пост селектор в регулярку
		$post_sel = $this->prepareSelect($post_sel);
		// добавим к постселектору селектор
		if ($post_sel) {
			if (substr($post_sel, 1, 5) == '(^|>)') {
				$post_sel = str_replace('$#', '[\S\s]*'.substr($post_sel, 1), $sel);
			} else {
				$post_sel = str_replace('$#', ''.substr($post_sel, 1), $sel);
			}
		}

		// сохраним все в нужные массивы
		$this->selectors[ ] = $sel;
		$this->post_selectors[ ] = $post_sel;
		$this->functions[ ] = $fn_clbk;
		return $this;
	}

	/**
	 * Подготавливает Селектор.
	 * Пирмер очень сложного селектора: <b> catalog> tr > shop[a=b][attr='val[2][sok]'][v=c]  > tag[id=0] > tra>tag[vii=i] </b>
	 *
	 * @param  string $sel Селектор. Как в jQuery, но доступны только следующие типы: теги, атрибуты(множественные и без значений), вложенность и потомок.
	 *
	 * @return array      Готовая регулярка, по которой будет определяться подходит ли тег селектору.
	 */
	protected function prepareSelect($sel)
	{
		if (trim($sel) == '') {
			return false;
		}
		// массив с многократно употребляемыми паттернами
		$p = array(
			'attr_val?' => '((=[^\]"\']*)|(=["\'][\S]+\]["\']))?', // возможно значения атрибутов
		);
		// атрибуты и возможно значения атрибутов
		$p[ 'attr+val?' ] = '[^\[\]]+'.$p[ 'attr_val?' ];
		// (атрибуты и возможно значения атрибутов, с кавычками)0 или несколько
		$p[ '[attr+val?]*' ] = '(\['.$p[ 'attr+val?' ].'\])*';

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
		foreach ($ar as &$r) {
			/* если есть атрибуты, то ловим их и сортируем правильно(natural)
			а затем соединяем превращая попутно в регулярку */
			if (strpos($r, '[') !== false) {
				// ловим
				preg_match_all(
					'#'
					.'(?P<attr>'
					.$p[ 'attr+val?' ]
					.')'
					.'\](?:\[)?'. // ] и может быть [

					'#',
					$r,
					$m
				);
				// сортируем
				sort($m[ 'attr' ]);

				/* пройдемся по атрибутам, чтоб экранировать все [ и ],
				и добавим регулярку на возможность присутствия значения атрибута,
				тем атрибутам которые указаны без значения */
				foreach ($m[ 'attr' ] as &$at) {
					$at = str_replace(array(']', '['), array('\]', '\['), $at);
					if (strpos($at, '=') === false) {
						$at .= $p[ 'attr_val?' ];
					}
				}
				unset($at);
				/* соединим готовые атрибуты попутно вставив между ними
				регулярку $p['[attr+val?]*'] - "(атрибуты и возможно значения атрибутов, с кавычками)0 или несколько" */
				$sel = $p[ '[attr+val?]*' ].'\['.
					implode('\]'.$p[ '[attr+val?]*' ].'\[', $m[ 'attr' ])
					.'\]'.$p[ '[attr+val?]*' ];
				/* если строка начинается с тега(не с атрибута),
				то вернем в нее тег + готовые атрибуты */
				if (substr($r, 0, 1) != '[') {
					$sel = str_replace(substr($r, strpos($r, '[')), $sel, $r);
				}
			} else {
				/* если атрибутов нет, то просто экранируем ] и [ */
				$sel = str_replace(array(']', '['), array('\]', '\['), $r);
				// после тека также могут быть "(атрибуты и возможно значения атрибутов, с кавычками)0 или несколько"
				$sel .= $p[ '[attr+val?]*' ];
			}
			// если селектор начинается с >, значит с корня дерева
			if (substr($sel, 0, 1) != '>') {
				$r = '(^|>)'.$sel;
			} else {
				$r = $sel;
			}
		}
		unset($r);
		/* так как пробел в селекторе обозначает не потомка(возможное наличие тегов между указанными), то
		заменим пробелы на регулярку обозначающую возможные теги */
		$sel = implode('[\S\s]*', $ar);
		/* уберем регулярку обозначающую возможные теги перед >(символ потомства) */
		$sel = str_replace('[\S\s]*>', '>', $sel);
		// добавим конец строки
		$sel .= '$';
		// завернем в обозначение регулярки, и экранируем(на всякий случай) обозначающие символы внутри регулярки
		$sel = '#'.str_replace('#', '\#', $sel).'#';

		// вернем готовую регулярку
		return $sel;
	}

	/**
	 * Функция открывающих тегов
	 *
	 * @param object $parser     Объект парсера
	 * @param string $tag        Имя тега
	 * @param array  $attributes Аттрибуты
	 *
	 * @return bool
	 */
	protected function start_element(
		/** @noinspection PhpUnusedParameterInspection */
		$parser,
		$tag,
		$attributes
	){
		if ($this->break_all) {
			return false;
		}
		$this->increase($tag, $attributes);

		// проверяю регулярки из $this->post_selectors, на соответствие сформированной_строке
		// при совпадении вызываю соответствующую совпавшей регулярке функцию, и помечаю чтоб не вызывать функцию после закрывающего тега
		$kes = $this->get_match($this->post_selectors);
		$idt = array();
		if (!empty($kes)) {
			foreach ($kes as $ke) {
				$selector = $this->selectors[ $ke ];
				if ($selector) {
					foreach ($this->node as $id => $nd) {
						preg_match($selector, $id, $m);
						if (!empty($m)) {
							$idt[ $ke ] = $id;
							break;
						}
					}
				}
			}
			if (!empty($idt)) {
				foreach($idt as $id){
					$this->call($kes, $id, true);
				}
			}
		}

		// при совпадении создаю новую ноду, для сбора инфы чтоб отправить в функцию
		$keys = $this->get_match();
		if (!empty($keys)) {
			// создаю ноду, в которую будет накапливаться инфа как в массив массивов
			$this->node[ $this->id ] = array();
			// запоминаю предыдущий тег в массив
			$this->ar_parents[ $this->id ][ ] =& $this->node[ $this->id ];
			// создаю ссылку на текущий тег
			$this->pointer[ $this->id ] =& $this->node[ $this->id ];
		}
		// если есть отслеживаемые ноды
		foreach ($this->node as $id => $nd) {
			if (strpos($this->id, $id) !== false) {
				// запоминаю предыдущий тег в массив
				$this->ar_parents[ $id ][ ] =& $this->pointer[ $id ];
				// создаю ссылку на текущий тег
				if ($this->id != $id) {
					$this->pointer[ $id ] =& $this->pointer[ $id ][ $tag ];
				}
				$this->tags[ $id ][ ] = $tag;
				// выясняю массив текущий элемент или нет
				if ($this->id != $id) {
					if ($tag == $this->last_tag) {
						if (!isset($this->pointer[ $id ][ 0 ])) {
							$this->pointer[ $id ] = array($this->pointer[ $id ]);
						}
						$this->pointer[ $id ] =& $this->pointer[ $id ][ ];
					}
				}
			}
		}
		return true;
	}

	/**
	 * Наращивает идентификатор и рабочие массивы
	 *
	 * @param string $tag        Имя тега
	 * @param array  $attributes Аттрибуты
	 */
	protected function increase($tag, $attributes)
	{
		// собираю теги в строку $this->id по которой буду тестить регулярки
		$this->increaseID($tag, $attributes);
		// собираю последовательность вложенных тегов в массив
		$this->chain_tags[ ] = $tag;
		// собираю последовательность атрибутов вложенных тегов в массив
		$this->chain_attributes[ ] = $attributes;
	}

	/**
	 * Проверяет на соответствие идентификатору все регулярки из $this->selectors
	 * Возвращает ключ первого подходящего селектора, или false
	 *
	 * @param array|bool $selectors    Массив селекторов. $this->selectors
	 *
	 * @return bool|array
	 */
	protected function get_match($selectors = false)
	{
		$keys = array();
		if ($selectors === false) {
			$selectors = $this->selectors;
		}
		// проход по массиву регулярок и проверка на соответствие
		foreach ($selectors as $k => $reg) {
			if (isset($this->break[ $reg ]) || !$reg) {
				continue;
			}
			preg_match($reg, $this->id, $m);
			if (!empty($m)) {
				$keys[ ] = $k;
			}
		}
		return $keys;
	}

	/**
	 * Вызов функции $fn_nm зарегистрированной на селектор, при помощи метода $this->register()
	 *
	 * @param array|string $fn_key        Ключ массива $this->functions по которому находится название функции передаваемое в функцию call_user_func. Может быть массивом(класс,метод) или строкой(функция)
	 * @param bool         $id            Идентификатор ноды
	 * @param bool         $post_sel      Останавливающий селектор
	 *
	 * @return bool
	 */
	protected function call($fn_key, $id = false, $post_sel = false)
	{
		$fn_nm = array();
		foreach ($fn_key as $fk) {
			$fn_nm[ $fk ] = $this->functions[ $fk ];
		}
		if ($id === false) {
			$id = $this->id;
		}
		// если функция вызвана раньше закрывающего тега - то помечаю, что уже использован
		// чтоб не вызвать функцию повторно с пустым массивом
		if (isset($this->node_used_post_selector[ $id ])) {
			return false;
		}

		// в функцию передаются следующие парамтеры:
		// имя текущего тега, атрибуты текущего тега, плоский массив $this->tags харнящий вложенность тегов до текущего
		$node = $this->node[ $id ];
		array_walk_recursive($node, array($this, 'text_trimmer'));

		$tgssz = sizeof((isset($this->tags[ $id ])) ? $this->tags[ $id ] : array());
		$tg_nm = (!$post_sel) ? $this->chain_tags[ sizeof($this->chain_tags) - 1 ] : $this->chain_tags[ $tgssz ];
		$ar_attr = (!$post_sel) ? $this->chain_attributes[ sizeof(
			$this->chain_attributes
		) - 1 ] : $this->chain_attributes[ $tgssz ];
		$diff = sizeof($this->chain_tags) - $tgssz;
		$chain_tags = (!$post_sel) ? $this->chain_tags : array_slice($this->chain_tags, 0, $diff);
		$chain_attr = (!$post_sel) ? $this->chain_attributes : array_slice($this->chain_attributes, 0, $diff);

		foreach ($fn_nm as $fk => $fn) {
			if ($this->break_all) {
				break;
			}
			if (isset($this->break[ $this->selectors[ $fk ] ])) {
				continue;
			}

			$ret = call_user_func(
				$fn,
				/* $this, */
				$node, // массив с текущим распарсенным элементом
				$tg_nm, // имя текущего тега
				$ar_attr, // атрибуты текущего тега
				$chain_tags, // плоский массив $this->chain_tags хранящий вложенность тегов до текущего
				$chain_attr, // плоский массив $this->chain_attributes хранящий вложенность атрибутов тегов до текущего
				$id, // идентификатор текущего тега
				$this->bytes_readed // прочитано байт из файла
			);


			/* если функция вернула 'stop' - остановим выборку текущего селектора */
			if ($ret === 'break') {
				$this->break[ $this->selectors[ $fk ] ] = true;
			}
			/* если функция вернула 'stop_all' - остановим все выборки */
			if ($ret === 'break_all') {
				$this->break_all = true;
			}
		}
		// после вызова функции массив с текущим элементом убивается и все его инструменты
		unset($this->node[ $id ], $this->tags[ $id ], $this->text[ $id ], $this->pointer[ $id ], $this->ar_parents[ $id ]);

		if ($post_sel) {
			$this->node_used_post_selector[ $id ] = true;
		}
		return true;
	}

	/**
	 * Наращивает идентификатор текущей нодой с атрибутами
	 * >yml_catalog>shop[id=31]>name
	 *
	 * @param $tag          Текущая нода
	 * @param $attributes   Атрибуты текущей ноды, предварительно отсортированы sort()
	 */
	protected function increaseID($tag, $attributes)
	{
		$this->id .= '>';
		$this->id .= $tag;
		if (!empty($attributes)) {
			asort($attributes);
			foreach ($attributes as $k => $v) {
				if (trim($k) != '') {
					if ($v == '') {
						$arttr[ ] = $k;
					} else {
						// заменим пробелы в значениях атрибутов на _
						$v = preg_replace('#\s+#', '_', $v);
						if (strpos($v, '[') !== false) {
							$v = "\"$v\"";
						}
						$arttr[ ] = "$k=$v";
					}
				}
			}
			$this->id .= (!empty($arttr)) ? '['.implode('][', $arttr).']' : '';
		}
	}

	/**
	 * Функция закрывающих тегов
	 *
	 * @param object $parser Объект парсера
	 * @param string $tag    Имя тега
	 */
	protected function end_element(
		/** @noinspection PhpUnusedParameterInspection */
		$parser,
		$tag
	){
		// запоминаем последний тег
		$this->last_tag = $tag;
		foreach ($this->node as $id => $nd) {
			if (strpos($this->id, $id) !== false) {
				// указатель на текущую ноду
				$node =& $this->pointer[ $id ];
				// атрибуты текущей ноSды
				$attrs = $this->chain_attributes[ sizeof($this->chain_attributes) - 1 ];
				if (!empty($attrs)) {
					// пишем в ноду атрибуты
					$node[ $this->attr_name ] = $attrs;
				}
				if (isset($this->text[ $id ])) {
					// пишем в ноду текст
					$node[ $this->text_name ] = $this->text[ $id ];
					$this->text[ $id ] = '';
				}
				// возвращаем указатель на позицию(тег) выше в дереве тегов
				if (isset($this->ar_parents[ $id ][ sizeof($this->ar_parents[ $id ]) - 1 ])) {
					$this->pointer[ $id ] =& $this->ar_parents[ $id ][ sizeof($this->ar_parents[ $id ]) - 1 ];
				}
				// сокращаем хвост вложенных тегов
				if (isset($this->ar_parents[ $id ])) {
					array_pop($this->ar_parents[ $id ]);
				}
				if (isset($this->tags[ $id ])) {
					array_pop($this->tags[ $id ]);
				}
			}
		}

		// проверяю регулярки из $this->selectors, на соответствие $this->id
		// при совпадении вызываю соответствующую совпавшей регулярке функцию
		$keys = $this->get_match();
		if (!empty($keys)) {
			$this->call($keys);
		}
		$this->decrease();

	}

	/**
	 * Укорачивает идентификатор и рабочие массивы
	 */
	protected function decrease()
	{
		// укорачиваю строку $this->id
		$this->decreaseID();
		// укорачиваю массив вложенных тегов
		array_pop($this->chain_tags);
		// укорачиваю массив вложенных атрибутов
		array_pop($this->chain_attributes);
	}

	/**
	 * Укорачивает идентификатор.
	 * Вызывается при нахождении закрывающего тега.
	 * Вырезает из идентификатора последний тег(все что после последнего знака ">").
	 */
	protected function decreaseID()
	{
		$this->id = substr($this->id, 0, strrpos($this->id, ">"));
	}

	/**
	 * Функция обработки данных
	 *
	 * @param object $parser Объект парсера
	 * @param string $text   Данные
	 */
	protected function cdata(
		/** @noinspection PhpUnusedParameterInspection */
		$parser,
		$text
	){
		// если есть отслеживаемые ноды, то мы в них накапливаем данные
		foreach ($this->node as $id => $nd) {
			if (strpos($this->id, $id) !== false) {
				if (is_array($this->node[ $id ])) {
					if (!isset($this->text[ $id ])) {
						$this->text[ $id ] = '';
					}
					$this->text[ $id ] .= $text;
				}
			}
		}
	}

	/**
	 * Подрезает все строки в массиве
	 */
	protected function text_trimmer(&$str)
	{
		if (is_string($str)) {
			$str = trim($str);
		}
	}

}


class SaXMLloaderException extends Exception
{

}
