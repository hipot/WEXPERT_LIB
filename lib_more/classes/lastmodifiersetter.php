<?
/**
 * Класс для установки корректного заголовка Last-Modified для статики и динамики.
 * Также обработка заголовка HTTP_IF_MODIFIED_SINCE
 *
 * @version 1.0
 * @author weXpert, 2012
 */


/**
 * Установка заголовка Last-Modified для статики и динамики.
 * Также обработка заголовка HTTP_IF_MODIFIED_SINCE
 */
class LastModifierSetter
{
	/**
	 * запуск в файле init.php
	 * можно сказать, что это единая точка входа в установщик заголовка
	 */
	static function initRunner()
	{
		global $APPLICATION;

		$curPage	= $APPLICATION->GetCurPage(true);
		$file		= $_SERVER['DOCUMENT_ROOT'] . $curPage;

		// если это не статика, вешаем проверяльщик перед выводом страницы
		// (компонент может не установить заголовок, тогда нужно отостать текущий)
		if (! file_exists($file)) {
			self::setEndBufferPageChecker();
			return;
		} else {
			// если на странице вызван компонент, то тоже вешаем проверяльщик перед выводом страницы
			// (компонент может не установить, тогда нужно отостать текущий)
			$str = file_get_contents($file);
			$str = preg_replace('~[\n\t\r\s ]+~', '', $str);
			if (preg_match('~\$APPLICATION->IncludeComponent~i', $str)) {
				self::setEndBufferPageChecker();
				return;
			}
		}

		// работа со статикой
		$LastModified_unix = filemtime($file);
		self::sendHeader($LastModified_unix);
	}


	/**
	 * посылка заголовка Last-Modified
	 * @param int $timeStamp = time() дата изменения в юникс-формате
	 * @param bool $checkModifiedSince = true проверять ли запрос вида HTTP_IF_MODIFIED_SINCE
	 * 		в этом случае выдавать страницу не нужно, а нужно сообщить изменилась она или нет
	 */
	static function sendHeader($timeStamp = false, $checkModifiedSince = true)
	{
		if (intval($timeStamp) <= 0) {
			$timeStamp = time();
		}

		$IfModifiedSince = false;
		if (isset($_ENV['HTTP_IF_MODIFIED_SINCE'])) {
			$IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
		}
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
		}
		if ($IfModifiedSince && $IfModifiedSince >= $LastModified_unix && $checkModifiedSince) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
			exit;
		}

		header('Last-Modified: '. gmdate("D, d M Y H:i:s \G\M\T", $timeStamp));
	}


	/**
	 * Вешаем проверяльщик на событие модуля main - OnEndBufferContent.
	 */
	static function setEndBufferPageChecker()
	{
		AddEventHandler("main", "OnEndBufferContent", array("LastModirierSetter", "LastModirierSetterEndBufferContent"));
	}


	/**
	 * Обработчик события модуля main - OnEndBufferContent.
	 * Если заголовка Last-Modified в заголовках к отправке не найдено, то высылает текущий
	 * @param string $content - содержимое всей страницы
	 */
	static function LastModirierSetterEndBufferContent(&$content)
	{
		// если компонент устанавил дату, то
		if (intval(self::$LastModified_unix_Component) > 0) {
			self::sendHeader(self::$LastModified_unix_Component);
		} else {
			// иначе ищем, устанавливал ли кто-то такой заголовок
			$bFind = false;
			foreach (headers_list() as $header) {
				if (preg_match('#Last-Modified#is', $header)) {
					$bFind = true;
					break;
				}
			}
			if (! $bFind) {
				self::sendHeader();
			}
		}
	}

	/************************************************************************************/
	/********** установка в динамике для компонентов iblock.list и iblock.detail ********/
	/************************************************************************************/

	/**
	 * Время последнего изменения даты компонента (в component_epilog.php устанавливается)
	 * @var int
	 */
	static $LastModified_unix_Component = false;


	/**
	 * Для вызова в файле result_modifier.php компонентов iblock.list и iblock.detail
	 * Добавляет в кеш компонента дату LAST_MODIFIED.
	 * Обязательно у элементов отбирать TIMESTAMP_X!!!
	 * Фомат массива $arResult - это либо один элемент инфоблока, либо массив элементов $arResult['ITEMS']
	 *
	 * @param array $arResult массив $arResult компонента iblock.list и iblock.detail
	 * @param CBitrixComponentTemplate $cbt объект шаблона компонента, передавать просто $this
	 */
	static function componentResultModifierRunner(&$arResult, $cbt)
	{
		self::$LastModified_unix_Component = false;

		$arResultEx = $arResult;
		if (count($arResultEx['ITEMS']) == 0) {
			$arResultEx = array(
				'ITEMS'		=> array(
					$arResult
				)
			);
		}


		// находим из всех выбранных элементов инфоблока самое последнее время изменения
		$LastModified_unix = 0;

		foreach ($arResultEx['ITEMS'] as $arItem) {
			if (trim($arItem['TIMESTAMP_X']) == '') {
				continue;
			}

			$arTime = ParseDateTime($arItem['TIMESTAMP_X']);
			$timeTmp = mktime($arTime['HH'], $arTime['MI'], $arTime['SS'], $arTime['MM'], $arTime['DD'], $arTime['YYYY']);
			if ($timeTmp > $LastModified_unix) {
				$LastModified_unix = $timeTmp;
			}
		}

		// объект компонента
		$cp = $cbt->__component;
		if (is_object($cp) && intval($LastModified_unix) > 0) {
			$cp->arResult['LAST_MODIFIED'] = $LastModified_unix;
			$cp->SetResultCacheKeys(array('LAST_MODIFIED'));
			if (! isset($arResult['LAST_MODIFIED'])) {
				$arResult['LAST_MODIFIED'] = $cp->arResult['LAST_MODIFIED'];
			}
		}
	}

	/**
	 * Для вызова в файле шаблона component_epilog.php
	 * Шлет выбранный LAST_MODIFIED из $arResult (если он выбрался через
	 * LastModirierSetter::componentResultModifierRunner)
	 *
	 * @param array $arResult массив $arResult компонента
	 */
	static function componentEpilogRunner($arResult)
	{
		if ($arResult["LAST_MODIFIED"] > 0) {
			self::setLastModified($arResult["LAST_MODIFIED"]);
		}
	}

	/**
	 * Произвольная установка заголовка LAST_MODIFIED в любом месте
	 *
	 * @param int $unix_ts - время в формате unix
	 */
	static function setLastModified($unix_ts)
	{
		if ($unix_ts > 0) {
			self::$LastModified_unix_Component = $unix_ts;
		}
	}
}
?>