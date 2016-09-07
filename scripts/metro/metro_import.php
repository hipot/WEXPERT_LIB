<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');
$M_ID=12;
$e = new CIBlockElement();
$s = new CIBlockSection();
foreach(require 'metro_array.php' as $vetka => $arM){
	$sid = $s->Add(array(
				 'IBLOCK_ID' => $M_ID,
				 'ACTIVE' => 'Y',
				 'CODE' => CUtil::translit($vetka,'en'),
				 'NAME' => $vetka,
				 'PICTURE' => CFile::MakeFileArray(SITE_TEMPLATE_PATH.$arM['LABEL']),
			));
			unset($arM['LABEL']);
	echo '<pre>		ветка	: '; print_r($vetka); echo '</pre>';
	foreach($arM as $m){
		$e->Add(array(
					 'IBLOCK_ID' => $M_ID,
					 'IBLOCK_SECTION_ID' => $sid,
					 'ACTIVE' => 'Y',
					 'CODE' => CUtil::translit($m[0],'en'),
					 'NAME' => $m[0],
					 'PROPERTY_VALUES' => array(
						 'opening_date' => $m[1],
						 'previous_names' => array('VALUE'=>array('TEXT'=>$m[2],'TYPE'=>'text')),
						 'altitude' => $m[3],
					 )
				));
		echo '<pre>'; print_r($m[0]); echo '</pre>';
	}
}

CIBlockSection::ReSort($M_ID);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>