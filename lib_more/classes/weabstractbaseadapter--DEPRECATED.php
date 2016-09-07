<?
/**
 * Базовый адаптер для работы с любой таблицей.
 * Суть - мы создаем класс наследник данного адаптера - и имеем механизм работы с элементами таблицы.
 *
 * @version 2.XX
 * @author 2013, hipot at weXpert dot ru
 * @uses $GLOBALS['DB']
 *
 * @changelog
 * 2.0.4
 * - ошибка в проверке сортировки checkOrder
 * - Возможность сортировать по связанной таблице
 * 'join_table_field_relay.name' => 'asc' или 'join_table_field_relay__name' => 'asc'
 *
 * 2.0.2
 * - Экранирование полей в запросах `
 * - Оптимизация выборки связанных таблиц, "tableName.*" для базовой
 *
 * 2.0
 * - автоматическое получение списка полей таблицы, если они не переданы
 * - расклеены методы получения getList - вернет массив, а getList_CDBResult - дескриптор на результат
 * (объем памяти сервера!)
 * - возможность отбирать связанные данные в гет-лист (присоединять еще одну/две таблицы к выборке адаптера)
 * - возможность указывать произвольные поля, возвращаемые через гет-лист, метод setBaseTableSelectFields()
 *
 */
abstract class weAbstractBaseAdapter
{
	/**
	 * Просто для удержания коннекта к базе
	 * @var CDatabase
	 */
	public $DB;

	/**
	 * Имя таблицы для работы
	 * @var string
	 */
	protected $tableName;

	/**
	 * Массив полей в таблице, список по-умолчанию (всех, либо тех, с которыми работаем)
	 * @var array
	 */
	public $tableFields;

	/**
	 * Массив полей в таблице, которые выбираются в getList()
	 * По-умолчанию совпадает с $tableFields если не указано явно через setBaseTableSelectFields()
	 * @var array
	 */
	protected $tableFieldsSelect;

	/**
	 * Массив обязательных полей в таблице (для обработки ошибок на уровне вставки/обновления)
	 * @var array
	 */
	var $requireFields;

	/**
	 * Отладка SQL-выражений. Осторожно! Выводит в браузер!
	 * @var boolen
	 * @see self::showQuery();
	 */
	public $_debug;

	/**
	 * Конструктор, указываем к какой таблице мы строим адаптер
	 * У наследников обязательно указать данный конструктор
	 *
	 * @param string $tableName Имя таблицы для работы
	 * @param array $tableFields = array() Массив имен полей таблицы, если имя пустое то выбираем их из БД.
	 * 			Указать можно только часть полей, если все не нужны (для производительности)
	 * @param array $requireFields массив обязательных полей (для обработки ошибок на уровне вставки/обновления)
	 */
	function __construct($tableName, $tableFields = array(), $requireFields  = array())
	{
		$this->DB				= $GLOBALS['DB'];
		$this->tableName		= $tableName;

		if ($tableName == '') {
			throw new Exception('Имя таблицы указывать обязательно!');
		}

		if (count($tableFields) == 0) {
			$tableFields		= $this->getTableFieldsFromDB($this->tableName);
		}

		// поля в таблице для проверок и поля под выбор сразу совпадают
		$this->tableFields			=
		$this->tableFieldsSelect	= self::chopFieldsToUpper($tableFields);

		$this->requireFields	= self::chopFieldsToUpper($requireFields);
	}

	/**
	 * Добавляет запись в таблицу, возвращает ID вставленной записи или false
	 * @param array $arFileds массив полей со значениями
	 * @return false|integer
	 */
	public function add($arFields)
	{
		$this->checkInsertFields($arFields);

		if (count($arFields) <= 0) {
			return false;
		}

		$sql = 'INSERT INTO ' . $this->tableName . ' SET ';
		foreach ($arFields as $field => $value) {
			if (trim($value) == '') {
				continue;
			}
			$value = $this->DB->ForSql($value);
			$sql .= ' `' . $field . '` = \'' . $value . '\', ';
		}
		$sql = rtrim($sql, ', ');

		if ($this->_debug) {
			self::showQuery($sql);
		}

		$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
		$lastId = intval($this->DB->LastID());
		return $lastId;
	}

	/**
	 * Обновление элемента
	 * @param int $id ID-элемента
	 * @param array $arFileds поля для обновления
	 */
	public function update($id, $arFields)
	{
		$id = intval($id);
		if ($id <= 0) {
			return false;
		}
		$this->checkInsertFields($arFields);
		if (count($arFields) <= 0) {
			return false;
		}

		$sql = 'UPDATE ' . $this->tableName . ' SET ';
		foreach ($arFields as $field => $value) {
			/*if (trim($value) == '') {
				continue;
			}*/
			$value = $this->DB->ForSql($value);
			$sql .= ' `' . $field . '` = \'' . $value . '\', ';
		}
		$sql = rtrim($sql, ', ');

		$sql .= ' WHERE ID = ' . $id;

		if ($this->_debug) {
			self::showQuery($sql . ' --> ' . print_r($arFields, true));
		}

		$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
		return true;
	}

	/**
	 * Удаление записи
	 * @param int $id ID записи для удаления
	 */
	public function delete($id)
	{
		$id = intval($id);
		if ($id <= 0) {
			return false;
		}
		$sql = 'DELETE FROM ' . $this->tableName . ' WHERE ID = ' . $id;

		if ($this->_debug) {
			self::showQuery($sql);
		}
		$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
		$bRet = ($res->AffectedRowsCount() > 0);
		return $bRet;
	}

	/**
	 * Установить выбираемые поля из основной таблицы, напр. если нужно сократить список этих полей
	 * <b>Вызывать перед getList</b>
	 * не забываем, что указанные в конструкторе по-умолчанию не вернуться. Нужно удалять объект и создавать его заново.
	 *
	 * @param array $tableFields = array() Массив имен полей таблицы, если имя пустое то выбираем их из БД.
	 * 			Указать можно только часть полей, если все не нужны (для производительности)
	 */
	public function setBaseTableSelectFields($tableFields = array())
	{
		if (count($tableFields) == 0) {
			$tableFields		= $this->getTableFieldsFromDB($this->tableName);
		}

		$this->tableFieldsSelect = self::chopFieldsToUpper($tableFields);
	}

	/**
	 * Получить список из таблицы по фильтру (в виде массива)
	 * Опасно! Много строк в бд - может сожрать память
	 *
	 * @param array $arFilter фильтр для выборки (индексы по этим полям и их комбинациям есть?!)
	 * @param array $arOrder = array() фильтр для сортировки, можно сортировать по связанной таблице
	 * 		в виде 'join_table_field_relay.name' => 'asc' или 'join_table_field_relay__name' => 'asc'
	 * 		см. $arJoinTables - он при этом обязателен
	 * @param bool|array $arNavStartParams = false для постранички
	 * 			(ключи PAGE_SIZE - размер страницы, PAGE_N - номер страницы)
	 * @param int &$cntRows = NULL - общее кол-во строк (для передачи управления постраничкой)
	 * @param string $addWhereSql = '' - дополнительное SQL-выражение для WHERE (не безопасно!)
	 * @param string $arJoinTables = array() - объединить таблицы через LEFT JOIN (не безопасно!)
	 * 			это ассоциативный массив, где каждый элемент - это объединяемая таблица, а именно:<br />
	 * 			<pre>
	 * 				'join_table_name' => array(
	 * 					&nbsp;&nbsp;&nbsp;&nbsp; 'join_table_field_relay', // поле связи основной таблицы
	 * 					&nbsp;&nbsp;&nbsp;&nbsp; 'base_table_filed_relay', // поле связи связанной таблицы
	 * 					&nbsp;&nbsp;&nbsp;&nbsp; array('join_table_field1', ...) // не обязательно, по-умолчанию все поля
	 * 				)</pre><br />
	 *			В итоге в результат попадают еще результаты связанной таблицы в виде JOIN_TABLE_NAME__*,
	 *			где * - все поля связанной таблицы.
	 * @return array
	 * @example
	 * <pre>class OrderAdapter extends weAbstractBaseAdapter {
	 * 		&nbsp;&nbsp;	function __construct() {	parent::__construct('b_sale_order');	}
	 * }
	 *
	 * $oa = new OrderAdapter();
	 * $oa->_debug = true;
	 *
	 * $oa->setBaseTableSelectFields(array('ID', 'CANCELED', 'USER_ID'));
	 *
	 * $arOs = $oa->getList(
	 * 		&nbsp;&nbsp;	array('USER_ID' => 111111),
	 * 		&nbsp;&nbsp;	array('ID' => 'DESC'),
	 * 		&nbsp;&nbsp;	array('PAGE_SIZE' => 3), $cntRfix,
	 * 		&nbsp;&nbsp;	' AND b_sale_status_lang.DESCRIPTION <> "" ',
	 * 		&nbsp;&nbsp;	/* $arJoinTables = * / array(
	 * 		&nbsp;&nbsp;		'b_user'	=> array('ID', 'USER_ID', array('LOGIN', 'ID', 'NAME', 'LAST_NAME')),
	 * 		&nbsp;&nbsp;		'b_sale_status_lang'	=> array('STATUS_ID',	'STATUS_ID')
	 * 		&nbsp;&nbsp;	)
	 * );
	 * var_dump($arOs);
	 * var_dump($cntRfix);</pre>
	 */
	public function getList($arFilter, 					$arOrder = array(),
							$arNavStartParams = false, &$cntRows = NULL,
							$addWhereSql = '', 			$arJoinTables = array())
	{
		$arResult = array();
		$res = $this->getList_CDBResult(
				$arFilter,
				$arOrder,
				$arNavStartParams,
				$cntRows,
				$addWhereSql,
				$arJoinTables
		);
		while ($arTmp = $res->Fetch()) {
			$arResult[] = $arTmp;
		}
		return $arResult;
	}

	/**
	 * Получить список из таблицы по фильтру
	 *
	 * @see self::getList();
	 * @return CDBResult|boolean
	 */
	public function getList_CDBResult(	$arFilter, 					$arOrder = array(),
										$arNavStartParams = false, &$cntRows = NULL,
										$addWhereSql = '', 			$arJoinTables = array())
	{
		$this->checkFilter($arFilter);

		// base sql start
		$sql = 'SELECT #SELECT_WHAT# FROM ' . $this->tableName;

		// select fields what and join
		$joinSql		= '';

		if (count($this->tableFieldsSelect) == count($this->tableFields)) {
			$selectWhat		= $this->tableName . '.*, ';
		} else {
			foreach ($this->tableFieldsSelect as $field) {
				$field		 = $this->tableName . '.' . $field;
				$selectWhat .= $field . ', ';
			}
		}

		if (count($arJoinTables) > 0) {

			foreach ($arJoinTables as $joinTableName => $arRelays) {
				if (count($arRelays) < 2) {
					continue;
				}

				// имена связанных полей
				$arRelays2el	= array( trim($arRelays[0]), trim($arRelays[1]) );
				$arRelays2el	= self::chopFieldsToUpper($arRelays2el);
				if (trim($joinTableName) != '') {
					$joinSql 	.=	' LEFT JOIN ' . $joinTableName . ' ON ' . $joinTableName . '.' . $arRelays2el[0]
								. 	' = '. $this->tableName . '.' . $arRelays2el[1];
				}

				// не все поля связанной таблицы
				if (count($arRelays[2]) > 0) {
					$joinTableFields = self::chopFieldsToUpper($arRelays[2]);
				} else {
					$joinTableFields = $this->getTableFieldsFromDB($joinTableName);
				}
				foreach ($joinTableFields as $fld) {
					$selectName = $joinTableName . '.' . $fld;
					$selectWhat .= $selectName . ' AS ' . ToUpper(str_replace('.', '__', $selectName)) . ', ';
				}

			}

			$sql .= $joinSql;
		}

		$selectWhat = trim($selectWhat, ', ');
		// replace in sql down

		// where on base table only
		$whereSqlBody = '';

		$cnt = count($arFilter);
		$i = 0;
		foreach ($arFilter as $field => $value) {

			$field = $this->tableName . '.' . $field;

			if (is_array($value)) {
				$cntVal = count($value);
				$j = 0;
				$whereSqlBody .= ' ( ';
				foreach ($value as $pVal) {
					$pVal = $this->DB->ForSql($pVal);
					$whereSqlBody .= ' ' . $field . ' = \'' . $pVal . '\'';
					if ($j++ < $cntVal-1) {
						$whereSqlBody .= ' OR ';
					}
				}
				$whereSqlBody .= ' ) ';
			} else {
				$value = $this->DB->ForSql($value);
				$whereSqlBody .= ' ' . $field . ' = \'' . $value . '\'';
			}

			if ($i++ < $cnt-1) {
				$whereSqlBody .= ' AND ';
			}
		}

		if (trim($addWhereSql) != '') {
			// may cause problems
			$whereSqlBody .= ' ' . $addWhereSql . ' ';
		}
		if (trim($whereSqlBody) == '') {
			$whereSqlBody = ' 1 ';
		}
		$sql .= ' WHERE ' . $whereSqlBody;

		// ORDER BY on base table only
		$this->checkOrder($arOrder);
		if (count($arOrder) > 0) {
			$sql .= ' ORDER BY ';
			foreach ($arOrder as $field => $by) {
				// даем возможность сортировать не безопасно по связанной таблице
				if (strpos(ToUpper($field), '__') !== false
					|| strpos(ToUpper($field), '.') !== false
				) {
					$field = $field;
				} else {
					$field = $this->tableName . '.' . $field;
				}

				$sql .= "$field $by, ";
			}
			$sql = rtrim($sql, ', ');
		}

		$nPageSize	= intval($arNavStartParams["PAGE_SIZE"]);
		$nCurPage	= intval($arNavStartParams["PAGE_N"]) - 1;
		if ($nCurPage < 0) {
			$nCurPage = 0;
		}
		if ($arNavStartParams["PAGE_SIZE"] > 0) {

			$sqlCnt = str_replace('#SELECT_WHAT#', 'COUNT(*) AS CNT', $sql);

			if ($this->_debug) {
				self::showQuery($sqlCnt);
			}

			$res		= $this->DB->Query($sqlCnt, false, __FILE__ . __LINE__)->Fetch();
			$cntRows	= intval($res['CNT']);

			$NavFirstRecordShow = $nCurPage * $nPageSize;
			$NavAdditionalRecords = $nPageSize;
			$sql .= " LIMIT " . $NavFirstRecordShow . ", " . $NavAdditionalRecords;
		}

		$sql = str_replace('#SELECT_WHAT#', $selectWhat, $sql);

		if ($this->_debug) {
			self::showQuery($sql);
		}

		$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
		return $res;
	}

	/**
	 * Получение записи по ID
	 * @param $id ID-записи
	 */
	public function getById($id)
	{
		$id = intval($id);
		if ($id <= 0) {
			return false;
		}
		$arResult = $this->getList(array('ID' => $id));
		return $arResult[0];
	}

	/**
	 * Проверяет поля перед вставкой
	 * @param array &$arFileds поля для проверки
	 */
	function checkInsertFields(&$arFields)
	{
		foreach ($arFields as $field => $value) {
			if (!in_array(ToUpper($field), $this->tableFields)
				|| (in_array(ToUpper($field), $this->requireFields) && trim($value) == '')
			) {
				unset($arFields[$field]);
			}
		}
	}

	/**
	 * Проверить фильтр
	 * @param array &$arFilter фильтруемый массив
	 */
	function checkFilter(&$arFilter)
	{
		foreach ($arFilter as $field => $value) {
			if (! in_array(ToUpper($field), $this->tableFields)) {
				unset($arFilter[$field]);
			}
		}
	}

	/**
	 * Проверить фильтр
	 * @param array &$arOrder фильтруемый массив
	 */
	function checkOrder(&$arOrder)
	{
		if (! is_array($arOrder)) {
			$arOrder = array();
		}

		foreach ($arOrder as $field => $value) {
			$value = ToUpper($value);
			if (! in_array(ToUpper($field), $this->tableFields)
				// даем возможность сортировать не безопасно по полям связанной таблицы
				&& strpos(ToUpper($field), '__') === false
				&& strpos(ToUpper($field), '.') === false
			) {
				unset($arOrder[$field]);
				continue;
			} else if (! in_array($value, array('ASC', 'DESC'))) {
				$value = 'ASC';
			}
			$arOrder[$field] = $value;
		}
	}

	/**
	 * Отладка, отображение запроса
	 * @param string $q строка вывода
	 */
	static function showQuery($q)
	{
		echo '<span style="color:red;">' . wordwrap($q, 60) . '</span><br /><br />';
	}

	/**
	 * Получить список колонок SQL-запросом, либо если уже был получен, то просто вернуть
	 * @param string $tableName имя таблицы
	 * @return array
	 */
	function getTableFieldsFromDB($tableName)
	{
		$a = array();
		if (trim($tableName) != '') {
			$query	= "SHOW COLUMNS FROM " . $tableName;
			$res	= $this->DB->Query($query);

			while ($row = $res->Fetch()) {
				$a[] = $row['Field'];
			}
		}
		return $a;
	}

	/**
	 * Работает с именами колонок. Приводим все колонки к одному знаменателю - верхний регистр
	 * @param array $tableFieldsEx - массив имен колонок таблицы
	 */
	static function chopFieldsToUpper($arFields)
	{
		if (! is_array($arFields)) {
			$arFields = array();
		}
		$FieldsEx = array();
		foreach ($arFields as $k => $v) {
			if (trim($v) == '') {
				continue;
			}
			$FieldsEx[] = ToUpper($v);
		}
		return $FieldsEx;
	}
}


?>