<?
/**
 * @see http://dev.1c-bitrix.ru/community/webdev/user/17890/blog/8910/?commentId=49110#com49110
 *
 * в Битриксе есть метод CIBlockProperty::GetPropertyArray. Это служебный метод, который принимает в качестве параметров
 * идентификаторы информационного блока и свойства и возвращает информацию об указанном свойстве из таблицы b_iblock_property.
 *
 * Характерная особенность этого метода заключается в том, что он вызывается очень часто. Практически любое действие,
 * которое хоть как-то затрагивает свойства инфоблока приводит к вызову этого метода и запросу к базе данных.
 *
 * Для большинства проектов это не является проблемой: у таблицы b_iblock_property есть все необходимые индексы
 * и запросы получаются очень быстрыми. Однако, когда на проекте заведены сотни информационных блоков,
 * а посещаемость измеряется миллионами хитов в сутки, начинает сказываться количество этих запросов.
 * И не просто сказываться — база начинает задыхаться.
 *
 * При этом, в коде метода нетрудно заметить так называемый "виртуальный кеш": полученные данные сохраняются в
 * глобальном массиве $IBLOCK_CACHE_PROPERTY и при следующем вызове метода для того же
 * свойства, данные возвращаются уже не из базы, а из этого глобального массива.
 *
 * Проблема заключается в том, что время жизни "виртуального кеша" не превышает
 * времени жизни скрипта — долей секунды. При этом, обновление записей в
 * таблице b_iblock_property происходит очень-очень редко.
 *
 * Отсюда возникает очевидная задача: требуется продлить время жизни "виртуального кеша", не затрагивая при этом код ядра Битрикс.
 *
 * Решение следующее:
 *  * Разрабатывается класс, реализующий интерфейс ArrayAccess. В классе обеспечивается работа с подходящим
 *  in-memory key-value store (Memcached, Redis или любой другой). В случае, если сервис недоступен, объект ведет себя как обычный массив.
 *
 *  * В php_interface/init.php производится подключение модуля информационных блоков и создается объект CIBlockProperty.
 *  Создание объекта приводит к однократной инициализации глобального массива $IBLOCK_CACHE_PROPERTY. После этого созданный объект можно уничтожить, выполнив unset.
 *
 *  * В php_interface/init.php глобальный массив $IBLOCK_CACHE_PROPERTY переопределяется объектом класса,
 *  созданного на этапе 1. Переопределение производится ниже места, описанного на этапе 2.
 *
 *
 * Задача решена: теперь данные кешируются в быстром key-value store,
 * а при вызове метода GetPropertyArray обращения к базе данных больше не происходит.
 * На реализацию потрачен час времени, добавлено около 50 строк понятного кода, ядро Битрикс осталось нетронутым.
 *
 * К сожалению, я не имею права показывать графики. Могу лишь сказать, что производительность выросла до удивительных значений.
 *
 * Идея http://dev.1c-bitrix.ru/community/webdev/user/23242/
 *
 * @use init.php
 * require __DIR__ . '/include/ib_props_memcache.php';
 *
 * @version 1.0
 * @author wexpert, 2015
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (constant('BX_CACHE_TYPE') != 'memcache' || !class_exists('Memcache')) {
	return;
}



/**
 * A lightweight wrapper around the PHP Memcached extension with three goals:
 *
 *  - You can specify a prefix to prepend to all keys.
 *  - You can use it exactly like a regular Memcached object.
 *  - You can access the cache like an array.
 *
 * Example:
 *
 * $cache = new MemcachedWrapper('foo');
 * $cache['bar'] = 'x';        // sets 'foobar' to 'x'
 * isset($cache['bar']);       // returns true
 * unset($cache['bar']);       // deletes 'foobar'
 * $cache->set('bar', 'x')     // sets 'foobar' to 'x'
 */
class MemcachedWrapper implements ArrayAccess
{
	/**
	 * Memcached methods that take key(s) as arguments, and the argument
	 * position of those key(s).
	 */
	protected $keyArgMethods = array(
		'add'           => 0,
		'addByKey'      => 1,
		'append'        => 0,
		'appendByKey'   => 0,
		'cas'           => 1,
		'casByKey'      => 2,
		'decrement'     => 0,
		'delete'        => 0,
		'deleteByKey'   => 1,
		'get'           => 0,
		'getByKey'      => 1,
		'getDelayed'    => 0,
		'getDelayedByKey' => 1,
		'getMulti'      => 0,
		'getMultiByKey' => 1,
		'increment'     => 0,
		'prepend'       => 0,
		'prependByKey'  => 1,
		'replace'       => 0,
		'replaceByKey'  => 1,
		'set'           => 0,
		'setByKey'      => 1,
		'setMulti'      => 0,
		'setMultiByKey' => 1,
	);
	protected $prefix;

	/**
	 * The underlying Memcached object, which you can access in order to
	 * override the prefix prepending if you really want.
	 */
	public $mc;

	public function __construct($prefix = '')
	{
		$this->prefix = $prefix;
		$this->mc = new Memcache();

		$cacheConfig = \Bitrix\Main\Config\Configuration::getValue("cache");
		$v = (isset($cacheConfig["memcache"])) ? $cacheConfig["memcache"] : null;

		if ($v != null && isset($v["port"])) {
			$port = intval($v["port"]);
		} else {
			$port = 11211;
		}

		if (! $this->mc->pconnect($v["host"], $port)) {
			throw new MemcachedWrapperError("Cant connect to memmcached: " . $v["host"]);
		}
	}

	public function __call($name, $args)
	{
		if (!is_callable(array($this->mc, $name))) {
			throw new MemcachedWrapperError("Unknown method: $name");
		}

		// find the position of the argument with key(s), if any
		if (isset($this->keyArgMethods[$name])) {
			$pos = $this->keyArgMethods[$name];
			// prepend prefix to key(s)
			if (strpos($name, 'setMulti') !== false) {
				$new = array();
				foreach ($args[$pos] as $k => $v) {
					$new[$this->prefix . $k] = $v;
				}
				$args[$pos] = $new;
			} else if (strpos($name, 'Multi') !== false || strpos($name, 'Delayed') !== false) {
				$new = array();
				foreach ($args[$pos] as $k) {
					$new[] = $this->prefix . $k;
				}
				$args[$pos] = $new;
			} else {
				$args[$pos] = $this->prefix . $args[$pos];
			}
		}
		$result = call_user_func_array(array($this->mc, $name), $args);
		// process keys in return value if necessary
		$prefixLen = strlen($this->prefix);
		$process = function ($r) use ($prefixLen) {
			$r['key'] = substr($r['key'], $prefixLen);
			return $r;
		};
		if ($name == 'fetch' && is_array($result)) {
			return $process($result);
		} else if ($name == 'fetchAll' && is_array($result)) {
			return array_map($process, $result);
		} else if (strpos($name, 'getMulti') === 0 && is_array($result)) {
			$new = array();
			foreach ($result as $k => $v) {
				$new[substr($k, strlen($this->prefix))] = $v;
			}
			return $new;
		} else {
			return $result;
		}

	}

	public function offsetExists($offset)
	{
		if ($this->mc->get($this->prefix . $offset)) {
			return true;
		} else if ($this->mc->getResultCode() != Memcached::RES_NOTFOUND) {
			return true;
		} else {
			return false;
		}
	}

	public function offsetGet($offset)
	{
		return $this->mc->get($this->prefix . $offset);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset === null) {
			throw new MemcachedWrapperError("Tried to set null offset");
		}
		return $this->mc->set($this->prefix . $offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->mc->delete($this->prefix . $offset);
	}
}
class MemcachedWrapperError extends Exception {}



CModule::IncludeModule('iblock');

$pr = new CIBlockProperty();
unset($pr);

$GLOBALS['IBLOCK_CACHE_PROPERTY'] = new MemcachedWrapper('IBLOCK_CACHE_PROPERTY_');


