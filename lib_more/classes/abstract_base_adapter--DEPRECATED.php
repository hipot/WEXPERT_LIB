<?
/**
 *
 * Базовый адаптер для работы с любой таблицей
 * @version 2.0 alpha
 * @author hipot AT wexpert DOT ru, 2010
 *
 */
class AbstractBaseAdapter
{
	/**
	 * Просто для удержания коннекта к базе
	 * @var CDatabase
	 */
	var $DB;

	/**
	 * Имя таблицы для работы
	 * @var string
	 */
	var $tableName;

	/**
	 * Массив полей в таблице
	 * @var array
	 */
	var $tableFields;

	/**
	 * Массив обязательных полей в таблице (на будущее)
	 * @var unknown_type
	 */
	var $requireFields;

	/**
	 * асооциативный массив соединяемых таблиц (на будущее)
	 * @var array
	 */
	var $arJoinTables;

	/**
	 * Отладка
	 * @var boolen
	 */
	var $_debug;

	/**
	 * Конструктор
	 * @param string $tableName Имя таблицы для работы
	 * @param array $tableFields Масив имен полей таблицы
	 * @param array $arJoinTables асооциативный массив соединяемых таблиц (future)
	 */
	function __construct($tableName, $tableFields, $arJoinTables, $requireFields)
	{
		global $DB;

		$this->DB = $GLOBALS['DB'];
		$this->tableName = $tableName;

		$tableFieldsEx = array();
		foreach ($tableFields as $k => $v) {
			if (trim($v) == '') {
				continue;
			}
			$tableFieldsEx[] = ToUpper($v);
		}
		$this->tableFields = $tableFieldsEx;

		if (! is_array($arJoinTables)) {
			$arJoinTables = array();
		}
		$this->arJoinTables = $arJoinTables;

		$tableReqFieldsEx = array();
		foreach ($requireFields as $k => $v) {
			if (trim($v) == '') {
				continue;
			}
			$tableReqFieldsEx[] = ToUpper($v);
		}
		$this->requireFields = $tableReqFieldsEx;
	}
	//
	// FIXME исторический хвост, убрать
	//
	function __constructor($tableName, $tableFields, $arJoinTables, $requireFields)
	{
		$this->__construct($tableName, $tableFields, $arJoinTables, $requireFields);
	}


	/**
	 * Добавляет запись в таблицу, возвращает ID вставленной записи или false
	 * @param array $arFileds массив полей со значениями
	 * @return false|integer
	 */
	function add($arFields)
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
			$value = addslashes($value);
			$sql .= ' ' . $field . ' = \'' . $value . '\', ';
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
	function update($id, $arFields)
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
			$value = addslashes($value);
			$sql .= ' ' . $field . ' = \'' . $value . '\', ';
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
	 * Проверяет поля перед вставкой
	 * @param array &$arFileds поля для проверки
	 */
	function checkInsertFields(&$arFields)
	{
		foreach ($arFields as $field => $value) {
			if (! in_array(ToUpper($field), $this->tableFields) || (in_array(ToUpper($field), $this->requireFields) && trim($value) == '')) {
				unset($arFields[$field]);
			}
		}
	}

	/**
	 * Удаление записи
	 * @param int $id ID записи для удаления
	 */
	function delete($id)
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
	 * Получить список из таблицы по фильтру
	 * @param array $arFilter фильтр для выборки
	 * @param array $arOrder = array() фильтр для сортировки
	 * @param bool|array $arNavStartParams = false для постранички
	 * (ключи PAGE_SIZE - размер страницы, PAGE_N - номер страницы)
	 * @param int &$cntRows = NULL - общее кол-во строк (для передачи управления постраничкой)
	 * @param string $addWhereSql = '' - дополнительное SQL-выражение для WHERE (в крайних случаях)
	 * @return array
	 */
	function getList($arFilter, $arOrder = array(), $arNavStartParams = false, &$cntRows = NULL, $addWhereSql = '')
	{
		$this->checkFilter($arFilter);

		$sql = 'SELECT * FROM ' . $this->tableName . ' WHERE ';

		$whereSqlBody = '';

		$cnt = count($arFilter);
		$i = 0;
		foreach ($arFilter as $field => $value) {
			if (is_array($value)) {
				$cntVal = count($value);
				$j = 0;
				$whereSqlBody .= ' ( ';
				foreach ($value as $pVal) {
					$pVal = addslashes($pVal);
					$whereSqlBody .= ' ' . $field . ' = \'' . $pVal . '\'';
					if ($j++ < $cntVal-1) {
						$whereSqlBody .= ' OR ';
					}
				}
				$whereSqlBody .= ' ) ';
			} else {
				$value = addslashes($value);
				$whereSqlBody .= ' ' . $field . ' = \'' . $value . '\'';
			}

			if ($i++ < $cnt-1) {
				$whereSqlBody .= ' AND ';
			}
		}

		//$whereSqlBody = (strlen($whereSqlBody) > 0) ? $whereSqlBody : '';
		// may cause problems
		if (trim($addWhereSql) != '') {
			$whereSqlBody .= $addWhereSql;
		} else if (trim($whereSqlBody) == '') {
			$whereSqlBody = ' 1 ';
		}
		$sql .= $whereSqlBody;

		$this->checkOrder($arOrder);
		if (count($arOrder) > 0) {
			$sql .= ' ORDER BY ';
			foreach ($arOrder as $field => $by) {
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

			$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
			$cntRows = $res->SelectedRowsCount();


			$NavFirstRecordShow = $nCurPage * $nPageSize;
			$NavAdditionalRecords = $nPageSize;
			$sql .= " LIMIT " . $NavFirstRecordShow . ", " . $NavAdditionalRecords;
		}

		if ($this->_debug) {
			self::showQuery($sql);
		}

		/*echo $sql;
		exit;*/

		$arResult = array();
		$res = $this->DB->Query($sql, false, __FILE__ . __LINE__);
		while ($arTmp = $res->Fetch()) {
			$arResult[] = $arTmp;
		}
		return $arResult;
	}

	/**
	 * Получение записи по ID
	 * @param $id ID-записи
	 */
	function getById($id)
	{
		$id = intval($id);
		if ($id <= 0) {
			return false;
		}
		$arResult = $this->getList(array('ID' => $id));
		return $arResult[0];
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
		foreach ($arOrder as $field => $value) {
			$value = ToUpper($value);
			if (! in_array(ToUpper($field), $this->tableFields)) {
				unset($arFilter[$field]);
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
	function showQuery($q)
	{
		echo $q . '<br />';
	}
}
?>