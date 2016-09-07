<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Файл дополнительных обработчиков, напр. если нужны еще какие-либо действия
 * после получения результата. Допустим, у нас форма  $arParams["POST_NAME"] == subscribe
 * чтобы сделать еще каких-либо действий после успешной отправки формы, делаем:
 *

if (! function_exists('CustomRequestHandler_subscribe')) {
	/**
	  * Дополнительный обработчик для формы $arParams["POST_NAME"] == subscribe
	  * @param array $_post массив после успешного выполнения $arParams[_POST]
	  * @param array $mailVars массив $mailVars
	  * /
	function CustomRequestHandler_subscribe($_post, $mailVars)
	{
	}
}
*/

if (! function_exists('CustomRequestHandler_subscribe')) {
	/**
	 * Дополнительный обработчик для формы $arParams["POST_NAME"] == subscribe
	 * @param array $_post массив после успешного выполнения $arParams[_POST]
	 * @param array $mailVars массив $mailVars
	 */
	function CustomRequestHandler_subscribe($_post, $mailVars)
	{
		// дополнительно еще подписываем человека на рассылку анонимно
		if (! CModule::IncludeModule("subscribe")) {
			return false;
		}
		$subscr = new CSubscription;
		
		$bAddedUpdated = false;
		$bAdded = false;
		
		$EMAIL = $mailVars['mail'];
			
		$arFields = Array(
			"USER_ID" => false,
			"SEND_CONFIRM" => "N",
			"FORMAT" => "html",
			"EMAIL" => $EMAIL,
			"ACTIVE" => "Y",
			"RUB_ID" => (SITE_ID == 's1') ? array(1) : array(2),
		);
	
		$subscription = CSubscription::GetList(array(), array('EMAIL' => $EMAIL));
		if ($arTmp = $subscription->GetNext()) {
			$arRubrics = CSubscription::GetRubricArray($arTmp['ID']);
			$arRubrics = is_array($arRubrics) ? $arRubrics : array();
			$arFields['RUB_ID'] = array_unique(array_merge($arFields['RUB_ID'], $arRubrics));
			
			if (! $subscr->Update($arTmp['ID'], $arFields, SITE_ID)) {
				$arWarning[] = $subscr->LAST_ERROR;
			} else {
				$bAddedUpdated = true;
				$ID = $arTmp['ID'];
			}
				
		} else {
			$ID = $subscr->Add($arFields, SITE_ID);
			if (! $ID) {
				$arWarning[] = $subscr->LAST_ERROR;
			} else {
				$bAdded = true;
			}
		}
		if ($bAdded || $bAddedUpdated) {
			CSubscription::ConfirmEvent($ID);
		}
		return $arWarning;
	}
}
?>