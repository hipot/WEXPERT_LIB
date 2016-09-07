<?
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


// require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");

CModule::IncludeModule("iblock");

$res = CIBlockElement::GetList(Array(), array(
	"IBLOCK_ID" => 26,
	"ID" => intval($_GET["ID"])
), false, false, array(
	"ID",
	"NAME",
	"DETAIL_PAGE_URL",
	"DETAIL_TEXT",
	"DETAIL_TEXT_TYPE",
	"PROPERTY_DATE_START",
	"PROPERTY_DATE_END",
	"PROPERTY_PLACE_EVENTS"
));
if ($arRes = $res->GetNext()) {
	if (trim($arRes["PROPERTY_DATE_END_VALUE"]) == '' || trim($arRes["PROPERTY_DATE_START_VALUE"]) == '') {
		require_once $_SERVER["DOCUMENT_ROOT"] . '/404_inc.php';
	} else {

		$desc = HTMLtoTXT($arRes["~DETAIL_TEXT"], false);
		$desc = preg_replace("#[\n\r]+#", '\n', trim($desc));
		$desc = html_entity_decode($desc);
		$desc = str_replace('&ndash;', '-', $desc);
		// $desc = mb_convert_encoding($desc, "UTF-8","windows-1251");
		// $arRes["PROPERTY_PLACE_EVENTS_VALUE"] = mb_convert_encoding($arRes["PROPERTY_PLACE_EVENTS_VALUE"], "UTF-8", "windows-1251");
		// $arRes["NAME"] = mb_convert_encoding($arRes["NAME"], "UTF-8", "windows-1251");

		$FROM = 'Компания «Микротест»';
		$start_day = explode(' ', $arRes["PROPERTY_DATE_START_VALUE"]);
		$start_d = explode('.', $start_day[0]);
		if ($start_day[1]) {
			$start_t = explode(':', $start_day[1]);
		} else {
			$start_t = array(
				0,
				0,
				0
			);
		}
		$end_day = explode(' ', $arRes["PROPERTY_DATE_END_VALUE"]);
		$end_d = explode('.', $end_day[0]);
		if ($end_day[1]) {
			$end_t = explode(':', $end_day[1]);
		} else {
			$end_t = array(
				0,
				0,
				0
			);
		}
		$DTSTART = gmdate("Ymd\THis\Z", mktime($start_t[0], $start_t[1], $start_t[2], $start_d[1], $start_d[0], $start_d[2]));
		$DTEND = gmdate("Ymd\THis\Z", mktime($end_t[0], $end_t[1], $end_t[2], $end_d[1], $end_d[0], $end_d[2]));

		$filename = $arRes["NAME"] . '_' . $arRes["PROPERTY_DATE_START_VALUE"] . '-' . $arRes["PROPERTY_DATE_END_VALUE"];
		header("Content-Type: text/Calendar; charset=UTF-8;");
		header("Content-Disposition: inline; filename=\"" . $filename . ".ics\"");

		echo "BEGIN:VCALENDAR\n";
		echo "PRODID:-//www.microtest.ru//iCalendar Export//EN\n";
		echo "VERSION:2.0\n";
		echo "METHOD:REQUEST\n";
		echo "BEGIN:VEVENT\n";
		// echo "ATTENDEE;ROLE=REQ-PARTICIPANT;CN=EDU:MAILTO:edu@softline.ru\n";
		echo "ORGANIZER;CN=" . $FROM . ":MAILTO:training@microtest.ru\n";
		echo "DTSTART:" . $DTSTART . "\n";
		echo "DTEND:" . $DTEND . "\n";
		echo "LOCATION:" . $arRes["PROPERTY_PLACE_EVENTS_VALUE"] . "\n";
		echo "TRANSP:OPAQUE\n";
		echo "SEQUENCE:0\n";
		echo "UID:" . date('HisYmd') . "\n";
		echo "DTSTAMP:" . date('Ymd') . 'T' . date('His') . "\n";
		echo "CATEGORIES:MT Events\n";
		echo "DESCRIPTION:" . $desc . "\n";
		echo "SUMMARY:" . $arRes["NAME"] . "\n";
		echo "PRIORITY:5\n";
		echo "CLASS:PUBLIC\n";
		echo "URL:http://www.microtest.ru/press/events/" . $arRes["ID"] . "/\n";
		echo "STATUS:CONFIRMED\n";
		echo "BEGIN:VALARM\n";
		echo "ACTION:DISPLAY\n";
		echo "TRIGGER:-PT150M\n";
		echo "DESCRIPTION:\n";
		echo "SUMMARY:" . $arRes["NAME"] . "\n";
		echo "DURATION:2\n";
		echo "REPEAT:2\n";
		echo "END:VALARM\n";
		echo "END:VEVENT\n";
		echo "END:VCALENDAR\n";
	}
} else {
	require_once $_SERVER["DOCUMENT_ROOT"] . '/404_inc.php';
}


require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>