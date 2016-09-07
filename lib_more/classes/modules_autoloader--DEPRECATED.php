<?
/**
 * Автозагрузчик модулей Битрикс, если они необходимы.
 *
 * Использование:
 * Файл подключается в dbconn.php
 * require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/lib/classes/modules_autoloader.php";
 *
 * 1. Позволяет отлавливать ошибку отсутствия класса таким образом:
 * $bIblockCustomModule = true;
 * try {
 * 		$el = new IblockElementCustom();
 * } catch (Exception $ignore) {
 * 		$bIblockCustomModule = false;
 * }
 *
 * 2. В случае стандартных классов битрикса - можно забыть о подключении в виде CModule::IncludeModule(),
 * теперь все будет делать этот класс.
 *
 *
 * Плюсы:
 * 1. Надоело после включения кеша натыкаться на ошибку, что не найден какой-то класс
 * (чаще всего это CIBlockElement).
 * 2. Не найденный класс не должен останавливать выполнение всей страницы, т.к. часто это
 * не критично, напр. забажившая лента слева новостей не должна убивать всю страницу.
 * Это позволит более гибко убирать куски кода, если совсем не найден какой-то класс.
 *
 * Минусы:
 * 1. Происходит подключение всех модулей, пока не найдем нужный класс, это плохо влияет на
 * производительность
 * 2. Будет способствовать говнокоду, т.к. разработчик совсем расслабит булки и не будет
 * думать о том, что и когда подключается. Без этого класса сейчас удобно сразу увидеть
 * запросы вне кеша, когда его включаешь. Сразу видно, где разработчик совсем не думал,
 * а просто написал запрос.
 *
 * НУЖНО ПОТЕСТИРОВАТЬ НА ПРОИЗВОДИТЕЛЬНОСТЬ. Обсудить минусы и плюсы
 *
 * @version 1.0 pre-alpha
 * @author weXpert, 2012
 * @see http://dev.1c-bitrix.ru/community/webdev/user/27606/blog/1121/
 * @see CModule::AddAutoloadClasses
 * @see http://ru.php.net/spl_autoload_register
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * autoload unknown classes, uses because spl_autoload_register won't work
 * @param string $sClassName
 */
function __autoload($sClassName)
{
	// битрикс использует этот метод для своих нужд
	CModule::RequireAutoloadClass($sClassName);
	
	if (! class_exists($sClassName)) {
		AutoloadClass::autoload($sClassName);
	}
}

// its bug with bitrix on php 5.3:
//spl_autoload_register(array('AutoloadClass', 'autoload'), true, false);

/////////////////////////////////////////////////////////////////////////////////////////

class AutoloadException extends Exception
{
}

class AutoloadClass
{
	/**
	 * Автоподгрузка классов
	 *
	 * @param string $sClassName имя неизвестного класса
	 * @return boolean
	 * @throws AutoloadException
	 */
	public static function autoload($sClassName)
	{
		global $DB, $MAIN_MODULE_INCLUDED, $MESS;
		
		// если класс не найден, то перебираем все модули битрикса
		//
		$rsInstalledModules = CModule::GetList();
		while ($ar = $rsInstalledModules->Fetch()) {
			CModule::IncludeModule($ar['ID']);
				
			// does the class requested actually exist now?
			if (class_exists($sClassName)) {
				// yes, we're done
				return true;
			}
		}

		// eval is evil, but fatal too. no class, create a new one and throw exception!
		eval("class $sClassName {
			function __construct() {
				throw new AutoloadException('Class $sClassName not found');
			}

			static function __callstatic(\$m, \$args) {
				throw new AutoloadException('Class $sClassName not found');
			}
		}");
		return false;
	}
}

?>