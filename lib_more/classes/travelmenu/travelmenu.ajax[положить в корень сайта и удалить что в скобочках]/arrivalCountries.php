<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$HTML = '';
	if($_REQUEST['code']){
		$loc = TravelmenuTourAPI::getLocations();
		foreach($loc['departureCities'] as $ci){
			if($ci['cityCode'] == $_REQUEST['code']){
				$arrivalCountryCodes = $ci['arrivalCountryCodes'];
				break;
			}
		}

		if(!empty($arrivalCountryCodes)){
			foreach($loc['arrivalCountries'] as $co){
				if(in_array($co['countryCode'], $arrivalCountryCodes) && $GLOBALS['SHOW_COUNTRY'][ $co['countryCode'] ]){
					$arg[$co['countryCode']] = $co['countryName'];
				}
			}
		}
		natcasesort($arg);
		foreach($arg as $k=>$v){
			$HTML .= "<option value='$k'>$v</option>";
		}
	}

	if(strlen($HTML)>0){
		$HTML = "<option></option>".$HTML;
	}

	echo $HTML;

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
endif;
?>
