<?
/**
 * Abstract Iblock Elements Layer
 * Подсказки на выборки CIBlockElement::GetList() на базе компонента wexpert:iblock.list
 *
 * Сразу отвечу на вопрос ЗАЧЕМ ЭТО НУЖНО?
 * 1. Учень удобное использование классов в шаблонах компонент, все поля подсказываются
 * 2. Удобная выборка связанных полей связанных элементов в качестве неограниченной вложенности,
 * естественно, тоже с подсказками по связанным элементам
 *
 *
 * @version 3.2 beta
 * @author hipot <hipot at wexpert dot ru>
 *
 *
 *
 * @example
 * 1. Добавить подключение в init.php файла:
 *
 * define('ABSTRACT_LAYER_SAULT', 'MY_SITE_RU');
 * require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/abstract_iblock_elem_level/include.php'
 *
 * Для удобства использования в нескольких проектах следует указать соль, используемая в имени генерированных классов, напр. подключить так
 * // напр. для сайта www.mysite.ru следует указать (разрешены только символы по маске: [0-9a-zA-Z_])
 *
 * define('ABSTRACT_LAYER_SAULT', 'MY_SITE_RU');
 * require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/abstract_iblock_elem_level/include.php';
 *
 * Если не установлена, по-умолчанию константа ABSTRACT_LAYER_SAULT принимает значение трансформированного имени домена:
 * Напр. www.good-site.wexpert.ru --> GOOD_SITE_WEXPERT_RU
 *
 * 2. Открыть сайт, чтобы создался файл с предопределенными классами
 * require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/abstract_iblock_elem_level/cahce/generated.php'
 *
 * 3. Использование в eclipse:
 * Открыть файлы
 * $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/abstract_iblock_elem_level/basic.php'
 * $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/abstract_iblock_elem_level/cache/generated.php'
 * чтобы IDE проиндексировал файл с созданными классами
 *
 * 4. ИСПОЛЬЗОВАНИЕ:
 *
 * 4.1. (УСТАРЕВШИЙ, см. 4.1.1) в шаблоне компонента wexpert:iblock.list можем писать:
 * //template.php
 * foreach ($aResult['ITEMS'] as $arItem) {
 * 		// имя класса пишется следующим образом: __WeIblockElementItem#IBLOCK_ID#,
 * 		// мы ведь знаем какого инфоблока список выводится, не так ли?
 * 		// в примере заполняем абстрактный уровень 10го инфоблока
 *		$oItem = new __WeIblockElementItem10($arItem);
 *
 *		echo $oItem->NAME; // подсказывает все стандартные поля инфоблока
 *
 *		// предположим, что в 10м инфоблоке есть строковое свойство code1
 *		echo $oItem->PROPERTIES->code1->VALUE; // подсказывает все свойства инфоблока и их поля
 *
 *		// code2 у нас множественное, давайте выведем первое значение:
 *		$arMultiple = $oItem->PROPERTIES->code2;
 *		echo $arMultiple[0]->VALUE;
 *
 *		// предположим, что у нас есть свойство типа "HTML/Text" с кодом opis, выведем его значение
 *		echo $oItem->PROPERTIES->code2->VALUE['TEXT'];
 * }
 *
 * 4.1.1 (UPDATED) в шаблоне компонента wexpert:iblock.list можем писать:
 * //template.php
 * foreach ($aResult['ITEMS'] as $arItem) {
 * 		// имя класса пишется следующим образом: __WeIblockElementItem_#ABSTRACT_LAYER_SAULT#_#IBLOCK_ID#,
 * 		// мы ведь знаем какого инфоблока список выводится, не так ли?
 * 		// в примере заполняем абстрактный уровень 10го инфоблока
 * 		// #ABSTRACT_LAYER_SAULT# - указанная нами соль, либо сгеренированная автоматически по имени домена
 *
 *		// шаблон подсказки по типам можно смело закидывать в SNIPPETS (в шаблоны вашего IDE)
 *
 *		/* @var $oItem __WeIblockElementItem_MY_SITE_RU_10 * /
 *		/** @var __WeIblockElementItem_MY_SITE_RU_10 $oItem * /
 *
 *		$oItem = new WeIblockElementItem($arItem);
 *
 *		echo $oItem->NAME; // подсказывает все стандартные поля инфоблока
 *
 *		// предположим, что в 10м инфоблоке есть строковое свойство code1
 *		echo $oItem->PROPERTIES->code1->VALUE; // подсказывает все свойства инфоблока и их поля
 *
 *		// code2 у нас множественное, давайте выведем первое значение:
 *		$arMultiple = $oItem->PROPERTIES->code2;
 *		echo $arMultiple[0]->VALUE;
 *
 *		// предположим, что у нас есть свойство типа "HTML/Text" с кодом opis, выведем его значение
 *		echo $oItem->PROPERTIES->code2->VALUE['TEXT'];
 *
 *		// вывод пути к файлу в свойстве
 *		echo $oItem->PROPERTIES->file->FILE_PARAMS->SRC;
 * }
 *
 * 4.2. (УСТАРЕВШИЙ, см. 4.1.1) Хотя правильнее сразу в файле component.php собирать объекты:
 * // component.php
 * // QUERY 1 MAIN
 * $rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);
 * while ($arItem = $rsItems->GetNext()) {
 * 		// QUERY 2
 * 		$db_props = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arItem['ID'],
 * 											array("sort" => "asc"), array("EMPTY" => "N"));
 * 		while ($ar_props = $db_props->GetNext()) {
 * 			if ($ar_props['MULTIPLE'] == "Y") {
 * 				$arItem['PROPERTIES'][ $ar_props['CODE'] ][] = $ar_props;
 * 			} else {
 * 				$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_props;
 * 			}
 *		}
 *
 *		// вместо $arResult["ITEMS"][] = $arItem;
 * 		$aResult['ITEMS'][] = new WeIblockElementItem($arItem);
 * }
 *
 * // template.php
 * // сразу бежимся уже по объектам
 * foreach ($aResult['ITEMS'] as $oItem) {
 * 		// указываем eclips'у какого типа объекты у нас в массиве $aResult['ITEMS']
 * 		// пробел между "* /" удалить
 * 		/* @var $oItem __WeIblockElementItem10 * /
 *
 * 		// работают подсказки по всем полям инфоблока
 * 		echo $oItem->NAME;
 *
 * 		// работают подсказки по множественным полям, допустим у нас есть поле "code2"
 * 		$arMultiple = $oItem->PROPERTIES->code2;
 * 		echo $arMultiple[0]->VALUE;
 * }
 *
 * 4.3. (УСТАРЕВШИЙ, см. 4.3.1, см. 4.1.1) Реализованы цепочки связанности, правда их нужно довыбирать в компоненте (см. пример)
 * // component.php
 * // QUERY 1 MAIN
 * $rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);
 * while ($arItem = $rsItems->GetNext()) {
 * 		// QUERY 2
 * 		$db_props = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arItem['ID'],
 * 											array("sort" => "asc"), array("EMPTY" => "N"));
 * 		while ($ar_props = $db_props->GetNext()) {
 *
 * 			// довыборка цепочек глубиной 3
 * 			if ($ar_props['PROPERTY_TYPE'] == 'E') {
 * 				$obWeChainBuilder = new WeIblockElemLinkedChains();
 *				$obWeChainBuilder->init(3);
 *				$ar_props['CHAIN'] = $obWeChainBuilder->getChains_r($ar_props['VALUE']);
 * 			}
 *
 *  		if ($ar_props['MULTIPLE'] == "Y") {
 * 				$arItem['PROPERTIES'][ $ar_props['CODE'] ][] = $ar_props;
 *	 		} else {
 *	 			$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_props;
 *	 		}
 *		}
 *
 *		// вместо $arResult["ITEMS"][] = $arItem;
 * 		$aResult['ITEMS'][] = new WeIblockElementItem($arItem);
 * }
 *
 * // template.php
 * // сразу бежимся уже по объектам
 * foreach ($aResult['ITEMS'] as $oItem) {
 * 		// указываем eclips'у какого типа объекты у нас в массиве $aResult['ITEMS']
 * 		// пробел между "* /" удалить
 * 		/* @var $oItem __WeIblockElementItem10 * /
 *
 * 		// работают подсказки по всем полям инфоблока
 * 		echo $oItem->NAME;
 *
 * 		// работают подсказки по множественным полям, допустим у нас есть поле "code2"
 * 		$arMultiple = $oItem->PROPERTIES->code2;
 * 		echo $arMultiple[0]->VALUE;
 * }
 *
 * 4.3.1 (UPDATED) выборка цепочек с кешем
 *
 * // component.php
 * // QUERY 1 MAIN
 * $rsItems = CIBlockElement::GetList($arOrder, $arFilter, false, $arNavParams, $arSelect);
 *
 * // создаем объект, должен создаваться до цикла по элементам, т.к. в него складываются
 * // уже выбранные цепочки в качестве кеша
 * $obWeChainBuilder = new WeIblockElemLinkedChains();
 *
 * while ($arItem = $rsItems->GetNext()) {
 * 		// QUERY 2
 * 		$db_props = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arItem['ID'],
 * 											array("sort" => "asc"), array("EMPTY" => "N"));
 * 		while ($ar_props = $db_props->GetNext()) {
 *
 * 			// довыборка цепочек глубиной 3
 * 			if ($ar_props['PROPERTY_TYPE'] == 'E') {
 * 				// инициализация должна происходить перед каждым вызовом getChains_r
 * 				// с указанием выбираемой вложенности
 *				$obWeChainBuilder->init(3);
 *				$ar_props['CHAIN'] = $obWeChainBuilder->getChains_r($ar_props['VALUE']);
 * 			}
 * 			if ($ar_props['PROPERTY_TYPE'] == 'F') {
 * 				$ar_props['FILE_PARAMS'] = CFile::GetFileArray($ar_props['VALUE']);
			}
 *
 *  		if ($ar_props['MULTIPLE'] == "Y") {
 * 				$arItem['PROPERTIES'][ $ar_props['CODE'] ][] = $ar_props;
 *	 		} else {
 *	 			$arItem['PROPERTIES'][ $ar_props['CODE'] ] = $ar_props;
 *	 		}
 *		}
 *
 *		$arResult["ITEMS"][] = $arItem;
 * }
 *
 * // освобождаем память от цепочек
 * unset($obWeChainBuilder);
 *
 * 5. За заполненностью полей, естественно, должен следить сам разработчик, т.к. класс предоставляет
 * просто удобный способ написания кода с автокомплитом. Т.е. класс - это абстрактный уровень
 * для результатов работы компонента iblock.list
 *
 * 6. Для обновления файла с классами нужно просто удалить файл
 * $_SERVER['DOCUMENT_ROOT'] . '/php_interface/lib/abstract_iblock_elem_level/cache/generated.php'
 * открыть сайт, чтобы файл сгенерировался и открыть его в eclipse, чтобы проиндексировались классы
 * При изменении структуры инфоблоков схема также обновляется.
 *
 * 7. ВНИМАНИЕ!!! Естественно, чтобы вся эта система работала, у имен свойств нужно задавать корректные
 * символьные имена (шаблон [A-Za-z0-9_]+), т.к. символьные коды свойств превращаются в поля объектов
 * 7.1 В случае свойств привязок к инфоблокам, обязательно должно быть указано, к какому инфоблоку
 * привязано свойство элемента, иначе как построить схему привязок?
 *
 *
 *
 *
 * =======================
 * ===== CHANGELOG: ======
 * 3.2b
 * - добавлены подсказки по свойствам типа "файл", дополнительный ключ у значений свойств
 * "FILE_PARAMS" (см. пример 4.3.1). Данный ключ также добавлен и для свойств элементов,
 * выбранных по цепочкам
 *
 * 3.0b
 * - кеширование цепочек при выборе, т.к. если связанный элемент уже выбран, то не стоит его выбирать вновь
 * из базы (см. пример
 *
 * 2.8b добавлено
 * - соль к именам генерируемых классов ABSTRACT_LAYER_SAULT, чтобы использовать
 * сгенерированные кеши мультипроектно (на разных проектах ID инфоблоков могут совпадать)
 * соответсвенно, новый тип комментариев:
 * eclipse: 	/* @var $oItem __WeIblockElementItem_#ABSTRACT_LAYER_SAULT#_#IBLOCK_ID# * /
 * PHPStorm:	/** @var __WeIblockElementItem_#ABSTRACT_LAYER_SAULT#_#IBLOCK_ID# $oItem * /
 *
 * 2.5b добавлено
 * - подсказки при выборке свойств прямо из CIBlockElement::GetList, причем подсказки
 * работают и вида PROPERTY_CODE_PROPERTY_CODE2_VALUE, т.е. так, как это позволяет гет лист
 * - подключение файла схемы не требуется для подсказок (см. обновленные примеры в справке)
 * достаточно делать комментарий указания типа в IDE:
 * eclipse: 	/* @var $oItem __WeIblockElementItem12 * /
 * PHPStorm:	/** @var __WeIblockElementItem12 $oItem * /
 * Т.е. теперь в шаблоне всегда создаем объект WeIblockElementItem(), а не унаследованный
 * __WeIblockElementItem#IBLOCK_ID#, а чтобы работали подсказки делаем комментарий для IDE,
 * как это показано выше
 *
 * 2.0b добавлены цепочки связанных элементов
 * - добавлены механизмы выбора связанных элементов
 * - добавлены подсказки по всей схеме инфоблоков связанных элементов (поля связанных элементов,
 * их свойства)
 * - у инфоблоков без свойств нет свойства объекта "PROPERTIES"
 *
 * 1.0b придуманы механизмы обновления схемы,
 * - схема обновляется при изменении структуры инфоблоков
 *
 * 0.4a начальная версия,
 * - возможность смотреть подсказки по полям инфоблоков без описания полей
 * - возможность смотреть свойства инфоблока
 * ========================
 */


if (! defined('ABSTRACT_LAYER_SAULT')) {
	/**
	 * Соль в именах генерируемых классов, разрешены символы [0-9a-zA-Z_]
	 * по-умолчанию, устанавливается в трансформированное имя домена, напр.
	 * www.good-site.wexpert.ru --> GOOD_SITE_WEXPERT_RU
	 * @var string
	 */
	define('ABSTRACT_LAYER_SAULT', ToUpper(str_replace(array('www.', '.', '-'), array('_', '_', '_'), $_SERVER['HTTP_HOST'])));
}

require_once dirname(__FILE__) . '/base.php';
require_once dirname(__FILE__) . '/chains.php';
require_once dirname(__FILE__) . '/installer.php';

/**
 * Файл с сгенерированной схемой элементов инфоблоков
 * @var string
 * @global
 */
$fileToGenerateSxema = $GLOBAL['fileToGenerateSxema'] = dirname(__FILE__) . '/cache/generated.php';

if (! file_exists($fileToGenerateSxema)) {
	WeIblockGenerateSxemManager::updateSxem();
}

if (file_exists($fileToGenerateSxema)) {
	// версия 2.5b, подключение схемы не требуется
	//require_once $fileToGenerateSxema;
}

/**
 * устанавливаем обработку событий
 */
WeIblockGenerateSxemManager::setUpdateHandlers();
?>