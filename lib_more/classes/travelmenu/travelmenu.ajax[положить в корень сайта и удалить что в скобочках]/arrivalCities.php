<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$HTML = '';
	if($_REQUEST['country']){
		$loc = TravelmenuTourAPI::getLocations();
		foreach($loc['arrivalCountries'] as $co){
			if($co['countryCode'] == $_REQUEST['country']){
				foreach($co['cities'] as $ci){
					$HTML .= "<option value='{$ci[cityCode]}'>{$ci[cityName]}</option>";
				}
			}
		}
	}
	if(strlen($HTML)>0){
		$HTML = '<option></option>'.$HTML;
	}
	echo $HTML;

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
endif;
?>
