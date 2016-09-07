<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$rsGr = CGroup::GetList(($by="c_sort"), ($order="desc"));
while($arGr = $rsGr->GetNext()){
	$arUserGroups[ $arGr['ID'] ] = $arGr['NAME'];
}

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"ELEMENT_ID" => Array(
			"NAME" => 'Идентификатор элемента',
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$arResult[\"ID\"]}",
			"COLS" => 30,
			"PARENT" => "BASE",
		),
		"MODER_GR" => Array(
			"NAME" => 'Группы-модераторы',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arUserGroups,
			"DEFAULT" => "1",
			"COLS" => 30,
			"PARENT" => "BASE",
		),
		"PRE_MODER" => Array(
			"NAME" => 'Использовать премодерацию',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"USE_EDITOR" => Array(
			"NAME" => 'Использовать визуальный редактор',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"nPageSize" => Array(
			"NAME" => 'Количество комментариев на страницу',
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "0",
			"COLS" => 30,
			"PARENT" => "BASE",
		),
		"WRITE_ALL" => Array(
			"NAME" => 'Разрешать всем пользователям оставлять комментарии',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "BASE",
		),
		"WRITE_COM_COM" => Array(
			"NAME" => 'Комментировать комментарии',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"MAX_DEPTH" => Array(
			"NAME" => 'Макс. вложенность комментариев',
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "5",
			"COLS" => 30,
			"PARENT" => "BASE",
		),
		"TAB_SIZE" => Array(
			"NAME" => 'Размер таба комментария',
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"COLS" => 30,
			"PARENT" => "BASE",
		),
		"USE_BAD_GOOD" => Array(
			"NAME" => 'Использовать дополнительные поля',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"USE_RATE" => Array(
			"NAME" => 'Использовать рейтинги',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"USE_CAPTCHA" => Array(
			"NAME" => 'Использовать CAPTCHA',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),
		"SEND_MAIL" => Array(
			"NAME" => 'Отправлять письмо',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "BASE",
		),

		"MY_TEMPLATE_PAGER" => Array(
			"NAME" => 'Использовать внутренний шаблон постранички',
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"TEMPLATE_PAGER" => Array(
			"NAME" => 'Шаблон постранички',
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 30,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"CACHE_TIME"	=>	array("DEFAULT"=>"86400"),
	)
);
?>
