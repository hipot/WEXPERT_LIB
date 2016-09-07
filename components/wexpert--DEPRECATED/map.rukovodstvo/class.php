<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

// подключили базовый класс
// CBitrixComponent::includeComponentClass("");

/**
 * Карта со списком городов, в каждом городе - список руководителей(baloon), и детальная руководителя(popup)
 *
 * @version 0.1
 * @author  matiaspub@gmail.com
 */

class ManagerMap extends CBitrixComponent
{
	/**
	 * Основная логика компонента, все равно что код component.php
	 * @return mixed
	 */
	public function executeComponent()
	{
		// $this->arParams - парметры компонента
		global $APPLICATION;
		$IBLOCK_ID = 52;
		$IBLOCK_CITYES = 8;


		if (!isset($this->arParams[ 'ID' ])) {
			$APPLICATION->AddHeadScript('http://api-maps.yandex.ru/2.0-stable/index.xml?load=package.standard,package.traffic&lang=ru-RU');
		}

		if ($this->StartResultCache(false)) {
			CModule::IncludeModule("iblock");

			if ($this->arParams[ 'ID' ] > 0) {

				$rsEl = CIBlockElement::GetByID($this->arParams[ 'ID' ]);
				if ($obEl = $rsEl->GetNextElement()) {
					$arEl = $obEl->fields;
					$arEl[ 'P' ] = $obEl->GetProperties();
					$this->arResult = $arEl;
				}

				$this->SetTemplateName('.default.ajax');

			} else {
				$rsEl = CIBlockElement::GetList(
					array("SORT" => "ASC"),
					array(
						"IBLOCK_ID" => $IBLOCK_ID,
					),
					false,false,
					array('ID','IBLOCK_ID','NAME','PREVIEW_PICTURE','PROPERTY_office','PROPERTY_office.NAME',
							'PROPERTY_office.ID','PROPERTY_office.PROPERTY_town','PROPERTY_office.PROPERTY_ya_point')
				);
				while ($arEl = $rsEl->GetNext()) {
					//массив связываюший ID города и ID офиса
					$arMap[ $arEl[ "PROPERTY_OFFICE_PROPERTY_TOWN_VALUE" ] ] = $arEl[ "PROPERTY_OFFICE_VALUE" ];

					$this->arResult[ 'PONT_LIST' ][ $arEl[ "PROPERTY_OFFICE_VALUE" ] ]['NAME'] = $arEl['PROPERTY_OFFICE_NAME'];
					$this->arResult[ 'PONT_LIST' ][ $arEl[ "PROPERTY_OFFICE_VALUE" ] ]['POINT'] = $arEl['PROPERTY_OFFICE_PROPERTY_YA_POINT_VALUE'];
					$this->arResult[ 'PONT_LIST' ][ $arEl[ "PROPERTY_OFFICE_VALUE" ] ][ "MANAGER_LIST" ][ ] = array(
						'ID'   => $arEl[ "ID" ],
						'NAME' => $arEl[ "NAME" ],
						'IMG'  => $arEl[ "PREVIEW_PICTURE" ]
					);
				}

				$rsCi = CIBlockSection::GetList(
					array("SORT" => "ASC"),
					array(
						"IBLOCK_ID" => $IBLOCK_CITYES,
					)
				);
				while($arCi = $rsCi->Fetch()){
					// по связанному массиву узнаем названия каких городов нам нужны
					if(isset($arMap[ $arCi['ID'] ])){
						$this->arResult[ 'PONT_LIST' ][ $arMap[ $arCi['ID'] ] ]['CITY'] = $arCi['NAME'];
					}
				}
			}


			if (count($this->arResult[ "ITEMS" ]) > 0) {
				$this->SetResultCacheKeys(array());
			} else {
				$this->AbortResultCache();
			}

			$this->IncludeComponentTemplate();
		}


		// возвращаем результат (если нужно)
		return $this->arResult;
	}
}

?>