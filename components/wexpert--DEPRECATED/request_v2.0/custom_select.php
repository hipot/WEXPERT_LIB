<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Файл дополнительных выборок, напр. если нужно выбрать справочник стран или проч для
 * формы  $arParams["POST_NAME"] == subscribe
 * мы делаем функцию
 *

if (! function_exists('CustomRequestSelects_subscribe')) {
	/**
	  * Дополнительные выборки для формы $arParams["POST_NAME"] == subscribe
	  * @return array массив, который будет доступен в $arResult['CUSTOM_SELECTS']
	  * /
	function CustomRequestSelects_subscribe()
	{
		static $_cache;
		if (isset($_cache)) {
			return $_cache;
		}
		
		// selects
		$array = array();

		$_cache = $array;
		return $_cache;
	}
}
*/

if (! function_exists('CustomRequestSelects_franchising')) {
	/**
	 * Дополнительные выборки для формы $arParams["POST_NAME"] == franchising
	 * @return array массив, который будет доступен в $arResult['CUSTOM_SELECTS']
	 */
	function CustomRequestSelects_franchising()
	{
		static $_cache;
		if (isset($_cache)) {
			return $_cache;
		}
		
		// selects
		$array = array();

		CModule::IncludeModule('iblock');
		$arF = array(
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => (LANGUAGE_ID == 'ru') ? 6 : 12
		);
		$rs = CIBlockElement::GetList(array('sort' => 'asc', 'name' => 'asc'), $arF, false, false, array('ID', 'NAME'));
		$i = 0;
		while ($ar = $rs->Fetch()) {
			$array['FRANCH'][] = $ar;
			$array['FRANCH_INDEX'][ $ar['ID'] ] = ($i++);
		}

		$_cache = $array;
		return $_cache;
	}
}
?>