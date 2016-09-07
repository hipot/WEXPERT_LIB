<?
/**
 * Файл для изменения значения свойств типа "Привязка к элементу" для нескольких элементов инфоблока.
 *
 * @version 2.2
 * @author WebExpert
 */

AddEventHandler("main", "OnAdminListDisplay", array("AdminGroupEdit", "OnAdminListDisplayHandler"));
AddEventHandler("main", "OnBeforeProlog", array("AdminGroupEdit", "OnBeforePrologHandler"));


class AdminGroupEdit
{

	public static function OnAdminListDisplayHandler(&$list)
	{

		if (ADMIN_SECTION !== true) {
			return;
		}

		/*
		 * Массив параметров события
		 * структура: ключ1 - итнфоблок
		 * массив значения ключа1 - список свойств для изменения
		 */
		$iblockId = $_REQUEST['IBLOCK_ID'];

		$arConfig = array(
			'19' => array('podborka', 'is_cus_icon')
		);

		if (! in_array($iblockId, array_keys($arConfig))) {
			return;
		}

		$fullHtml = '';

		foreach ($arConfig[ $iblockId ] as $propCode) {
			$rsProps = CIBlockProperty::GetList(array(), array('PROPERTY_TYPE' => "E", "IBLOCK_ID" => $iblockId, 'CODE' => $propCode ));

			while ($arProp = $rsProps->GetNext()) {

				if (! in_array($arProp['CODE'], $arConfig[ $iblockId ]) ) {
					continue;
				}

				$html = '<span style="display:none;" id="set_value_block_'.$arProp['CODE'].'">';
				$html .= '<span class="adm-select-wrap"><select class="adm-select" name="set_value_block['.$arProp['CODE'].']">';
				$html .= '<option value="0">(убрать значение)</option>';

				$rsElements = CIBlockElement::GetList(array(), array('IBLOCK_ID'=>$arProp["LINK_IBLOCK_ID"]));
				while ($arElement = $rsElements->GetNext()) {
					$html .= '<option value="'.$arElement['ID'].'">'.$arElement['NAME'].'</option>';
				}
				$html .= '</select></span></span>';

				$list->arActions['set_value_'.$arProp['CODE']] = "Изменить свойство \"" . $arProp['NAME'] . '"';
				$list->arActionsParams['select_onchange'] .= "BX('set_value_block_".$arProp['CODE']."').style.display = (this.value == 'set_value_".$arProp['CODE']."' ? 'block':'none');";

				$fullHtml .= $html;
			}

		}

		$list->arActions['set_value_block'] = array('type' => 'html', 'value' => $fullHtml);
	}

	public static function OnBeforePrologHandler()
	{
		global $USER;

		if (! preg_match('#^set_value_#', $_REQUEST['action'])
			|| !isset($_REQUEST['set_value_block']) || ADMIN_SECTION !== true
		) {
			return;
		}

		CModule::IncludeModule("iblock");

		foreach ($_REQUEST['set_value_block'] as $fName => $fValue) {

			// поверка на то, какое поле меняется
			if (ToLower($_REQUEST['action']) != ToLower('set_value_' . $fName)) {
				continue;
			}

			if ($fValue == 0) {
				$fValue = false;
			}

			$arProp = CIBlockProperty::GetList(array(), array(
				'IBLOCK_ID' 	=> intval($_REQUEST['IBLOCK_ID']),
				'CODE'			=> $fName
			))->GetNext();

			foreach ($_REQUEST['ID'] as $id) {

				if ($arProp['MULTIPLE'] == 'Y') {
					CIBlockElement::SetPropertyValuesEx($id, false, array($fName => array($fValue) ));
				} else {
					CIBlockElement::SetPropertyValuesEx($id, false, array($fName => $fValue));
				}

			}
		}
	}
}

?>