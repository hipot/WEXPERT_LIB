<?
/**
 * Класс для работы с плоскими БД (на файлах)
 */

/**
 * Класс для работы с плоскими БД (на файлах)
 * @author hipot
 * @version 1.0 beta
 */
class DbaWorker
{
	/**
	 * Путь к файлу БД
	 * @var string
	 */
	var $databasePath;
	
	/**
	 * Режим работы БД
	 * @var string
	 * @see http://docs.php.net/manual/ru/dba.requirements.php 'List of DBA handlers'
	 */
	var $dbMode;
	
	/**
	 * Держит соединение
	 * @var resourse
	 */
	var $handler;
	
	function DbaWorker($databasePath, $dbMode = 'flatfile')
	{
		$this->databasePath = $databasePath;
		$this->dbMode = $this->checkMode($dbMode);
	}
	
	function __construct($databasePath, $dbMode = 'flatfile')
	{
		$this->DbaWorker($databasePath, $dbMode);
	}
	
	/**
	 * Проверяет работу режима хранения
	 * @param string $dbMode режим работы
	 * @return string
	 */
	function checkMode($dbMode)
	{
		$arHandlers = dba_handlers();
		$dbMode = strtolower($dbMode);
		foreach ($arHandlers as $k => $hanler) {
			$arHandlers[$k] = strtolower($hanler);
		}
		if (! in_array($dbMode, $arHandlers)) {
			$dbMode = $arHandlers[0];
		}
		return $dbMode;
	}
	
	/**
	 * Открыть файл для работы
	 */
	function open()
	{
		$hdl = dba_open($this->databasePath, 'c',  $this->dbMode);
		if ($hdl) {
			$this->handler = $hdl;
		} else {
			return false;
		}
	}
	
	/**
	 * Закрываем файл
	 */
	function close()
	{
		return dba_close($this->handler);
	}
	
	/**
	 * Получить значение по ключу
	 * @param string $id ключ на получение значение
	 * @return array|string
	 */
	function getByKey($id)
	{
		if ((($id = trim($id)) != '') && dba_exists($id, $this->handler)) {
			$val = dba_fetch($id, $this->handler);
		} else {
			$val = false;
		}
		$serTest = unserialize($val);
		if ($serTest !== false) {
			$val = $serTest;
		}
		return $val;
	}
	
	/**
	 * Удалить значение по ключу
	 * @param string $id Ключ для удаление
	 * @return bool
	 */
	function deleteByKey($key)
	{
		$ret = false;
		if ((($key = trim($key)) != '') && dba_exists($key, $this->handler)) {
			$ret = dba_delete($key, $this->handler);
		}
		return $ret;
	}
	
	/**
	 * Записать значения по ключу
	 * @param string $id ключ для записи
	 * @param array|string $val значение для записи
	 * @return bool
	 */
	function writeValByKey($key, $val)
	{
		if ((($key = trim($key)) == '') || !isset($val)) {
			return false;
		}
		
		if (!is_string($val) || !is_numeric($val)) {
			$val = serialize($val);
		}
		
		$ret = false;
		if (! dba_exists($key, $this->handler)) {
			$ret = dba_insert($key, $val, $this->handler);
		} else {
			$ret = dba_replace($key, $val, $this->handler);
		}
		return $ret;
	}
	
	/**
	 * Получить список всех записей
	 * @return array
	 */
	function getList()
	{
		$arResult = array();
		
		// Извлекаем ключ первой записи
		$key = dba_firstkey($this->handler);
		do {
			$val = $this->getByKey($key);
			if ($val !== false) {
				$arResult[$key] = $val;
			}
		} while ($key = dba_nextkey($this->handler));
		
		return $arResult;
	}
}
?>