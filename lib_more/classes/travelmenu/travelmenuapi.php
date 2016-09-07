<?
/**
 * Класс для работы с TravelmenuTourAPI, который позволяет осуществлять поиск и бронирование туров (http://www.travelmenu.ru/)
 * @author Матяш Сергей WEB-XPERT
 */
class TravelmenuAPI{
//	public static $HOST = 'http://dev.tmru.dev2.mrise.ru/api.php?xml=';
	/**
	 * Хост
	 * @var string
	 */
	private static $HOST = 'http://www.travelmenu.ru/api.php?xml=';

	/**
	 * Время кеширования
	 */
	const CACHE_TIME = 86400;
	/**
	 * Дебажить запросы или нет<br>
	 * Если true, то ссылка на каждый запрос к travelmenu выводится в консоль
	 * @var bool
	 */
	public static $DEBUG = false;

	/**
	 * Логин на сайте Travelmenu.ru
	 * @var string
	 */
	public static $login = 'bodnarouk@ulixes.ru';
	/**
	 * Пароль на сайте Travelmenu.ru
	 * @var string
	 */
	public static $password = '1234567890';

	/**
	 * Статусы брони
	 * @var array
	 */
	public static $BRONE_STATUS = array(
		'PND' => 'В процессе бронирования.',
		'CNF' => 'Забронирован.',
		'CLX' => 'Отменен.',
	);
	/**
	 * Статусы продукта
	 * @var array
	 */
	public static $PRODUCT_STATUS = array(
		'PND' => 'В процессе бронирования.',
		'REQ' => 'Номер "По запросу" и в процессе подтверждения отелем. Подтверждение/отказ будут получены в течение 24 часов.',
		'CNF' => 'Забронирован.',
		'CLX' => 'Отменен.',
	);
	/**
	 * Статусы оплаты брони
	 * @var array
	 */
	public static $PAY_BRONE_STATUS = array(
		'UNAVAILABLE'        => 'Оплата недоступна. Например, бронь еще не подтверждена или отменена.',
		'PENDING'            => 'Ожидает оплаты',
		'CARD_RESERVED'      => 'Сумма зарезервирована на карте',
		'PAID'               => 'Оплачен',
		'WARRANTY_LETTER'    => 'Гарантийное письмо',
		'PERSONAL_LIABILITY' => 'Под личную ответственность менеджера',
	);
	/**
	 * Поддерживаемые коды валют
	 * @var array
	 */
	public static $CUR_CODE = array(
		'CAD' => 'Canadian Dollar',
		'EUR' => 'Euro',
		'GBP' => 'Pound sterling',
		'RUR' => 'Russian Ruble',
		'UAH' => 'Ukrainian Hrivna',
		'USD' => 'United States Dollar',
	);
	/**
	 * Поддерживаемые коды валют для платежей
	 * @var array
	 */
	public static $CUR_CODE_PAY = array(
		'CAD' => 'Canadian Dollar',
		'EUR' => 'Euro',
		'GBP' => 'Pound sterling',
		'USD' => 'United States Dollar',
	);
	/**
	 * Поддерживаемые коды валют
	 * @var array
	 */
	public static $PAY_STATUS = array(
		'HOLD' => 'Сумма заблокирована на карте',
		'PAID' => 'Оплачен',
		'VOIDED' => 'Оплачен',
		'ERROR_PAY' => 'Не удалось подтвердить блокировку (Capture)',
		'ERROR_VOID' => 'Не удалось отменить блокировку (Void)',
	);

	public static $ERRORS = array(
		500 => 'Внутренняя ошибка, необходимо обратиться в поддержку',
		1   => 'Внутренняя ошибка, необходимо обратиться в поддержку',
		2   => 'Неверный XML',
		3   => 'Неизвестный запрос',
		4   => 'Неверный логин/пароль',
		5   => 'Travelmenu API временно отключен',
		6   => 'Неверные входные данные',
		7   => 'Статус брони не позволяет создать ваучер',
		8   => 'Нет прав на выполнение данного запроса, необходимо обратиться в поддержку',
		100 => 'Внутренняя ошибка при платеже, необходимо обратиться в поддержку',
		101 => 'Неверный номер карты',
		102 => 'Неверный CVV карты',
		103 => 'Неверный срок действия карты',
		104 => 'Неверное имя владельца карты',
		105 => 'Валюта платежа не поддерживается',
	);

	/**
	 * Запрос типов питания<br>
	 * Возвращает все типы питания, доступные для поиска. Запрос не имеет параметров.
	 * @static
	 * @return array
	 */
	public static function GetMealTypes(){
		$CACHE_ID = 'GetMealTypes';
		$CAHCE_DIR = '/cache/php/TravelmenuAPI/';
		$obCache = new CPHPCache;
		if($_GET['clear_cache']=='Y'){$obCache->CleanDir($CAHCE_DIR);};
		if($obCache->StartDataCache(self::CACHE_TIME, $CACHE_ID, $CAHCE_DIR)){
			$res = self::fullRequest('GetMealTypesRequest');
			foreach($res['GetMealTypesResponse']['MealTypes']['MealType'] as $ml){
				$arm[] = (array)$ml;
			}
			$obCache->EndDataCache(array("arm" => $arm)); // помним в кеш
		} else{
			$arVars = $obCache->GetVars(); // берем кеш
			$arm = $arVars["arm"]; // в удобную переменную
		}

		return $arm;
	}

	/**
	 * Запрос нициации поиска<br>
	 * Возвращает все типы питания, доступные для поиска. Запрос не имеет параметров.<br>
	 * <i>Максимальное количество номеров - 4, <br>Максимальное количество туристов во всех номерах - 8</i>
	 * @param array $ar <b>Массив параметров:</b><br>
	 * CountryCode - Код страны (GetLocationsResponse/Countries/Country/Code)<br>
	 * CityCode - Код города (GetLocationsResponse/Countries/Country/Cities/City/Code)<br>
	 * DateCheckin - Дата заезда в формате YYYY-MM-DD<br>
	 * DateCheckout - Дата выезда в формате YYYY-MM-DD<br>
	 * CurrencyCode - Код валюты. См. Поддерживаемые коды валют<br>
	 * MealTypeCodes - Список типов питания (GetMealTypesResponse/MealTypes/MealType/Code)<br>
	 * AvailableOnly - Флаг, определяющий, включать ли в результаты поиска номера по запросу. По умолчанию FALSE (включать все номера: доступные и по запросу)<br>
	 * NoMapping - Флаг, определяющий, использовать ли системные отели Travelmenu при формировании результата. По умолчанию FALSE (использовать)<br>
	 * Stars - Список запрашиваемых классов отеля (от 1 до 5)<br>
	 * HotelName - Название отеля для поиска. Обрабатывается как регистронезависимый поиск подстроки в строке<br>
	 * HotelCode - Код отеля<br>
	 * PriceRange - Границы стоимости номеров в отеле<br>
	 * Occupancies - Список номеров, каждый из которых содержит список возрастов туристов<br>
	 * @return array<b>Массив параметров ответа:</b><br>
	 * SearchCode - Код – идентификатор поиска<br>
	 * CompletionPercent - Процент выполнения поиска от 1 до 100
	 */
	public static function StartAsyncSearch($ar){
		$sort = array('CountryCode','CityCode','DateCheckin','DateCheckout','CurrencyCode','Occupancies','MealTypeCodes','AvailableOnly','NoMapping','Stars','HotelName','HotelCode','PriceRange');
		return self::fullRequest('StartAsyncSearchRequest', $ar, $sort);
	}

	/**
	 * Текущий результат асинхронного поиска
	 * @param string $sc Код поиска, полученный из запроса StartAsyncSearch
	 * @return <b>Возвращаемый массив:</b><br>
	 *
	 */
	public static function GetAsyncSearchCurrentResults($sc){
		$ar = array('SearchCode'=>$sc);
		return self::fullRequest('GetAsyncSearchCurrentResultsRequest',$ar);
	}

	/**
	 * Запрос описания отелей<br>
	 * Запрос возвращает описание отелей.
	 * @param array $ar <b>Массив параметров:</b><br>
	 * CountryCode - Код страны. Необязательный параметр<br>
	 * CityCode - Код города. Необязательный параметр<br>
	 * HotelCode - Код отеля. Необязательный параметр<br>
	 * @return <b>Возвращаемый массив:</b><br>
	 * Code - Код отеля<br>
	 * Name - Название отеля<br>
	 * Grade - Количество звезд<br>
	 * Address - Адрес<br>
	 * Phone - Телефон<br>
	 * Longitude - Долгота<br>
	 * Latitude - Широта<br>
	 * Images - Список URL картинок отеля<br>
	 * Description - Описание отеля<br>
	 */
	public static function GetHotels($ar){
		$sort = array('CountryCode', 'CityCode', 'HotelCode');
		return self::fullRequest('GetHotelsRequest',$ar,$sort);
	}

	/**
	 * Поиск номеров в отеле<br>
	 * @param array $ar <b>Массив параметров:</b><br>
	 * CountryCode - Код страны (GetLocationsResponse/Countries/Country/Code)<br>
     * CityCode - Код города (GetLocationsResponse/Countries/Country/Cities/City/Code)<br>
     * DateCheckin - Дата заезда в формате YYYY-MM-DD<br>
     * DateCheckout - Дата выезда в формате YYYY-MM-DD<br>
     * CurrencyCode - Код валюты. См. Поддерживаемые коды валют<br>
     * MealTypeCodes - Список типов питания (GetMealTypesResponse/MealTypes/MealType/Code)<br>
     * AvailableOnly - Флаг, определяющий, включать ли в результаты поиска номера по запросу. По умолчанию FALSE (включать все номера: доступные и по запросу)<br>
     * NoMapping - Флаг, определяющий, использовать ли системные отели Travelmenu при формировании результата. По умолчанию FALSE (использовать)<br>
     * Stars - Список запрашиваемых классов отеля (от 1 до 5)<br>
     * HotelName - Название отеля для поиска. Обрабатывается как регистронезависимый поиск подстроки в строке<br>
     * HotelCode - Код отеля<br>
     * PriceRange - Границы стоимости номеров в отеле<br>
     * Occupancies - Список номеров, каждый из которых содержит список возрастов туристов<br>
	 * @return array<b>Массив параметров ответа:</b><br>
	 * TravelerOccupancies/AvailabilityCode - код списка доступных номеров, необходимый для получения условий отмены и бронирования<br>
	 * TravelerOccupancies/TravelerOccupancy/TravelerAges - список возрастов туристов, для которых доступны варианты номеров TravelerOccupancies/TravelerOccupancy/Occupancies, из которых только один может быть выбран для бронирования<br>
	 * <b>Для каждого номера возвращается информация::</b><br>
	 * Code - Код номера, необходимый для получения условий отмены и бронирования<br>
	 * Name - Название номера<br>
	 * MealTypeCode - Код питания<br>
	 * Price - Цена<br>
	 * Available - TRUE если номер доступен, FALSE если по запросу<br>
	 */
	public static function SearchAvailability($ar){
		$sort = array('CountryCode','CityCode','DateCheckin','DateCheckout','CurrencyCode','MealTypeCodes','Occupancies','AvailableOnly','NoMapping','HotelName','HotelCode','Stars','PriceRange');
		return self::fullRequest('SearchAvailabilityRequest',$ar, $sort);
	}

	/**
	 * Получаем массив куска авторизации для преобразования в XML
	 * @return array
	 */
	public static function getCredentials(){
		return array(
            'Credentials' => array(
                'User'=> self::$login,
                'Password'=>self::$password
            )
       );
	}

	/**
	 * Выбор всех локаций(и городов и стран), WHERE NAME LIKE $term
	 * @param $term Выражение для поиска совпадений (например '%осси%')
	 * @return mixed CDBResult
	 */
	public static function getPlacesByTerm($term){
		global $DB;
		return $DB->Query("
			SELECT ci.NAME as ciNAME, ci.CODE as ciCODE, co.CODE as coCODE, co.NAME as coNAME
			FROM travelmenu_country AS co
			INNER JOIN travelmenu_city AS ci
			WHERE ci.COUNTRY_CODE = co.CODE AND (co.NAME LIKE '%$term%' OR ci.NAME LIKE '%$term%')
		");
	}

	/**
	 * Достает инфу из таблицы 'travelmenu_city' с условиями $arF
	 * @static
	 * @param array $arF Фильтры (можно и LIKE если ключ начинается с %)
	 * @return bool CDBResult
	 */
	public static function getCity($arF=array()){
		return self::getInfoFromTable($arF,'travelmenu_city');
	}

	/**
	 * Достает инфу из таблицы 'travelmenu_country' с условиями $arF
	 * @static
	 * @param array $arF Фильтры (можно и LIKE если ключ начинается с %)
	 * @return bool CDBResult
	 */
	public static function getCountry($arF=array()){
		return self::getInfoFromTable($arF,'travelmenu_country');
	}

	/**
	 * Достает инфу из таблицы $tbl с условиями $arF
	 * @static
	 * @param array $arF Фильтры (можно и LIKE если ключ начинается с %)
	 * @param bool $tbl Имя таблицы
	 * @return bool CDBResult
	 */
	public static function getInfoFromTable($arF=array(),$tbl=false){
		if(!$tbl) return false;
		global $DB;
		$where='';
		if(!empty($arF)){
			$c=0;
			foreach($arF as $k=>$v){
				if(strpos($k,'%')!==false){
					$k = substr($k,1);
					$ac = 'LIKE';
				} else{
					$ac = '=';
				}
				if($c>0){
					$where .= 'AND ';
				}
				$where .= "$k $ac \"{$DB->ForSql($v)}\"";
				$c++;
			}
		}
		$q='SELECT * FROM '.$tbl;
		if(strlen($where)>0){
			$q = $q.' WHERE '.$where;
		}
		return $DB->Query($q);
	}

	/**
	 * <i>Очень прибольшущий запрос, так что не злоупотреблять</i><br>
	 * Загружает в БД таблицы стран(travelmenu_country) и городов(travelmenu_city)
	 * @static
	 * @param bool $dbg=false Если true, то выводятся все строки вставки в БД
	 */
	public static function refreshLocations($dbg=false){
		global $DB;

		$tbls = $DB->Query('SHOW TABLES LIKE "travelmenu_c%"');
		// добавим таблицы в базу
		if($tbls->AffectedRowsCount()<2){
			$DB->RunSqlBatch(dirname(__FILE__)."/travelmenu_files/travelmenu.sql");
		}

		$loc = TravelmenuAPI::GetLocations();
		if(sizeof($loc)>0){
			$DB->Query('DELETE FROM travelmenu_city');
			$DB->Query('DELETE FROM travelmenu_country');

			foreach($loc as $cntry){
				// добавляем страну
				$cntry_r = 'INSERT INTO travelmenu_country (CODE,NAME) VALUES ("'.$cntry['Code'].'","'.
						$DB->ForSql($cntry['Name']).'")';
				if($dbg){
					echo '<pre>';
					print_r($cntry_r);
					echo '</pre>';
				}
				$DB->Query($cntry_r);
				if($cntry['Cities']['City'][0]){
					foreach($cntry['Cities']['City'] as $city){
						// добавляем город со страной $cntry['Code']
						$city_r = 'INSERT INTO travelmenu_city (CODE,NAME,COUNTRY_CODE) VALUES ('.$city['Code'].',"'.
								$DB->ForSql($city['Name']).'","'.$cntry['Code'].'")';
						if($dbg){
							echo '<pre>';
							print_r($city_r);
							echo '</pre>';
						}
						$DB->Query($city_r);
					}
				} else{
					$city_r = 'INSERT INTO travelmenu_city (CODE,NAME,COUNTRY_CODE) VALUES ('.
							$cntry['Cities']['City']['Code'].',"'.$DB->ForSql($cntry['Cities']['City']['Name']).'","'.
							$cntry['Code'].'")';
					if($dbg){
						echo '<pre>';
						print_r($city_r);
						echo '</pre>';
					}
					$DB->Query($city_r);
				}
			}
		}
	}

	/**
	 * <i>Основной метод для всех запросов</i><br>>
	 * Подготавливает данные для запроса и после ответа
	 * @param $ar Массив параметров запроса, который превращается в XML
	 * @param $req_name Название запроса - корневая ветка XML
	 * @param array $sort Массив параметров отсортированных в нужном порядке
	 * @return array Результат ответа на запрос.
	 */
	public static function fullRequest($req_name, $ar=array(), $sort=array()){
		if(!empty($sort)){
			$ar = self::sort($ar,$sort);
		}
		if(!empty($ar)){
			$ar = self::getCredentials()+$ar;
		} else{
			$ar = self::getCredentials();
		}
		$ar = array($req_name=>$ar);
		$xml = self::toRequest($ar);
		$resp = self::getData($xml);
		$res = self::fromResponse($resp);
		return $res;
	}

	/**
	 * Основной метод получающий данные от сервиса
	 * @static
	 * @return bool Возвращает массив(преобразованный json-объект)
	 */
	public static function getData($xml){
		if(class_exists('Cconsole') && self::$DEBUG==true){
			Cconsole::info('trm_hotel_request: ', self::$HOST.urlencode(preg_replace('/\n/','',$xml)));
		}
		$ret = file_get_contents(self::$HOST.urlencode($xml));
		return $ret;
	}

	/**
	 * Преобразует ответ(xml) в массив
	 * @param $resp Ответ
	 * @return array
	 */
	public static function fromResponse($resp){
		if(!$resp) return false;
		$xml = new CXmlAsArray($resp);
		return $xml->getArray();
	}

	/**
	 * Конвертирует массив парамтеров в XML объект
	 * @param $ar Массив параметров
	 * @return $XML DOMDocument
	 */
	public static function toRequest($ar){
		if(!$ar) return false;
		$xml = new CXmlAsArray($ar);
		return $xml->getXML();
	}

	/**
	 * Сортирует массив $ar в соответствии с массивом ключей $sort
	 * @param array $ar Сортируемый массив
	 * @param array $sort Массив ключей
	 */
	public static function sort($ar,$sort){
		foreach($sort as $s){
			if($ar[$s]){
				$var[$s] = $ar[$s];
			}
		}
		return $var;
	}

	/**
	 * Запрос локаций<br>
	 * Возвращает все страны и соответствующие города, доступные для поиска. Запрос не имеет параметров.
	 * @static
	 * @return array
	 */
	public static function GetLocations(){
//		$CACHE_ID = 'getLocations';
//		$CAHCE_DIR = '/cache/php/TravelmenuAPI/';
//		$obCache = new CPHPCache;
//		if($_GET['clear_cache']=='Y'){$obCache->CleanDir($CAHCE_DIR);};
//		if($obCache->StartDataCache(self::CACHE_TIME, $CACHE_ID, $CAHCE_DIR)){
			$res = self::fullRequest('GetLocationsRequest');
			foreach($res['GetLocationsResponse']['Countries']['Country'] as $co){
				$loc[] = (array)$co;
			}
//			$obCache->EndDataCache(array("loc" => $loc)); // помним в кеш
//		} else{
//			$arVars = $obCache->GetVars(); // берем кеш
//			$loc = $arVars["loc"]; // в удобную переменную
//		}

		return $loc;
	}
}




/**
 * Представление XML документа в виде массива и наоборот,<br>
 * Зависит от того, что передать в конструктор(массив либо XML)
 * @author WeXpert MATIASH
 * @version 1.0
 */
class CXmlAsArray{

	/**
	 * Мндикатор - что было загружено
	 * @var string
	 */
	public $is;

	/**
	 * Массив в виде XML
	 * @var SimpleXMLElement
	 */
	public $xml;

	/**
	 * XML в виде массива
	 * @var Array
	 */
	public $array;

	/**
	 * Имя элемента массива, в котором содержатся аттрибуты тега.
	 * @var String
	 */
	public $attr_nm='@attributes';

	/**
	 * Версия выходного XML документа
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * Кодировка выходного XML документа
	 * @var string
	 */
	public $encoding = 'UTF-8';

	/**
	 * Массив тегов, которые нужно пропускать
	 * @var Array
	 */
	public $skip = array();

	/**
	 * <b>Загружает XML из файла или текста</b>
	 * @param string $data Файл или текст, определяется автоматически, но можно указать явно в параметре $is_url.
	 * @param int $op=null Дополнительные опции (additional Libxml parameters)<br>
	 * <i>Некоторые необходимые константы</i>:<br>
	 * &nbsp;&nbsp;LIBXML_DTDVALID - Validate with the DTD;<br>
	 * &nbsp;&nbsp;LIBXML_NOBLANKS - Remove blank nodes;<br>
	 * &nbsp;&nbsp;LIBXML_NOCDATA - Merge CDATA as text nodes;<br>
	 * &nbsp;&nbsp;LIBXML_NOEMPTYTAG - Expand empty tags (e.g. [br/] to [br][/br]);<br>
	 * &nbsp;&nbsp;LIBXML_NOERROR - Suppress error reports<br>
	 * &nbsp;&nbsp;LIBXML_NOWARNING - Suppress warning reports<br>
	 * &nbsp;&nbsp;LIBXML_NOXMLDECL - Drop the XML declaration when saving a document<br>
	 * &nbsp;&nbsp;LIBXML_NSCLEAN - Remove redundant namespaces declarations<br>
	 * @param bool $is_url=false Является или нет ссылкой
	 * @param string $ns='' Namespace префикс или URI
	 * @param bool $is_prefix=false TRUE - если $ns это префикс, иначе URI
	 * <br><br><br>
	 *
	 * <b>Либо загружает массив из которого в дальнейшем будет делаться XML</b><br>
	 * @param array $ar Массив который будет преобразован в XML
	 * @param string $root_name='root' Название корневой ноды
	 * @param array $prms=array() Параметры корневой ноды
	 */
	public function __construct($data){
		$args = func_get_args();
		if(is_array($args[0])){
			if(!isset($args[1])) $args[1] = 'ROOT'; // Название корневой ноды
			if(!isset($args[2])) $args[2] = array(); // Параметры корневой ноды
			$this->is = 'array';
			$this->array = $args[0];
		} else{
			$this->is = 'xml';
			if(!isset($args[1])) $args[1] = false;	// Дополнительные опции
			if(!isset($args[2])) $args[2] = false;	// Является или нет ссылкой
			if(!isset($args[3])) $args[3] = '';		// Namespace префикс или URI
			if(!isset($args[4])) $args[4] = false;	// TRUE - если $ns это префикс, иначе URI
			if(!$args[2] && is_file($args[0]) && is_readable($args[0])){ // проверяем файл или XML-строка
				$args[2] = true;
			}
			$this->xml = new SimpleXMLElement($args[0],$args[1],$args[2],$args[3],$args[4]);
			if(!$this->xml){
				$this->xml = new SimpleXMLElement($args[0],$args[1],false,$args[3],$args[4]);
			}
		}
	}

	/**
	 * Конвертирует SimpleXMLElement объект в массив
	 * @param bool $root Прпопускать ли корневую ветку
	 * @param $obj Объект, по умолчанию $this->xml, только для рекурсии
	 * @param bool $rt Только для рекурсии
	 * @return array Возвращает массив
	 */
	public function toArray($root=false, $obj=false, $rt=false) {
		if(!$this->xml) return false;
		$arrData = array();
		if($obj===false){
			$obj = $this->xml;
		}
		if(is_object($obj)){
			$obj = get_object_vars($obj);
		}

		if(is_array($obj)){
			foreach($obj as $index => $value){
				if(is_object($value) || (is_array($value)/* && !empty($value)*/)){
					$value = $this->toArray($root, $value, true);
				}
				if(in_array($index, $this->skip)){
					continue;
				}
				if($this->attr_nm != '@attributes'){
					if($index == '@attributes'){
						$index = $this->attr_nm;
					}
				}
				$arrData[$index] = $value;
			}
		}
		if($rt){
			return $arrData;
		} else{
			if($root){
				$this->array = $arrData;
			} else{
				$this->array = array($this->xml->getName() => $arrData);
			}
		}
	}

	/**
	 * Конвертирует массив $this->array в объект SimpleXMLElement
	 * @param string $r_nm Имя корневой директории.<br>
	 * По умолчанию первый элементв массива, если массив состоит из 1го корневого элемента
	 * @param unknown_type $attr Аттрибуты корневой директории
	 */
	public function toXML($fr=true,$r_nm='root',$attr=array()){
		if(!is_array($this->array)) return false;
		if($fr === true && sizeof($this->array) == 1){
			$r_nm = key($this->array);
			$this->array = $this->array[ key($this->array) ];
		}
	    $xml = new SimpleXMLElement("<?xml version=\"{$this->version}\" encoding=\"{$this->encoding}\" ?><$r_nm></$r_nm>");
	    if(!empty($attr)){
	    	foreach($attr as $n=>$v){
	    		$xml->AddAttribute($n,$v);
	    	}
	    }
	    $this->xml = $this->buildXml($xml,$this->array);
	}

	/**
	 * Конвертирует в массив и возвращает его.
	 * @param bool $root Прпопускать ли корневую ветку
	 */
	public function getArray($root=false){
		$this->toArray($root);
		return $this->array;
	}

	/**
	 * Конвертирует в SimpleXMLElement и возвращает его как строку XML
	 * @param string $r_nm Имя корневого элемента
	 * @param array $attr Аттрибуты корневого элемента
	 */
	public function getXML($fr=true,$r_nm='root',$attr=array()){
		$this->toXML($fr,$r_nm,$attr);
		return $this->xml->asXML();
	}

	/**
	 * Конвертирует в SimpleXMLElement и выводит его в браузер как строку XML, с правлиьным Content-Type
	 * @param string $r_nm='root' Имя корневого элемента
	 * @param array $attr=array Аттрибуты корневого элемента
	 * @param string $hd=true Заголовок 'Content-Type:text/xml' если $hd=true
	 */
	public function showXML($fr=true,$r_nm=true,$attr=array(),$hdr=true){
		$this->toXML($fr,$r_nm,$attr);
		if($hdr){
	 		if($hdr===true) $hdr = 'Content-Type:text/xml';
			header($hdr);
		}
		echo $this->xml->asXML();
	}

	/**
	 * Рекурсивная функция, прикрепляет массив $ar к XML-элементу $xml
	 * @param SimpleXMLElement $xml наращиваемый SimpleXMLElement
	 * @param array $ar прикрепляемый массив
	 * @param string $rn имя корневой директории, нужно для рекурсии
	 */
	protected function buildXml($xml, $ar, $rn=''){
		if(is_array($ar[ $this->attr_nm ]) && !empty($ar[ $this->attr_nm ]) && $ar[ $this->attr_nm ]){
			foreach($ar[ $this->attr_nm ] as $ak=>$av){
				$xml->AddAttribute($ak,$av);
			}
			unset($ar[ $this->attr_nm ]);
		}
		if(is_array($ar) && !empty($ar)){
			if($this->is_numeric_array($ar)){
				foreach($ar as $v){
					if(is_array($v)){
						$kc = $xml->addChild($rn);
						$xml->addChild($this->buildXml($kc, $v, $rn));
					} else{
						$xml->addChild($this->buildXml($xml, $v, $rn));
					}
				}
			} else{
				foreach($ar as $k=>$v) {
					if(is_array($v)) {
						if($this->is_numeric_array($v)){
							$xml->addChild($this->buildXml($xml, $v, $k));
						} else{
							$cc = $xml->addChild($k);
							$xml->addChild($this->buildXml($cc, $v, $k));
						}
					} else{
						$xml->addChild($k, $v);
					}
				}
			}
		} else{
			$xml->addChild($rn,$ar);
		}
		return $xml;
	}

	/**
	 * Проверяет если массив числовой(не ассоциативный)
	 * @param array $var
	 */
	public static function is_numeric_array($var){
		$k = array_keys($var);
		$ml = count($var)-1;
		return (($k[0] === 0) && ($k[$ml] == $ml));
	}

}
?>
