<?
/**
 * Работа с отладкой переменных
 *
 * @author hipot
 * @version 1.0
 */
class WeDumper
{
	/**
	 * Кинуть дамп переменной в консоль в ff, chrome (firebug)
	 * работает с массивами, объектами и простыми типами.
	 * php-массивы дампятся как js-объекты.
	 *
	 * @param mixed $object переменная для дампа
	 * @param string $notes = '' Заметки к дампу (например, указываем файл и строчку, в котором
	 * 		был вызов дампа и имя переменной:  __FILE__ . ':' . __LINE__ . ' arItem')
	 * @param bool $only_admin = true выводить только для администратора (O)
	 * @uses CUtil::PhpToJSObject
	 * @example
	 * WeDumper::dumpToFirebugConsole(true);
	 * WeDumper::dumpToFirebugConsole(false);
	 * WeDumper::dumpToFirebugConsole(020);
	 * WeDumper::dumpToFirebugConsole(80);
	 * WeDumper::dumpToFirebugConsole('Огого " еще вот \'так\' ' . print_r($USER, true));
	 * WeDumper::dumpToFirebugConsole(array('test' => 123, 'test2' => 'текст'));
	 * WeDumper::dumpToFirebugConsole($USER, __FILE__ . ':' . __LINE__ . ' $USER');
	 * WeDumper::dumpToFirebugConsole(NULL);
	 * WeDumper::dumpToFirebugConsole(1.2);
	 */
	static public function dumpToFirebugConsole($object, $notes = '', $only_admin = true)
	{
		global $USER;
		if ($only_admin && !$USER->IsAdmin()) {
			return;
		}
		
		if (is_string($object) || is_bool($object) || $object === NULL) {
			$mod = '%s';
			if (is_bool($object)) {
				$object = $object ? 'true' : 'false';
			} else if ($object === NULL) {
				$object = 'NULL';
			}
			$object = preg_replace('#\s+#is', ' ', $object);
			$object = "'" . str_replace("'", "\'", $object) . "'";
		} else if (is_integer($object)) {
			$mod = '%i';
		} else if (is_double($object) || is_float($object)) {
			$mod = '%f';
		} else if (is_array($object) || is_object($object)) {
			$mod = '%o';
			if (is_object($object)) {
				$object = self::obj2arr($object);
			}
				
			$object = CUtil::PhpToJSObject($object, false);
		}
		?>
			<script type="text/javascript">
			try {
				console.info("dump <?=trim(addslashes($notes))?> >> <?=$mod?>", <?=$object;?>);
			} catch (ignore) {
			}
			</script>
		<?
	}
	
	/**
	 * Дампит переменную различными способами (print_r или var_dump)
	 *
	 * @param mixed $object переменная для дампа
	 * @param string $notes = '' Заметки к дампу (например, указываем файл и строчку, в котором
	 *		был вызов дампа и имя переменной:  __FILE__ . ':' . __LINE__ . ' arItem')
	 * @param bool $in_browser = false выводить ли результат в браузер через <pre> либо в html-комментариях (O)
	 * @param bool $only_admin = true выводить только для администратора (O)
	 * @param bool $var_dump = false использовать ли функцию var_dump() для дампа (O)
	 * @example WeDumper::dumpToBrowser($USER, __FILE__ . ':' . __LINE__ . ' $USER');
	 */
	static public function dumpToBrowser($object, $notes = '', $in_browser = false, $only_admin = true, $var_dump = false)
	{
		global $USER;
		if ($only_admin && !$USER->IsAdmin()) {
			return;
		}
		$notes = trim($notes);
		echo ($in_browser) ? "<pre> dump $notes >> " : "<!-- dump $notes >> ";
		if ($var_dump) {
			var_dump($object);
		} else {
			print_r($object);
		}
		echo ($in_browser) ? "</pre>" : "-->";
	}
	
	/**
	 * Рекурсивное преобразование объекта в массив
	 *
	 * @param object $obj объект для преобразования
	 * @return array
	 */
	static public function obj2arr($obj)
	{
		if (!is_object($obj) && !is_array($obj)) {
			return $obj;
		}
		if (is_object($obj)) {
			$obj = get_object_vars($obj);
		}
		if (is_array($obj)) {
			foreach ($obj as $key => $val) {
				$obj[$key] = self::obj2arr($val);
			}
		}
		return $obj;
	}
}
?>