<?
//
// FIXME примитивная защита, убираем ссылки
//
global $USER;

$arCurValues = array(4, 9);
$arUserGroups = $USER->GetUserGroupArray();

if (count(array_intersect($arCurValues, $arUserGroups)) <= 0 && !$USER->IsAdmin()) {
	return;
}

$aMenuLinks = array(
	array(
		"Дополнительно",
		"",
		array(),
		array("SEPARATOR" => "Y", "SECTION_ID" => "some_configs", "SORT" => 50),
		""
	),
	array(
		"Пропатченный генератор карты сайта",
		"/bitrix/admin/we_search_sitemap.php?lang=ru",
		array(),
		array("SORT" => 100),
		""
	),
);
?>