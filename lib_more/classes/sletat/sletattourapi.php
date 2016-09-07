<?
/**
 * Класс для работы с SletatTourAPI, который позволяет осуществлять поиск и бронирование туров ( http://sletat.ru/ )
 * @author Матяш Сергей WEB-XPERT
 * @version 1.0 14.08.2012
 */
class SletatTourAPI{

	/**
	 * Дебажить запросы или нет<br>
	 * Если true, то ссылка на каждый запрос к travelmenu выводится в консоль
	 * @var bool
	 */
	public static $DEBUG = false;
	/**
	 * Дебажные запросы для sletat или реальные<br>
	 * Если true, то в параметры подмешивается debug=1
	 * @var bool
	 */
	public static $SD = true;
	/**
	 * @var array Массив ошибок
	 */
	public static $ERRORS = array(
		'empty' => 'По запросу ничего не найдено.'
	);

	public static $MEALS = array(
		'0' => 'не важно',
		'AI' => 'завтраки, обеды, ужины, напитки',
		'BB' => 'завтраки',
		'FB' => 'завтраки, обеды, ужины',
		'FB+' => 'завтраки, обеды, ужины - расширенное меню',
		'HB' => 'завтраки, ужины',
		'HB+' => 'завтраки, ужины - расширенное меню',
		'RO' => 'без питания',
		'UAI' => 'завтраки, обеды, ужины, напитки - расширенное меню',
	);

	/**
	 * @var bool Последняя ошибка
	 */
	public static $LAST_ERROR = false;
	/**
	 * @var string Хост сервиса
	 */
	private static $HOST = 'http://module.sletat.ru/';
	/**
	 * @var string URL последнего запроса
	 */
	private static $url = '';
	/**
	 * @var string директория последнего запроса
	 */
	private static $dir = '';
	/**
	 * @var array параметры последнего запроса
	 */
	private static $params = array();


	/**
	 * Получение списка городов вылета
	 * @param bool $showcase(0) Включение режима выдачи для горящих туров.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор города.<br>
	 * Name str Название города.<br>
	 * DescriptionUrl str Ссылка на описание города.
	 */
	public static function GetDepartCities($showcase = 0, $dbg = 0) {
		self::$dir = 'Main.svc/GetDepartCities';
		self::$params['debug'] = $dbg;
		self::$params['showcase'] = $showcase;
		return self::getData();
	}

	/**
	 * Получение списка стран
	 * @param string $townFromId(false) Идентификатор города вылета.
	 * @param bool $showcase(0) Включение режима выдачи для горящих туров.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор страны.<br>
	 * Name str Название страны.<br>
	 * Alias str Текстовый код страны.<br>
	 * HasTickets bool Флаг «Есть ли билеты» по умолчанию для страны.<br>
	 * HotelIsInStop bool Флаг «На стопе ли отель» по умолчанию для страны.<br>
	 * Rank int Ранг страны (0 — самый высокий).<br>
	 * TicketsIncluded bool Флаг «Включены ли билеты» по умолчанию для страны.<br>
	 */
	public static function GetShowcaseReview($townFromId=false, $showcase=0, $dbg=0){
		self::$dir = 'Main.svc/GetShowcaseReview';
		self::$params['debug'] = $dbg;
		self::$params['showcase'] = $showcase;
		if($townFromId){
			self::$params['townFromId'] = $townFromId;
		}
		return self::getData();
	}

	/**
	 * Получение списка стран
	 * @param string $townFromId(false) Идентификатор города вылета.
	 * @param bool $showcase(0) Включение режима выдачи для горящих туров.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор страны.<br>
	 * Name str Название страны.<br>
	 * Alias str Текстовый код страны.<br>
	 * HasTickets bool Флаг «Есть ли билеты» по умолчанию для страны.<br>
	 * HotelIsInStop bool Флаг «На стопе ли отель» по умолчанию для страны.<br>
	 * Rank int Ранг страны (0 — самый высокий).<br>
	 * TicketsIncluded bool Флаг «Включены ли билеты» по умолчанию для страны.<br>
	 */
	public static function GetCountries($townFromId=false, $showcase=0, $dbg=0){
		self::$dir = 'Main.svc/GetCountries';
		self::$params['debug'] = $dbg;
		self::$params['showcase'] = $showcase;
		if($townFromId){
			self::$params['townFromId'] = $townFromId;
		}
		return self::getData();
	}

	/**
	 * Получение списка курортов
	 * @param int $countryId(0) Идентификатор страны.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор курорта.<br>
	 * Name str Название курорта.<br>
	 * DescriptionUrl str Ссылка на описание курорта.
	 */
	public static function GetCities($countryId, $dbg = 0) {
		self::$dir = 'Main.svc/GetCities';
		self::$params['debug'] = $dbg;
		self::$params['countryId'] = $countryId;
		return self::getData();
	}

	/**
	 * Получение списка отелей
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @param array $params Параметры запроса:<br>
	 * countryId * int Идентификатор страны.<br>
	 * towns int[] Идентификаторы городов, разделение запятой.<br>
	 * stars int[] Идентификаторы звёзд, разделение запятой.<br>
	 * filter str Поиск по подстроке среди названий.<br>
	 * all int Количество отелей к выдаче. Топовые сверху<br>
	 * @return Объект ответа:<br>
	 * Id int Идентификатор курорта.<br>
	 * Name str Название курорта.<br>
	 * Range float Ранг отеля.
	 */
	public static function GetHotels($params, $dbg = 0) {
		self::$dir = 'Main.svc/GetHotels';
		self::$params = $params;
		self::$params['debug'] = $dbg;
		return self::getData();
	}

	/**
	 * Получение списка курортов
	 * @param int $countryId * (0) Идентификатор страны.
	 * @param int $towns * Идентификаторы городов, разделение запятой.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор курорта.<br>
	 * Name str Название курорта.<br>
	 */
	public static function GetHotelStars($countryId, $towns, $dbg = 0) {
		self::$dir = 'Main.svc/GetHotelStars';
		self::$params['debug'] = $dbg;
		self::$params['countryId'] = $countryId;
		self::$params['towns'] = $towns;
		return self::getData();
	}

	/**
	 * Получение списка типов питаний
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор курорта.<br>
	 * Name str Название курорта.<br>
	 */
	public static function GetMeals($dbg=0){
		self::$dir = 'Main.svc/GetMeals';
		self::$params['debug'] = $dbg;
		return self::getData();
	}

	/**
	 * Получение списка туроператоров
	 * @param int $countryId См. свойство Enabled в ответе.
	 * @param bool $townFromId См. свойство Enabled в ответе.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Идентификатор туроператора.<br>
	 * Name str Название курорта.<br>
	 * Enabled str Есть ли вылеты по указанной паре townFromId-countryId.
	 */
	public static function GetTourOperators($countryId, $townFromId, $dbg = 0) {
		self::$dir = 'Main.svc/GetTourOperators';
		self::$params['debug'] = $dbg;
		self::$params['countryId'] = $countryId;
		return self::getData();
	}

	/**
	 * Получение списка географических объектов
	 * @param int $params <b>Параметры запроса:</b><br>
	 * countryId * int Идентификатор страны назначения.<br>
	 * cityFromId * int Идентификатор города вылета.<br>
	 * s_adults * int Количество взрослых.<br>
	 * s_kids * int Количество детей.<br>
	 * s_nightsMin * int Минимальное количество ночей.<br>
	 * s_nightsMax * int Максимальное количество ночей.<br>
	 * currencyAlias * str Валюта. Допустимые значения: USD, EUR, RUR.<br>
	 * s_departFrom * str Дата отправки в формате DD/MM/YYYY.<br>
	 * s_departTo * str Дата прибытия в формате DD/MM/YYYY.<br><br>
	 * filter int Фильтр. 0 — нет, 1 — по ТО.<br>
	 * f_to_id int Идентификатор ТО, по которому осуществлять фильтрацию.<br>
	 * fake bool Симулировать выдачу.<br>
	 * extend bool 1 — найти ещё цен (не все ТО поддерживают доп. цены по запросу).<br>
	 * requestId int Номер запроса для дозагрузки цен.<br>
	 * userId int Используется только для sletat.ru.<br>
	 * key GUID Устарело, не используется.<br>
	 * pageSize int Размер страницы, строк.<br>
	 * pageNumber int Номер страницы.<br>
	 * countryName str Название страны назначения. Параметр не обязателен.<br>
	 * cityFromName str Название города вылета. Параметр не обязателен.<br>
	 * cities int[] Список городов, разделение запятой.<br>
	 * cityNames str Устарело, не используется.<br>
	 * meals int[] Список питания, разделение запятой.<br>
	 * mealNames str Устарело, не используется.<br>
	 * stars int[] Список звёздностей, разделение запятой.<br>
	 * starNames str Устарело, не используется.<br>
	 * hotels int[] Список отелей, разделение запятой.<br>
	 * hotelNames str Устарело, не используется.<br>
	 * s_kids_ages int[] Список возрастов детей, разделение запятой.<br>
	 * s_priceMin int Минимальная цена.<br>
	 * s_priceMax int Максимальная цена.<br>
	 * visibleOperators str[] Список видимых ТО, разделение запятой.<br>
	 * hiddenOperators str[] Список скрытых ТО, разделение запятой.<br>
	 * 4requestTimeout int Время сбора данных по ТО перед отображением, c.<br>
	 * s_hotelIsNotInStop bool Продажа не приостановлена.<br>
	 * s_hasTickets bool Есть билеты.<br>
	 * s_ticketsIncluded bool Авиаперелёт включён в цену.<br>
	 * s_clearCache bool Очистить кэш. Передавайте 0 для нормальной работы.<br>
	 * s_showcase bool Передавайте “true” для горящих туров.<br>
	 * updateResult bool Получить свежие цены. Требует requestId.<br>
	 * includeDescriptions bool Флаг выдачи описаний размещения, отеля и питания.<br>
	 * includeOilTaxesAndVisa bool Флаг выдачи топливных сборов и виз.<br>
	 * @param bool $dbg(0) Включить дебаггер sletat.ru<br>
	 * @return <b>Объект ответа:</b><br>
	 * aaData[0] int Идентификатор цены.<br>
	 * aaData[1] int Шифрованый идентификатор ТО.<br>
	 * aaData[2] str Ссылка на описание отеля.<br>
	 * aaData[3] int Идентификатор отеля.<br>
	 * aaData[4] str Ссылка на описание курорта.<br>
	 * aaData[5] int Идентификатор курорта.<br>
	 * aaData[6] str Название тура.<br>
	 * aaData[7] str Название отеля.<br>
	 * aaData[8] str Звёздность.<br>
	 * aaData[9] str Тип комнаты (не размещение).<br>
	 * aaData[10] str Питание.<br>
	 * aaData[11] str Размещение.<br>
	 * aaData[12] str Дата отправки в формате DD.MM.YYYY.<br>
	 * aaData[13] str Дата прибытия в формате DD.MM.YYYY.<br>
	 * aaData[14] int Количество ночей.<br>
	 * aaData[15] str Цена + валюта.<br>
	 * aaData[16] int Количество взрослых.<br>
	 * aaData[17] int Количество детей.<br>
	 * aaData[18] str Название туроператора (только для партнёров sletat.ru).<br>
	 * aaData[19] str Название курорта.<br>
	 * aaData[20][0] str Ссылка на форму поиска ТО (только для партнёров sletat.ru).<br>
	 * aaData[21] enum Выпущен стоп на отель (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[22] enum Билеты включены в цену (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[23] enum Билеты эконом-класса туда (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[24] enum Билеты эконом-класса обратно (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[25] enum Билеты бизнес-класса туда (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[26] enum Билеты бизнес-класса туда (null – неизвестно, 0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[27] str Дата заезда в отель. Используйте только в текстовом представлении.<br>
	 * aaData[28] str Дата выписки из отеля. Используйте только в текстовом представлении.<br>
	 * aaData[29] str Ссылка на миниатюрную фотографию отеля.<br>
	 * aaData[30] int Идентификатор страны.<br>
	 * aaData[31] str Название страны.<br>
	 * aaData[32] int Идентификатор города вылета.<br>
	 * aaData[33] str Название города вылета.<br>
	 * aaData[34] str Ссылка на логотип туроператора.<br>
	 * aaData[35] float Рейтинг отеля от 0 до 10.<br>
	 * aaData[36] str Описание питания (выдаётся при includeDescriptions = true).<br>
	 * aaData[37] str Описание размещения (выдаётся при includeDescriptions = true).<br>
	 * aaData[38] str Описание отеля (выдаётся при includeDescriptions = true).<br>
	 * aaData[39] int Cистемный идентификатор размещения в отеле (если слинковано).<br>
	 * aaData[40] bool Флаг демо-режима, всегда true в демо режиме.<br>
	 * aaData[41] int Ситемный идентификатор питания  (если слинковано).<br>
	 * aaData[42] int Цена тура в виде числа (без прибавление к строке валюты).<br>
	 * aaData[43] str Валюта цены.<br>
	 * aaData[44] int Системный идентификатор типа номера в отеле  (если слинковано).<br>
	 * aaData[45] int Cистемный идентификатор категории отеля  (если слинковано).<br>
	 * aaData[46] int Kоличество фотографий для отеля (если отель слинкован).<br>
	 * aaData[47] str Ссылка на личный кабинет туроператора.<br><br>
	 * <b>Данные о топливных сборах (если includeOilTaxesAndVisa = 1):</b><br>
	 * x[0] int Идентификатор туроператора.<br>
	 * x[1] str Дата начала действия топливного сбора.<br>
	 * x[2] str Дата окончания действия топливного сбора.<br>
	 * x[3] int Стоимость.<br>
	 * x[4] str Валюта.<br>
	 * x[5] str Наименование перевозчика (авиакомпании).<br>
	 * x[6] str Наименование принимающей стороны.<br><br>
	 * <b>Данные о визах (если includeOilTaxesAndVisa = 1):</b><br>
	 * x[0] int Стоимость.<br>
	 * x[1] str Валюта.<br>
	 */
	public static function GetTours($params, $dbg = 0) {
		$params = self::prePrms($params);
		self::$dir = 'Main.svc/GetTours';
		self::$params['debug'] = $dbg;
		self::$params = $params;
		return self::getData();
	}

	/**
	 * Статус поиска
	 * @param int $requestId Идентификатор запроса.
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * Id int Шифрованый идентификатор ТО.<br>
	 * Name str Название ТО.<br>
	 * ExecutionTimeMs int Время, затраченное на поискпо данному ТО.<br>
	 * IsCached bool Получены ли данные из кэша.<br>
	 * IsError bool Была ли ошибка при исполнении поиск по данному ТО.<br>
	 * IsProcessed bool Закончен ли поиск по данному ТО.<br>
	 * IsTimeout bool Исчерпано ли разрешённое время при запросе к данному ТО.<br>
	 * RowsCount int Количество цен от ТО<br>
	 */
	public static function GetLoadState($requestId, $dbg = 0) {
		self::$dir = 'Main.svc/GetLoadState';
		self::$params['debug'] = $dbg;
		self::$params['requestId'] = $requestId;
		return self::getData();
	}

	/**
	 * Актуализация цены
	 * @param int $params Параметры запроса:<br>
	 * sourceId * int Шифрованый идентификатор ТО.
	 * offerId * int Идентификатор цены.
	 * currencyAlias * str Валюта. Допустимые значения: USD, EUR, RUR.
	 * countryId * int Идентификатор страны.
	 * requestId * int Номер запроса, из которого извлекли цену
	 * showcase bool Включение режима выдачи для горящих туров
	 * userId int Не важно. Используется только для sletat.ru
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * x.randomNumber int «Быстрый» номер тура в рамках запроса.<br>
	 * aaData[0] str Страна назначения.<br>
	 * aaData[1] str Город вылета.<br>
	 * aaData[2] str Курорт назначения.<br>
	 * aaData[3] str Название программы.<br>
	 * aaData[4] str Дата заезда в формате DD.MM.YYYY.<br>
	 * aaData[5] int Количество ночей.<br>
	 * aaData[6] str Название отеля.<br>
	 * aaData[7] str Не используется.<br>
	 * aaData[8] str Звёздность отеля.<br>
	 * aaData[9] str Тип номера.<br>
	 * aaData[10] str Зарезервировано.<br>
	 * aaData[11] str Тип питания.<br>
	 * aaData[12] bool Билеты включены в цену.<br>
	 * aaData[13] bool Выпущен стоп на отель.<br>
	 * aaData[14] bool Билеты эконом-класса туда (0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[15] bool Билеты эконом-класса обратно (0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[16] bool Билеты бизнес-класса туда (0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[17] bool Билеты бизнес-класса туда (0 — нет, 1 — есть, 2 — по запросу).<br>
	 * aaData[18] int Оригинальная цена.<br>
	 * aaData[19] int Цена в запрошенной валюте.<br>
	 * aaData[20] str Дополнительное описание тура.<br>
	 * aaData[21] str Название валюты оригинальной цены.<br>
	 * aaData[22] str Название размещения.<br>
	 * aaData[23] str Системное название валюты.<br>
	 * aaData[24] int ID туроператора для тех кому мы позволяем его видеть (или пустая строка)<br>
	 * aaData[25] str Название туроператора, если разрешено или пустая строка.<br>
	 * aaData[26] int ID страны или пустая строка, если запись не слинкована.<br>
	 * aaData[27] str Название страны или пустая строка, если запись не слинкована.<br>
	 * aaData[28] int ID города вылета или пустая строка, если запись не слинкована.<br>
	 * aaData[29] str Название города вылета или пустая строка, если запись не слинкована.<br>
	 * aaData[30] int ID курорта или пустая строка, если запись не слинкована.<br>
	 * aaData[31] str Название курорта или пустая строка, если запись не слинкована.<br>
	 * aaData[32] int ID отеля или пустая строка, если запись не слинкована.<br>
	 * aaData[33] str Название отеля или пустая строка, если запись не слинкована.<br>
	 * aaData[34] int ID звёздности или пустая строка, если запись не слинкована.<br>
	 * aaData[35] str Название звёздности или пустая строка, если запись не слинкована.<br>
	 * aaData[36] int ID типа номера или пустая строка, если запись не слинкована.<br>
	 * aaData[37] str Название типа номера или пустая строка, если запись не слинкована.<br>
	 * aaData[38] int ID питания или пустая строка, если запись не слинкована.<br>
	 * aaData[39] str Название питания или пустая строка, если запись не слинкована.<br>
	 * aaData[40] int ID размещения или пустая строка, если запись не слинкована.<br>
	 * aaData[41] str Название размещения или пустая строка, если запись не слинкована.<br>
	 * aaData[42] str Ссылка на туроператора, если разрешено или пустая строка.<br>
	 * aaData[43] str Ссылка на отель.<br>
	 * aaData[44] str Ссылка на первую фотографию отеля.<br>
	 * aaData[45] int Количество доступных фотографий отеля.<br>
	 */
	public static function ActualizePrice($params, $dbg = 0) {
		self::$dir = 'Main.svc/ActualizePrice';
		self::$params = $params;
		self::$params['debug'] = $dbg;
		return self::getData();
	}

	/**
	 * Заказ путёвки
	 * @param int $params Параметры запроса:<br>
	 * userId int Не важно. Используется только для sletat.ru<br>
	 * requestId * int Номер запроса, из которого извлекли цену.<br>
	 * sourceId * int Шифрованый идентификатор ТО.<br>
	 * offerId * int Идентификатор цены.<br>
	 * user * str Имя заявителя.<br>
	 * email * str Электронная почта заявителя.<br>
	 * phone * str Телефон заявителя.<br>
	 * info * str Дополнительная информация от заявителя.<br>
	 * countryName * str Название страны.<br>
	 * cityFromName * str Название города вылета.<br>
	 * currencyAlias * str Валюта. Допустимые значения: USD, EUR, RUR.<br>
	 * key GUID Устарело, не используется.<br>
	 * @param bool $dbg(0) Включить дебаггер sletat.ru<br>
	 * @return Объект ответа:<br>
	 * x.IsError bool Ошибка во время сохранения заявки.
	 */
	public static function SaveTourOrder($params, $dbg = 0) {
		self::$dir = 'Main.svc/SaveTourOrder';
		self::$params = $params;
		self::$params['debug'] = $dbg;
		return self::getData();
	}

	/**
	 * Сообщение на сервер об ошибке
	 * @param array $params Параметры запроса:<br>
	 * requestId * int Номер запроса, в котором возникла ошибка.<br>
	 * currencyAlias * str Валюта. Допустимые значения: USD, EUR, RUR.<br>
	 * countryId * int Идентификатор страны.<br>
	 * dptCityId * int Идентификатор города вылета.<br>
	 * sourceId * int Шифрованый идентификатор туроператора. <br>
	 * offerId * int Идентификатор цены.<br>
	 * errorType * int Тип ошибки (0 — общее, 1 — описание, 2 — цена, 4 — оба)<br>
	 * userId int Не важно. Используется только для sletat.ru.<br>
	 * key GUID Устарело, не используется.<br>
	 * errorMessage str Текст сообщения об ошибке.<br>
	 * @param bool $dbg(0) Включить дебаггер sletat.ru
	 * @return Объект ответа:<br>
	 * IsError int Ошибка во время отправки сообщения.<br>
	 */
	public static function ReportError($params, $dbg = 0) {
		self::$dir = 'Main.svc/ReportError';
		self::$params = $params;
		self::$params['debug'] = $dbg;
		return self::getData();
	}

	/**
	 * Основной метод получающий данные от сервиса
	 * @static
	 * @return bool Возвращает преобразованный json-объект
	 */
	private static function getData(){
		if(self::$DEBUG){
			self::$params['debug'] = 1;
			if(!self::$SD){
				unset(self::$params['debug']);
			}
		}
		self::makeLink();
		if(class_exists('Cconsole') && self::$DEBUG){
			Cconsole::info('['.self::$dir.']_request: ', self::$url);
		}
//		$content = QueryGetData(self::$HOST, '80', self::$url, )
		$ret = json_decode(file_get_contents(self::$url));
		if(!$ret || empty($ret)){
			self::$LAST_ERROR = self::$ERRORS['empty'];
			return false;
		}
		if($xml = simplexml_load_string($ret)){
			if($xml->Reason->Text){
				self::$LAST_ERROR = $xml->Reason->Text;
				return false;
			}
		}
		if(class_exists('Cconsole') && self::$DEBUG){
			Cconsole::info('['.self::$dir.']_response: ', $ret);
		}
		return $ret;
	}

	/**
	 * Подготавливает параметры для поиска
	 * @param $ar Массив параметров для поиска из гета или поста
	 * @return array Сформированный массив параметров для поиска
	 */
	public static function prePrms($ar){
		foreach($ar as $k => $v){
			if(is_array($v)){
				$v = implode(',',$v);
			}
			if(strpos($k,'[')!==false){
				$k = preg_replace('#^[\S]+\[([\S]+)\]?$#', '$1', $k, 1);
			}
			if($k == 's_departFrom' || $k == 's_departTo'){
				if(!preg_match('#\d{2}/\d{2}/\d{4}#', $v)){
					$v = preg_replace('#(\d{2}).(\d{2}).(\d{4})#', '$1/$2/$3', $v);
				}
			}
			$params[ $k ] = $v;
		}
		if(!$params['s_departTo']){
			$params['s_departTo'] = '15/09/2012';
		}
		if(!$params['currencyAlias']){
			$params['currencyAlias'] = 'RUR';
		}
//		echo '<pre>'; print_r($params); echo '</pre>';
		return $params;
	}

	/**
	 * Создает ссылку для запроса на sletat.ru
	 */
	private static function makeLink(){
		self::$url = self::$HOST.self::$dir.'?'.self::makeParams();
	}

	/**
	 * Конвертирует массив параметров в строку GET формата
	 * @static
	 * @param $ar Массив параметров
	 * @param bool $nm Имя надмассива
	 * @return string Строка в форме гет запроса без знака ?
	 */
	public static function makeParams($ar=false, $nm=false){
		if(!$ar && !empty(self::$params)){
			$ar = self::$params;
		}
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

	public static function fgContents(){
		$path = '/Main.svc/GetMeals';
		$host = 'module.sletat.ru';
		$url = 'yoo.wexpert.ru';
		$data = '';
		$response = '';
		$headers = '';
		$fp = fsockopen($host, 80, $errno, $errstr, 10);
		if($fp){
			$out = "GET ".$path." HTTP/1.1\n";
			$out .= "Host: ".$host."\n";
			$out .= "Referer: ".$url."/\n";
			$out .= "User-Agent: Opera\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\n";
			$out .= "Content-Length: ".strlen($data)."\n\n";
			$out .= $data."\n\n";

			fputs($fp, $out);

			while($gets = fgets($fp, 2048)){
				if($gets == "\r\n"){
					$start = true;
				}
				if($start){
					$response .= $gets;
				} else{
					$headers .= $gets;
				}
			}
			fclose($fp);
		}
	}
}
?>
