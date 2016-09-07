<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$HTML = '';
	if($_REQUEST['country']){
		$loc = TravelmenuAPI::getCity(array('COUNTRY_CODE'=>$_REQUEST['country']));
		while($ci = $loc->Fetch()){
			$HTML .= "<option value='{$ci["CODE"]}'>{$ci["NAME"]}</option>";
		}
	}
	if(strlen($HTML)>0){
		$HTML = '<option></option>'.$HTML;
	}
	echo $HTML;

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
endif;
?>
