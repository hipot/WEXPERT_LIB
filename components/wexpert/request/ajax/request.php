<?
/**
 * аякс пост формы, тут и выполняются действия по форме
 */

if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	exit;
}
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$escPOST = escapeArray($_POST);

if ($escPOST['__form__'] == 'recall') {

	$APPLICATION->IncludeComponent("wexpert:request", "recall_popup_ajax", array(
		"_POST"			=> $escPOST,
		"POST_NAME"		=> "recall",
		"REQ_FIELDS"	=> array("phone"),
		"EVENT_TYPE"	=> "RECALL_REQUEST",
		"EVENT_ID"		=> 0,
		"ADD_ELEMENT"	=> array(),
		"NO_REDIRECT"	=> "Y"
	), false, array('HIDE_ICONS' => 'Y'));

} else {
	die("wtf?");
}

require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>