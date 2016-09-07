<?
/**
 * Класс для работы с TravelmenuTourAPI, который позволяет осуществлять поиск и бронирование туров (http://www.travelmenu.ru/)
 * @author Матяш Сергей WEB-XPERT
 */
class TravelmenuTourAPI{
	/**
	 * Рекомендуемый(TravelmenuTourAPI) период кеширования 1 день
	 */
	const CACHE_TIME = 86400;
	/**
	 * E-mail
	 */
	const EMAIL = 'test@travelmenu.com';
	/**
	 * Дебажить запросы или нет<br>
	 * Если true, то ссылка на каждый запрос к travelmenu выводится в консоль
	 * @var bool
	 */
	public static $DEBUG = false;
	/**
	 * @var array Массив ошибок
	 */
	public static $ERRORS = array(
		'travel' => array(
			'1' => 'Внутренняя ошибка. Обратитесь в поддержку',
			'2' => 'Входные параметры не прошли валидацию',
			'3' => 'Предоставленный код поиска устарел – для актуализации/бронирования необходимо сделать новый поиск',
			'4' => 'Запрошенный метод (актуализация, бронирование) не поддерживается для данного тура',
			'5' => 'Тур не может быть забронирован, т. к. его цена или доступность изменилась с момента поиска. Н необходимо сделать новый поиск',
			'6' => 'Ошибка подключения к туроператору',
		),
		'empty' => 'По запросу ничего не найдено.'
	);
	/**
	 * @var array Классы перелета
	 */
	public static $AIR_CLASSES = array(
		'1' => 'Эконом',
		'2' => 'Первый',
		'3' => 'Бизнес',
	);
	/**
	 * @var array Типы питания
	 */
	public static $EAT_TYPES = array(
		'RO' => 'Без питания',
		'BB' => 'Завтрак',
		'HB' => 'Полупансион',
		'FB' => 'Полный пансион',
		'AI' => 'Все включено',
	);
	/**
	 * @var array Поддерживаемые коды валют
	 */
	public static $CURR_CODES = array(
		'RUR' => 'Российский рубль',
		'USD' => 'Доллар',
		'EUR' => 'Евро',
	);

	/**
	 * @var bool Последняя ошибка
	 */
	public static $LAST_ERROR = false;
	/**
	 * @var string Хост сервиса
	 */
	private static $HOST = 'http://cas1.travelmenu.com/';
	/**
	 * @var bool URL последнего запроса
	 */
	private static $url = false;

	/**
	 * Cправочник локаций (кешируемый на день)
	 * @static
	 * @return bool Возвращает все страны и города, доступные для поиска<br><br>
	 * departureCities - Массив городов вылета<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; cityCode - Код для поиска <br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; cityName - Название города <br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; countryCode - Код страны <br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; countryCode - Массив кодов стран, куда возможен вылет из данного города <br>
	 * arrivalCountries - Массив стран прибытия<br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; countryCode - Код страны для поиска <br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; countryName - Название страны <br>
	 * &nbsp;&nbsp;&nbsp;&nbsp; cities - Массив городов прибытия <br>
	 * <br><br>
	 * <b>Возвращает массив</b>
	 */
	public static function getLocations(){
		self::$url = 'rest/getLocations?email='.self::EMAIL;
		$CACHE_ID = 'getLocations';
		$CAHCE_DIR = '/cache/php/TravelmenuTourAPI/';
		$obCache = new CPHPCache;
		if($obCache->StartDataCache(self::CACHE_TIME, $CACHE_ID, $CAHCE_DIR)){

			$res = self::getData();

			foreach($res->getLocations->arrivalCountries as &$co){
				$co->countryName = self::t($co->countryName,'CO');
				if(sizeof($co->cities)){
					foreach($co->cities as &$ci){
						$ci->cityName = self::t($ci->cityName,'CI');
					}unset($ci);
				}
			}unset($co);

			foreach($res->getLocations->departureCities as &$ci){
				$ci->cityName = self::t($ci->cityName,'CI');
			}unset($ci);

			$res = self::objTOarray($res);
			$res = $res['getLocations'];

			$obCache->EndDataCache(array("res" => $res)); // помним в кеш
		} else{
			$arVars = $obCache->GetVars(); // берем кеш
			$res = $arVars["res"]; // в удобную переменную
		}

		return $res;
	}

	/**
	 * Справочник отелей (кешируемый на день)
	 * @static
	 * @param $params (array) Параметры запроса<br><br>
	 * arrivalCountryCode - Код страны прибытия, полученный из запроса getLocations. Необязательный параметр<br>
	 * arrivalCityCode - Код города прибытия, полученный из запроса getLocations.  Код города является уникальным для всех стран. Необязательный параметр<br>
	 * Если arrivalCountryCode и arrivalCityCode не указаны, будут возвращены все отели. Ответ содержит массив объектов городов, каждый из которых содержит массив объектов отелей в городе.<br>
	 * @return bool Возвращает список отелей, доступных для поиска.<br>
	 * Объект отеля содержит:<br>
	 * code - Код для поиска<br>
	 * name - Название<br>
	 * grade - Количество звезд. От 1 до 5<br>
	 */
	public static function getHotels($params){
		self::$url = 'rest/getHotels?email='.self::EMAIL.'&'.self::makeParams($params);

		$CACHE_ID = 'getHotels';
		$CACHE_DIR = '/cache/php/TravelmenuTourAPI/';
		$obCache = new CPHPCache;
		if($obCache->StartDataCache(self::CACHE_TIME, $CACHE_ID, $CACHE_DIR)){

			$res = self::getData();
			$res = self::objTOarray($res);

			$obCache->EndDataCache(array("res" => $res)); // помним в кеш
		} else{
			$arVars = $obCache->GetVars(); // берем кеш
			$res = $arVars["res"]; // в удобную переменную
		}

		return $res;
	}

	/**
	 * Инициализация асинхронного поиска туров
	 * @static
	 * @param $params (array) Параметры запроса<br><br>
	 * departureCityCode - Код города вылета, полученный из справочника<br>
	 * arrivalCountryCode - Код страны прибытия, полученный из справочника<br>
 	 * arrivalCityCodes	- Коды городов прибытия, полученные из справочника. Необязательный параметр. Если не указаны, поиск осуществляется по всей стране<br>
 	 * hotelCodes - Коды отелей в городах прибытия, полученные из справочника. Необязательный параметр. Если не указаны, поиск осуществляется во всех отелях<br>
 	 * departureDate - Дата вылета<br>
 	 * departureDateInterval - Гибкие даты вылета, количество +- дней относительно даты вылета. Необязательный параметр. Если не указан, поиск осуществляется только на одну дату вылета<br>
 	 * nightsCount - Минимальное количество ночей во всех отелях тура<br>
 	 * nightsCountTo - Максимальное количество ночей во всех отелях тура<br>
 	 * travelerAges - Возраста туристов на момент даты вылета.  Турист до 17 лет включительно считается ребенком, 18 означает 18 лет и более.<br>
 	 * gradeCodes - Количество звезд отеля. Массив из элементов от 1 до 5. Необязательный параметр<br>
 	 * boardCodes - Типы питания. Необязательный параметр. Если не указан, выдаются туры со всеми типами питания. См. Типы питания<br>
 	 * currencyCode - Код валюты. См. Поддерживаемые коды валют<br>
 	 * isHotelAvail - Искать только туры, где есть места в отеле<br>
 	 * isFlightIncluded - Искать только туры, в которые включен перелет<br>
 	 * isAirTicketsAvail - Искать только туры, где есть авиабилеты<br>
	 * @return bool Результатом выполнения является массив:<br>
	 * request - Параметры поиска<br>
	 * searchCode - Код асинхронного поиска, необходимый для получения результатов поиска
	 */
	public static function startSearch($params){
		self::$url = 'rest/startSearch?email='.self::EMAIL.'&'.self::makeParams($params);
		return self::getData();
	}

	/**
	 * Получение текущих результатов асинхронного поиска
	 * @static
	 * @param $params (array) Параметры запроса<br><br>
	 * searchCode - Код поиска, полученный из запроса  startSearch<br>
	 * lastCheckTStamp - Unix timestamp, когда последний раз были запрошены текущие результаты поиска. Возвращается в запросе getCurrentResults. Необязательный параметр. Если передан, туры выдаются, если с момента последней проверки были найдены новые. Обратите внимание, что выдаются не только новые туры, а все, в том числе новые. Если с момента последней проверки новые туры не были найдены, массив туров в результате будет пустым. Если параметр не передан, выдаются все найденные на данный момент туры.<br>
	 * @return bool Запрос возвращает объект:<br>
	 * request - Параметры поиска<br>
	 * lastCheckTStamp - Unix timestamp. Используется в последующих запросах, если необходимо получать туры только если найдены новые с момента lastCheckTStamp<br>
	 * searchCode - Код поиска<br>
	 * processedPercent - Процент завершенности поиска. От 0 до 100. Чтобы получить все найденные туры, необходимо делать запрос getCurrentResults, пока processedPercent не достигнет 100. Рекомендуемая частота запроса 4 секунды.<br>
	 * tours - Массив объектов туров<br>
	 * <b>Объект  тура:</b><br>
	 * name - Название тура<br>
	 * code - Код, необходимый для бронирования<br>
	 * departureDate - Дата вылета. Поле может отсутствовать, т. к. в туре известна дата вылета или дата заезда в отель<br>
	 * price - Объект цены. Поле value содержит сумму, поле currencyCode – код валюты. Код валюты может не соответствовать запрошенному при поиске, если оператор не поддерживает указанную валюту. Но в любом случае это будет одна из поддерживаемых валют. См. Поддерживаемые коды валют<br>
	 * commentsText - Комментарии. Поле может отсутствовать<br>
	 * hotels - Массив объектов отелей<br>
	 * ticketBusinessBackCount - Количество авиабилетов бизнес класса обратно. Поле отсутствует, если билетов нет. См. Квоты<br>
	 * ticketBusinessToCount - Количество авиабилетов бизнес класса туда. Поле отсутствует, если билетов нет<br>
	 * ticketFirstBackCount - Количество авиабилетов первого класса обратно. Поле отсутствует, если билетов нет.<br>
	 * ticketFirstToCount - Количество авиабилетов первого класса туда. Поле отсутствует, если билетов нет.<br>
	 * ticketEconomyBackCount - Количество авиабилетов эконом класса обратно.<br>
	 * ticketEconomyToCount - Количество авиабилетов эконом класса туда.<br>
	 * operatorName - Название туроператора<br>
	 * isActualizeSupported - Поддерживает ли данный тур запрос актуализации<br>
	 * <b>Объект отеля:</b><br>
	 * accomodation - Название размещения. Например:  2 adults + 1 child. Поле может отсутствовать<br>
	 * boardCode - Питание<br>
	 * checkinDate - Дата заезда. Может отсутствовать, в этом случае указана дата вылета<br>
	 * code - Код отеля<br>
	 * nightsCount - Количество ночей<br>
	 * roomName - Название номера. Например: double<br>
	 * availCount - Количество мест в отеле. См. Квоты<br>
	 * vendorHotelGrade - Количество звезд отеля у туроператора<br>
	 * vendorHotelName - Название отеля у туроператора<br>
	 * vendorCityName - Название города у туроператора<br>
	 * countryCode - Код страны из справочника<br>
	 * cityCode - Код города из справочника<br><br>
	 * Поля vendorHotelGrade, vendorHotelName, vendorCityName могут не совпадать с соответствующими полями из справочников, т. к. справочники содержат внутренние коды/названия travelmenu, в соответствие которым ставятся данные от туроператоров.  Для избежания неточностей клиенту при заказе необходимо отображать данные от туроператора, а не из справочников.
	 */
	public static function getCurrentResults($params){
		self::$url = 'rest/getCurrentResults?email='.self::EMAIL.'&'.self::makeParams($params);
		return self::getData();
	}

	/**
	 * Получение подробных данных о туре
	 * @static
	 * @param $params (array) Параметры запроса<br><br>
	 * searchCode - Код поиска. Код действителен в течении часа после поиска. Если код устарел, необходимо сделать новый поиск<br>
	 * tourCode - Код тура, полученный из запроса getCurrentResults<br>
	 * @return bool Результатом является объект тура, который содержит все поля из запроса getCurrentResults, а также:<br><br>
	 * flightsTo - Варианты перелета туда. Поле может отсутствовать<br>
	 * flightsBack - Варианты перелета обратно. Поле может отсутствовать<br>
	 * insurances - Двумерный массив объектов страховок. Бронировать можно один из вариантов в каждой группе. Поле может отсутствовать<br>
	 * transfers - Двумерный массив объектов трансферов. Бронировать можно один из вариантов в каждой группе. Поле может отсутствовать<br>
	 * hotels - Массив отелей<br>
	 * <b>Объект перелета содержит:</b><br>
	 * class - Класс. См. Классы перелетов<br>
	 * code - Код для бронирования<br>
	 * trips - Массив рейсов. Будет содержать несколько элементов, если есть пересадки<br>
	 * <b>Объект рейса:</b><br>
	 * number - Номер рейса. Поле может отсутствовать<br>
	 * departureAirportCode - IATA код аэропорта вылета. Поле может отсутствовать<br>
	 * arrivalAirportCode - IATA код аэропорта прибытия. Поле может отсутствовать<br>
	 * departureLocationName - Город/аэропорт отправления<br>
	 * arrivalLocationName - Город/аэропорт прибытия<br>
	 * departureDateTime - Дата и время вылета. Поле может отсутствовать<br>
	 * arrivalDateTime - Дата и время прибытия. Поле может отсутствовать<br>
	 * time - Время в пути в формате hh:mm. Поле может отсутствовать<br>
	 * planeName - Тип самолета. Поле может отсутствовать<br>
	 * availCount - Количество авиабилетов. См. Квоты<br>
	 * <b>Объект страховки содержит:</b><br>
	 * code - Код для бронирования<br>
	 * name - название<br>
	 * fromDate - Дата начала<br>
	 * toDate - Дата окончания<br>
	 * <b>Объект трансфера содержит:</b><br>
	 * code - Код для бронирования<br>
	 * name - название<br>
	 * date - дата<br>
	 * <b>Объект отеля, кроме полей из запроса getCurrentResults также содержит:</b><br>
	 * checkinDate - Дата заезда<br>
	 * checkOutDate - Дата выезда<br>
	 */
	public static function actualizeTour($params){
		self::$url = 'rest/actualizeTour?email='.self::EMAIL.self::makeParams($params);
		return self::getData();
	}

	/**
	 * Основной метод получающий данные от сервиса
	 * @static
	 * @return bool Возвращает массив(преобразованный json-объект)
	 */
	private function getData(){
		if(class_exists('Cconsole') && self::$DEBUG==true){
			Cconsole::info('trm_tour_request: ', self::$HOST.self::$url);
		}
		$ret = json_decode(file_get_contents(self::$HOST.self::$url));
		if(!$ret || empty($ret)){
			self::$LAST_ERROR = self::$ERRORS['empty'];
			return false;
		} elseif($ret->error->code > 0){
			self::$LAST_ERROR = self::$ERRORS['travel'][ $ret->error->code ].': '.$ret->error->message;
			return false;
		}
		return $ret;
	}

	/**
	 * Конвертирует массив параметров в строку GET формата
	 * @static
	 * @param $ar Массив параметров
	 * @param bool $nm Имя надмассива
	 * @return string Строка в форме гет запроса без знака ?
	 */
	public static function makeParams($ar, $nm=false){
		foreach($ar as $k=>$v){
			if(is_array($v)){
				$ret[] = self::makeParams($v, $k);
			} elseif($v!==false){
				if($nm){
					if(is_numeric($k)){
						$ret[] = $nm.'[]='.$v;
					} else{
						$ret[] = $nm.'['.$k.']='.$v;
					}
				} else{
					$ret[] = "$k=$v";
				}
			}
		}
		return implode('&',$ret);
	}

	/**
	 * Переводит строку если такая есть в таблице travelmenu_transitions<br>
	 * <i>пока только города и страны</i>
	 * @static
	 * @param array $arF Фильтры (можно и LIKE если ключ начинается с %)
	 * @param array $t CI-город, или CO-страна
	 * @return bool CDBResult
	 */
	public static function t($str,$t=false){
		$arf = array('EN'=>$str);
		if($t=='CI' || $t='CO'){
			$arf['IT'] = $t;
		}
		$db = self::getInfoFromTable($arf,'travelmenu_transitions');
		if($ar = $db->Fetch()){
			if(trim($ar['RU'])!='' && $ar['RU']!='-'){
				return $ar['RU'];
			}
		}
		return $str;
	}

	/**
	 * Обновляет таблицу travelmenu_transitions из csv файлов<br>
	 * tm_city_translation.csv, tm_country_translation.csv
	 * @static
	 * @param bool $dbg Выводить ли каждый запрос к базе в барузер
	 */
	public static function refreshTransitions($dbg=false){
		global $DB;

		$tbls = $DB->Query('SHOW TABLES LIKE "travelmenu_transitions"');
		// добавим таблицы в базу
		if($tbls->AffectedRowsCount()<1){
			$added = $DB->RunSqlBatch(dirname(__FILE__)."/travelmenu_files/travelmenu_transitions.sql");
		}
		if($dbg){
			echo '<pre>'; print_r($added); echo '</pre>';
		}

		$arCi = self::csv_parser(dirname(__FILE__)."/travelmenu_files/tm_city_translation.csv",',');
		if(sizeof($arCi)>0){
			$DB->Query('DELETE FROM travelmenu_transitions WHERE IT = "CI"');
			foreach($arCi as $ci){
				// добавляем страну
				$ci_r = 'INSERT INTO travelmenu_transitions (EN,RU,IT) VALUES ("'.$DB->ForSql($ci[0]).'","'.$DB->ForSql($ci[1]).'","CI")';
				if($dbg){
					echo '<pre>'; print_r($ci_r); echo '</pre>';
				}
				$DB->Query($ci_r);
			}
		}

		$arCo = self::csv_parser(dirname(__FILE__)."/travelmenu_files/tm_country_translation.csv",',');
		if(sizeof($arCo)>0){
			$DB->Query('DELETE FROM travelmenu_transitions WHERE IT = "CO"');
			foreach($arCo as $co){
				// добавляем страну
				$co_r = 'INSERT INTO travelmenu_transitions (EN,RU,IT) VALUES ("'.$DB->ForSql($co[0]).'","'.$DB->ForSql($co[1]).'","CO")';
				if($dbg){
					echo '<pre>'; print_r($co_r); echo '</pre>';
				}
				$DB->Query($co_r);
			}
		}
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
	 * Универсальный парсер CSV, все кроме самого файла определяет автоматом
	 * @param string $file_name Имя файла - file($file_name).
	 * @param string $dlm Разделитель столбцов.
	 * @return mixed
	 */
	public static function csv_parser($file_name, $dlm = ';') {
		$csv_lines = file($file_name);
		if(is_array($csv_lines)){
			$cnt = count($csv_lines);
			for($i = 0; $i < $cnt; $i++){
				$line = trim($csv_lines[$i]);
				$first_char = true;
				$col_num = 0;
				$length = strlen($line);
				for($b = 0; $b < $length; $b++){
					$ch = substr($line, $b, 1);
					if($skip_char != true){
						$process = true;
						if($first_char == true){
							if($ch == '"'){
								$terminator = '"'.$dlm;
								$process = false;
							} else{
								$terminator = $dlm;
							}
							$first_char = false;
						}

						if($ch == '"'){
							$next_char = substr($line, $b + 1, 1);
							if($next_char == '"'){
								$skip_char = true;
							} elseif($next_char == $dlm){
								if($terminator == '"'.$dlm){
									$first_char = true;
									$process = false;
									$skip_char = true;
								}
							} elseif($next_char == "\n" || $next_char == ""){
								$first_char = true;
								$process = false;
							}
						}

						if($process == true){
							if($ch == $dlm){
								if($terminator == $dlm){
									$first_char = true;
									$process = false;
								}
							}
						}

						if($process == true){
							$column .= $ch;
						}
						if($b == ($length - 1)){
							$first_char = true;
						}
						if($first_char == true){
							$values[$i][$col_num] = $column;
							$column = '';
							$col_num++;
						}
					} else{
						$skip_char = false;
					}
				}
			}
		}
		return $values;
	}

	public static function objTOarray($obj, $skip = array()) {
		$arrData = array();

		// if input is object, convert into array
		if(is_object($obj)){
			$obj = get_object_vars($obj);
		}

		if(is_array($obj)){
			foreach($obj as $index => $value){
				if(is_object($value) || is_array($value)){
					$value = self::objTOarray($value, $skip); // recursive call
				}
				if(in_array($index, $skip)){
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}
}
?>
