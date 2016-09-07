<?
exit;


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

set_time_limit(0);

/**
 * оптимизатор одиночных свойств инфоблока с товарами (19й инфоблок)
 * таблица свойств b_iblock_element_prop_s19
 *
 * ОЧЕНЬ ОСТОРОЖНО!
 *
 * @link http://alexvaleev.ru/mysql-row-size-too-large/
 */
class IblockSinglePropsDescriptionDbPacker
{
	static function packDescriptionCol($IBLOCK_ID)
	{
		// состав таблицы
		$arColsInTable = array();
		$arColsDescriptions = array();
		$strSql = 'DESCRIBE b_iblock_element_prop_s' . $IBLOCK_ID;
		$rs = $GLOBALS['DB']->Query($strSql);
		while ($ar = $rs->Fetch()) {
			if (strpos($ar['Field'], 'PROPERTY_') !== false) {
				$arColsInTable[ $ar['Field'] ] = $ar['Type'];
			} else if (strpos($ar['Field'], 'DESCRIPTION_') !== false) {
				$arColsDescriptions[ $ar['Field'] ] = $ar['Type'];
			}
		}

		// оптимизируем описания только у привязок к элементам, числа, списки
		$arBxOptimizeTypes = array('E', 'N', 'L');

		$cntQuery = 0;

		// выбираем свойства
		$properties = CIBlockProperty::GetList(array("sort" => "asc"), array("IBLOCK_ID" => $IBLOCK_ID));
		while ($prop_fields = $properties->Fetch()) {

			$filedDBType	= $arColsInTable[ 'PROPERTY_' . $prop_fields['ID'] ];
			$filedDescrType = $arColsDescriptions[ 'DESCRIPTION_' . $prop_fields['ID'] ];

			$prop_fields['filedDBType'] = ToUpper($filedDBType);
			$prop_fields['filedDescrType'] = ToUpper($filedDescrType);

			if ($prop_fields['MULTIPLE'] == 'N'
				&& $prop_fields['WITH_DESCRIPTION'] == 'N'
				&& isset($filedDBType)
				&& in_array($prop_fields['PROPERTY_TYPE'], $arBxOptimizeTypes)
			) {
				if ($prop_fields['filedDescrType'] != 'VARCHAR(1)') {
					$q = 'ALTER TABLE b_iblock_element_prop_s'.$IBLOCK_ID.' '
						.'CHANGE `'.'DESCRIPTION_' . $prop_fields['ID'].'` `'.'DESCRIPTION_' . $prop_fields['ID'].'` VARCHAR( 1 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';

					$GLOBALS['DB']->Query($q);

					echo $q . '<br />';
					$cntQuery++;
					if ($cntQuery > 3) {
						die($cntQuery . ' ago. Press F5...');
					}
				}
			}
		}

		echo 'done';
	}

	static function unpackDescriptionCol()
	{
	}
}


IblockSinglePropsDescriptionDbPacker::packDescriptionCol(19);



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>