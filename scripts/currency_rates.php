<?
/**
 * этот скрипт нужно ставить на каждый день в 18 часов дня, он будет получать курсы на завтра
 */

set_time_limit(0);
ini_set('mbstring.func_overload', "2");
ini_set('mbstring.internal_encoding', "UTF-8");

// установить здесь значение после переноса!
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = 'E:/server/exller/www';
$_SERVER['SERVER_NAME'] = 'http://exller.develop.mnwb.ru';


define("LANG", "ru");

define("BX_UTF", true);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * Обновление курсов, агент
 */
function AgentGetCurrencyRate()
{
	global $DB;
	
	// подключаем модуль "валют"
	if (! CModule::IncludeModule('currency')) {
		return "AgentGetCurrencyRate();";
	}
	
	$arCurList = array('USD', 'EUR');
	$bWarning = False;
	// на завтра получаем курсы
	$rateDay = GetTime(time() + 86400, "SHORT", LANGUAGE_ID);
	$QUERY_STR = "date_req=".$DB->FormatDate($rateDay, CLang::GetDateFormat("SHORT", SITE_ID), "D.M.Y");
	$strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);
	
	// данная строка нужна только если у вас сайт в кодировке utf8
	$strQueryText = iconv('windows-1251', 'utf-8', $strQueryText);
	
	if (strlen($strQueryText) <= 0) {
		$bWarning = True;
	}
	
	if (! $bWarning)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
		$strQueryText = eregi_replace("<!DOCTYPE[^>]{1,}>", "", $strQueryText);
		$strQueryText = eregi_replace("<"."\?XML[^>]{1,}\?".">", "", $strQueryText);
		$objXML = new CDataXML();
		$objXML->LoadString($strQueryText);
		$arData = $objXML->GetArray();
		$arFields = array();
		$arCurRate["CURRENCY_CBRF"] = array();
	
		if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"])>0)
		{
			for ($j1 = 0; $j1<count($arData["ValCurs"]["#"]["Valute"]); $j1++)
			{
				if (in_array($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"], $arCurList))
				{
					$arRateAdd = array(
						'CURRENCY' => $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"],
						'DATE_RATE' => $rateDay,
						'RATE' => DoubleVal(str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"])),
						'RATE_CNT' => IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]),
					);
					
					CCurrencyRates::Add($arRateAdd);
				}
	
			}
		}
	}
	
	return "AgentGetCurrencyRate();";
}

AgentGetCurrencyRate();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>