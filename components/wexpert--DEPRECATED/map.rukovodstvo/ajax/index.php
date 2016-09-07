<?if(
	strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
	&& $_REQUEST['id']
){
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$APPLICATION->IncludeComponent('wexpert:map.rukovodstvo', '', array('ID'=>$_REQUEST['id']));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}?>