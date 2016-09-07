<?
/**
 * аякс пост формы, тут и выполняются действия по форме
 */

if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	exit;
}
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$escPOST = escapeArray($_POST);

if ($escPOST['__form__'] == 'subscribe') {
	
	$APPLICATION->IncludeComponent("wexpert:request", "subscribe_ajax", array(
		"_POST"			=> $escPOST,
		"POST_NAME"		=> "subscribe",
		"REQ_FIELDS"	=> array("name", "fio", "country", "city", "mail"),
		"EVENT_TYPE"	=> "SUBSCRIBE_REQUEST",
		"EVENT_ID"		=> 0,
		"ADD_ELEMENT"	=> array(),
		"NO_REDIRECT"	=> "Y"
	), false, array('HIDE_ICONS' => 'Y'));
	
} else if ($escPOST['__form__'] == 'callback') {
	
	$APPLICATION->IncludeComponent("wexpert:request", "callback_ajax", array(
		"_POST"			=> $escPOST,
		"POST_NAME"		=> "callback",
		"REQ_FIELDS"	=> array("name", "fio", "country", "city", "mail", "msg"),
		"EVENT_TYPE"	=> "CALLBACK_REQUEST",
		"EVENT_ID"		=> 0,
		"ADD_ELEMENT"	=> array(),
		"NO_REDIRECT"	=> "Y"
	), false, array('HIDE_ICONS' => 'Y'));
		
} else if ($escPOST['__form__'] == 'franchising') {
	
	$APPLICATION->IncludeComponent("wexpert:request", "franchising_ajax", array(
		"_POST"			=> $escPOST,
		"POST_NAME"		=> "franchising",
		"REQ_FIELDS"	=> array("name", "fio", "surname", "country", "city", "franch", "volume", "phone", "mail"),
		"EVENT_TYPE"	=> "FRANCHISING_REQUEST",
		"EVENT_ID"		=> 0,
		"ADD_ELEMENT"	=> array(),
		"NO_REDIRECT"	=> "Y"
	), false, array('HIDE_ICONS' => 'Y'));
		
} else {
	die("wtf?");
}

require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>