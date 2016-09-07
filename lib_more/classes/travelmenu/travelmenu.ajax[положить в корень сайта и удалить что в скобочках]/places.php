<?
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	$places=array();
	if($_REQUEST['term']){
		$rs = TravelmenuAPI::getPlacesByTerm($_REQUEST['term']);
		while($ar = $rs->Fetch()){
			if(!$GLOBALS['SHOW_COUNTRY'][ $ar['coCODE'] ]) continue;
			$n = $ar['ciNAME'].', '.$ar['coNAME'];
			$places[$ar['coNAME'].$ar['ciNAME']] = array(
				'name' => $n,
				'value' => $n,
				'city' => $ar['ciCODE'],
				'country' => $ar['coCODE'],
			);
		}
		ksort($places);
		$places = array_values($places);
	}

	echo json_encode($places);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
endif;
?>
