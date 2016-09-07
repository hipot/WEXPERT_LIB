<?
/**
 * Abstract Layer
 * Подсказки на выборки CIBlockElement::GetList
 *
 * @version 3.2 beta
 * @author hipot <hipot at wexpert dot ru>
 *
 */

/**
 * Оболочка над объектом информационного блока, возвращаемого цепочкой CIBlockElement::GetList()->GetNext()
 * Доступность полей определяется наличием переданного в конструктор массива $arItem
 *
 * @author hipot
 * @version 1.0
 *
 */
class WeIblockElementItem
{
	/**
	 * ID элемента
	 * @var int
	 */
	public $ID;
	
	/**
	 * Мнемонический идентификатор
	 * @var string
	 */
	public $CODE;
	
	/**
	 * EXTERNAL_ID или XML_ID Внешний идентификатор
	 * @var string
	 */
	public $EXTERNAL_ID;
	
	/**
	 * EXTERNAL_ID или XML_ID Внешний идентификатор
	 * @var string
	 */
	public $XML_ID;
	
	/**
	 * Название элемента
	 * @var string
	 */
	public $NAME;
	
	/**
	 * ID информационного блока.
	 * @var int;
	 */
	public $IBLOCK_ID;
	
	/**
	 * ID группы. Если не задан, то элемент не привязан к группе. Если элемент привязан
	 * к нескольким группам, то в этом поле ID одной из групп. По умолчанию содержит
	 * привязку к разделу с минимальным ID.
	 * @var int
	 */
	public $IBLOCK_SECTION_ID;
	
	/**
	 * Символический код инфоблока
	 * @var string
	 */
	public $IBLOCK_CODE;
	
	/**
	 * Флаг активности (Y|N)
	 * @var string
	 */
	public $ACTIVE;
	
	/**
	 * Дата начала действия элемента
	 * @var datetime
	 */
	public $DATE_ACTIVE_FROM;
	
	/**
	 * Дата окончания действия элемента
	 * @var datetime
	 */
	public $DATE_ACTIVE_TO;
	
	/**
	 * Порядок сортировки элементов между собой (в пределах одной группы-родителя)
	 * @var int
	 */
	public $SORT;
	
	/**
	 * Код картинки в таблице файлов для предварительного просмотра (анонса)
	 * @var int
	 */
	public $PREVIEW_PICTURE;
	
	/**
	 * Предварительное описание (анонс)
	 * @var string
	 */
	public $PREVIEW_TEXT;
	
	/**
	 * Тип предварительного описания (text/html)
	 * @var string
	 */
	public $PREVIEW_TEXT_TYPE;
	
	/**
	 * Код картинки в таблице файлов для детального просмотра
	 * @var int
	 */
	public $DETAIL_PICTURE;
	
	/**
	 * Детальное описание
	 * @var string
	 */
	public $DETAIL_TEXT;
	
	/**
	 * Тип детального описания (text/html)
	 * @var string
	 */
	public $DETAIL_TEXT_TYPE;
	
	/**
	 * Содержимое для поиска при фильтрации групп. Вычисляется автоматически.
	 * Складывается из полей NAME и DESCRIPTION (без html тэгов, если DESCRIPTION_TYPE
	 * установлен в html)
	 * @var string
	 */
	public $SEARCHABLE_CONTENT;
	
	/**
	 * Дата создания элемента
	 * @var Datetime
	 */
	public $DATE_CREATE;
	
	/**
	 * Код пользователя, создавшего элемент
	 * @var int
	 */
	public $CREATED_BY;
	
	/**
	 * Имя пользователя, создавшего элемент (доступен только для чтения)
	 * @var string
	 */
	public $CREATED_USER_NAME;
	
	/**
	 * Время последнего изменения полей элемента
	 * @var Datetime
	 */
	public $TIMESTAMP_X;
	
	/**
	 * Код пользователя, в последний раз изменившего элемент
	 * @var int
	 */
	public $MODIFIED_BY;
	
	/**
	 * Имя пользователя, в последний раз изменившего элемент. (доступен только для чтения)
	 * @var string
	 */
	public $USER_NAME;
	
	/**
	 * Путь к папке сайта. Определяется из параметров информационного блока.
	 * Изменяется автоматически. (доступен только для чтения)
	 * @var string
	 */
	public $LANG_DIR;
	
	/**
	 * Шаблон URL-а к странице для публичного просмотра списка элементов информационного
	 * блока. Определяется из параметров информационного блока. Изменяется
	 * автоматически. (доступен только для чтения)
	 * @var string
	 */
	public $LIST_PAGE_URL;
	
	/**
	 * Шаблон URL-а к странице для детального просмотра элемента. Определяется из
	 * параметров информационного блока. Изменяется автоматически. (доступен только
	 * для чтения)
	 * @var string
	 */
	public $DETAIL_PAGE_URL;
	
	/**
	 * Количество показов элемента (изменяется при вызове функции CIBlockElement::CounterInc).
	 * @var int
	 */
	public $SHOW_COUNTER;
	
	/**
	 * Дата первого показа элемента (изменяется при вызове функции CIBlockElement::CounterInc).
	 * @var Datetime
	 */
	public $SHOW_COUNTER_START;
	
	/**
	 * Комментарий администратора документооборота.
	 * @var string
	 */
	public $WF_COMMENTS;
	
	/**
	 * Код статуса элемента в документообороте
	 * @var int
	 */
	public $WF_STATUS_ID;
	
	/**
	 * Текущее состояние блокированности на редактирование элемента. Может принимать
	 * значения: red - заблокирован, green - доступен для редактирования, yellow -
	 * заблокирован текущим пользователем.
	 * @var string
	 */
	public $LOCK_STATUS;
	
	/**
	 * Теги элемента. Используются для построения облака тегов модулем Поиска
	 * @var string
	 */
	public $TAGS;
	/*
	
	/**
	 * Динамичное создание итема из массива
	 * @param array $arItem массив c полями элемента CIBlockElement::GetList()
	 */
	function __construct($arItem)
	{
		foreach ($arItem as $field => $value) {
			if (!isset($value) || $value === NULL) {
				continue;
			}
			if ($field == 'PROPERTIES') {
				// если множественное свойство, то это массив массивов
				foreach ($value as $propCode => $propArOrVal) {
					if (intval($propArOrVal['ID']) <= 0) {
						$this->{$field}->{$propCode} = $this->generatePropObj($propArOrVal);
					} else {
						$this->{$field}->{$propCode} = new WeIblockElementItemPropertyValue($propArOrVal);
					}
				}
				
			} else {
				$this->{$field} = $value;
			}
		}
	}
	
	public function generatePropObj($value)
	{
		foreach ($value as $k => $sv) {
			$value[$k] = new WeIblockElementItemPropertyValue($sv);
		}
		return $value;
	}
}

/**
 * Значение свойств инфоблока, возвращаемые CIBlockElement::GetProperty()
 * @author hipot
 * @version 1.0
 */
class WeIblockElementItemPropertyValue
{
	/**
	 * ID свойства
	 * @var int
	 */
	public $ID;
	
	/**
	 * Время последнего изменения свойства
	 * @var Datetime
	 */
	public $TIMESTAMP_X;
	
	/**
	 * Код информационного блока
	 * @var int
	 */
	public $IBLOCK_ID;
	
	/**
	 * Название свойства
	 * @var string
	 */
	public $NAME;
	
	/**
	 * Активность свойства (Y|N).
	 * @var string
	 */
	public $ACTIVE;
	
	/**
	 * Индекс сортировки
	 * @var int
	 */
	public $SORT;
	
	/**
	 * Мнемонический код свойства
	 * @var string
	 */
	public $CODE;
	
	/**
	 * Значение свойства по умолчанию (кроме свойства типа список L)
	 * @var string
	 */
	public $DEFAULT_VALUE;
	
	/**
	 * Тип свойства. Возможные значения: S - строка, N - число, F - файл, L - список,
	 * E - привязка к элементам, G - привязка к группам
	 * @var string
	 */
	public $PROPERTY_TYPE;
	
	/**
	 * Количество строк в ячейке ввода значения свойства
	 * @var int
	 */
	public $ROW_COUNT;
	
	/**
	 * Количество столбцов в ячейке ввода значения свойства
	 * @var int
	 */
	public $COL_COUNT;
	
	/**
	 * Тип для свойства список (L). Может быть "L" - выпадающий список или "C" - флажки
	 * @var string
	 */
	public $LIST_TYPE;
	
	/**
	 * Множественность (Y|N)
	 * @var string
	 */
	public $MULTIPLE;
	
	/**
	 * Внешний код свойства
	 * @var string
	 */
	public $XML_ID;
	
	/**
	 * Список допустимых расширений для свойств файл "F"(через запятую).
	 * @var string
	 */
	public $FILE_TYPE;
	
	/**
	 * Количество строк в выпадающем списке для свойств типа "список"
	 * @var int
	 */
	public $MULTIPLE_CNT;
	
	
	/**
	 * Для свойств типа привязки к элементам и группам задает код информационного блока
	 * с элементами/группами которого и будут связано значение.
	 * @var int
	 */
	public $LINK_IBLOCK_ID;
	
	/**
	 * Признак наличия у значения свойства дополнительного поля описания.
	 * Только для типов S - строка, N - число и F - файл (Y|N).
	 * @var string
	 */
	public $WITH_DESCRIPTION;
	
	/**
	 * Индексировать значения данного свойства (Y|N)
	 * @var string
	 */
	public $SEARCHABLE;
	
	/**
	 * Выводить поля для фильтрации по данному свойству на странице списка элементов
	 * в административном разделе
	 * @var string
	 */
	public $FILTRABLE;
	
	/**
	 * Обязательное (Y|N)
	 * @var string
	 */
	public $IS_REQUIRED;
	
	/**
	 * Флаг хранения значений свойств элементов инфоблока (1 - в общей таблице | 2 - в отдельной).
	 * (доступен только для чтения)
	 * @var int
	 */
	public $VERSION;
	
	/**
	 * Идентификатор пользовательского типа свойства
	 * @var string
	 */
	public $USER_TYPE;
	
	/**
	 * Свойства пользовательского типа
	 * @var array
	 */
	public $USER_TYPE_SETTINGS;
	
	/**
	 * ID значения свойства
	 * @var int
	 */
	public $PROPERTY_VALUE_ID;
	
	/**
	 * Значение свойства у элемента. Массив в случае свойств типа HTML/Text ([TEXT] => значение, [TYPE] => text|html)
	 * @var string|float|int|array
	 */
	public $VALUE;
	
	/**
	 * Дополнительное поле описания значения
	 * @var string
	 */
	public $DESCRIPTION;
	
	/**
	 * Значение варианта свойства
	 * @var string
	 */
	public $VALUE_ENUM;
	
	/**
	 * Внешний код варианта свойства
	 * @var string
	 */
	public $VALUE_XML_ID;
	
	public $TMP_ID;
	
	/**
	 * Создание объекта значения свойства
	 * @param array $arPropFlds результат схемы CIBlockElement::GetProperty()->GetNext()
	 */
	function __construct($arPropFlds)
	{
		foreach ($arPropFlds as $fld => $value) {
			if ($fld == 'CHAIN') {
				$value = WeIblockElemLinkedChains::chainArrayToChainObject($value);
			}
			if ($fld == 'FILE_PARAMS') {
				$value = new WeValueFile($value);
			}
			$this->{$fld} = $value;
		}
	}
}


/**
 * Значение свойств инфоблока, возвращаемые CIBlockElement::GetProperty()
 * @author hipot
 * @version 1.0
 */
class WeIblockElementItemPropertyValueLinkElem extends WeIblockElementItemPropertyValue
{
	/**
	 * Цепочка из связанных элементов
	 * @var WeIblockElementItem
	 */
	public $CHAIN;
}

/**
 * значение свойств типа файл
 * @author hipot
 * @version 1.0
 */
class WeIblockElementItemPropertyValueFile extends WeIblockElementItemPropertyValue
{
	/**
	 * Поля параметров файла, возвращаемые методом CFile::GetFileArray()
	 * @var WeValueFile
	 */
	public $FILE_PARAMS;
}

/**
 * Объект информации о файле, полученный через CFile::GetFileArray()
 * @author hipot
 * @version 1.0
 */
class WeValueFile
{
	/**
	 * ID файла
	 * @var int
	 */
	public $ID;
	
	/**
	 * Дата изменения записи
	 * @var timestamp
	 */
	public $TIMESTAMP_X;
	
	/**
	 * Идентификатор модуля которому принадлежит файл.
	 * @var string
	 */
	public $MODULE_ID;

	/**
	 * Высота изображения (если файл - графический).
	 * @var int
	 */
	public $HEIGHT;
	
	/**
	 * Ширина изображения (если файл - графический).
	 * @var int
	 */
	public $WIDTH;
	
	/**
	 * Размер файла (байт)
	 * @var int
	 */
	public $FILE_SIZE;
	
	/**
	 * MIME тип файла
	 * @var string
	 */
	public $CONTENT_TYPE;
	
	/**
	 * Подкаталог в котором находится файл на диске. Основной каталог для хранения файлов
	 * задается в параметре "Папка по умолчанию для загрузки файлов" в настройках главного
	 * модуля, значение данного параметра программно можно получить с помощью вызова
	 * функции: COption::GetOptionString("main", "upload_dir", "upload");
	 * @var string
	 */
	public $SUBDIR;
	
	/**
	 * Имя файла на диске сервера
	 * @var string
	 */
	public $FILE_NAME;
	
	/**
	 * Оригинальное имя файла в момент загрузки его на сервер
	 * @var string
	 */
	public $ORIGINAL_NAME;
	
	/**
	 * Описание файла
	 * @var string
	 */
	public $DESCRIPTION;

	/**
	 * Функция возвращает путь от корня к зарегистрированному файлу.
	 * Путь к файлу начинающийся от каталога указанного в параметре DocumentRoot в
	 * настройках веб-сервера, заданный по правилам формирования URL-адресов.
	 * Пример: /ru/about/index.php
	 * @var string
	 */
	public $SRC;
	
	/**
	 * Создание объекта информации о файле
	 * @param array $arPropFlds результат, полученный через CFile::GetFileArray()
	 */
	function __construct($arPropFlds)
	{
		foreach ($arPropFlds as $fld => $value) {
			$this->{$fld} = $value;
		}
	}
}
?>