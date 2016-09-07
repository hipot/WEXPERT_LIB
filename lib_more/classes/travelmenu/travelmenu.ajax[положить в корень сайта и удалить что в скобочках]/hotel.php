<?
//if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	$places=array();

	if($_REQUEST['term']){

		$CACHE_TIME = 3600*24;
		$CACHE_ID = 'GetHotels|-'.$_REQUEST['country'].'-'.$_REQUEST['city'];
		$CACHE_DIR = '/cache/php/TravelmenuAPI/';
		$obCache = new CPHPCache;
		if($obCache->StartDataCache($CACHE_TIME, $CACHE_ID, $CACHE_DIR)){
			$HOT = array();
			$rs = TravelmenuAPI::GetHotels(
				array(
				     'CountryCode' => $_REQUEST['country'],
				     'CityCode'    => $_REQUEST['city']
				)
			);
			foreach($rs['GetHotelsResponse']['Hotels']['Hotel'] as $h){
				$HOT[] = array(
					'Name' => $h['Name'],
					'Lower' => ToLower($h['Name']),
					'Code' => $h['Code'],
					'NameGrade' => $h['Name'].' '.$h['Grade'].'*'
				);
			}

			$obCache->EndDataCache(array("HOT" => $HOT)); // помним в кеш
		} else{
			$arVars = $obCache->GetVars(); // берем кеш
			$HOT = $arVars["HOT"]; // в удобную переменную, либо array_merge
		}

		$mterm = ToLower($_REQUEST['term']);
		foreach($HOT as $h){
			if(strpos($h['Lower'],$mterm)!==false){
				$hotels[] = array(
					'label' => $h['NameGrade'],
					'value' => $h['Name'],
					'code' => $h['Code'],
				);
			}
		}

	}

	echo json_encode($hotels);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
//endif;
?>
