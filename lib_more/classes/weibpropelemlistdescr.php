<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Класс weIBPropElemListDescr добавляет новое свойство инфоблоку: Привязка к элементам с описанием (we)
 * При помощи свойства можно привязывать компонент к элементу и добавлять описание к привязке.
 *
 * Использование:
 * Добавить обработчик AddEventHandler('iblock', 'OnIBlockPropertyBuildList', array('weIBPropElemListDescr', 'GetUserTypeDescription'));
 *
 * Создатель:   petr_we
 * Дата:        30.10.14
 *
 * @author WebExpert, 2014, study.wexpert.ru
 */

class weIBPropElemListDescr
{
	public static function GetUserTypeDescription()
	{
		return array(
			"PROPERTY_TYPE"        => "E",
			"USER_TYPE"            => "BindToTheDescription",
			"DESCRIPTION"          => 'Привязка к элементам с описанием (we)',
			"GetPropertyFieldHtml" => array("weIBPropElemListDescr", "GetPropertyFieldHtml"),
		);
	}

	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$arItem = Array(
			"ID"        => 0,
			"IBLOCK_ID" => 0,
			"NAME"      => ""
		);
		if (intval($value["VALUE"]) > 0) {
			$arFilter = Array(
				"ID"        => intval($value["VALUE"]),
				"IBLOCK_ID" => $arProperty["LINK_IBLOCK_ID"],
			);
			$rsItem   = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID", "IBLOCK_ID", "NAME"));
			$arItem   = $rsItem->GetNext();
		}

		$html .=
			'<input name="' . $strHTMLControlName["VALUE"] . '" id="' . $strHTMLControlName["VALUE"] . '" value="' . htmlspecialcharsex($value["VALUE"]) . '" size="5" type="text">' .
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=' . LANG . '&amp;IBLOCK_ID=' . $arProperty["LINK_IBLOCK_ID"] . '&amp;n=' . $strHTMLControlName["VALUE"] . '\', 600, 500);">' .
			'&nbsp;<input type="text" name="' . $strHTMLControlName["DESCRIPTION"] . '" value="' . htmlspecialcharsex($value["DESCRIPTION"]) . '" />' .
			'&nbsp;<span id="sp_' . md5($strHTMLControlName["VALUE"]) . '_' . $key . '" >' . $arItem["NAME"] . '</span>';

		return $html;
	}
}