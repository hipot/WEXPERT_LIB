<?
if (! defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

CModule::IncludeModule("iblock");
CModule::IncludeModule("blog");

global $USER, $DB;

$user_id = $USER->GetID();


if ($arParams['BLOG_POST_COMMENT_TEMPLATE'] == '') {
	$arParams['BLOG_POST_COMMENT_TEMPLATE'] = '.default';
}

// код свойства для привязок
if (! isset($arParams['LINK_IB_PROP_CODE'])) {
	$arParams['LINK_IB_PROP_CODE'] = 'blog_post_id';
}


$arResult["error_msg"] = '';


if ($USER->IsAuthorized()) {
	$arResult["AUTH_USER"]  = true;
	$arResult["use_capcha"] = false;
} else {
	$arResult["AUTH_USER"]   = false;
	$arResult["use_capcha"]  = true;
	$arResult["capcha_code"] = htmlspecialchars($APPLICATION->CaptchaGetCode());
}


// установка фильтров и сортировки
$arFilter = array("ACTIVE" => "Y");

if (isset($arParams["CODE"])) {
	$arFilter["CODE"] = trim($arParams["CODE"]);
}
if (isset($arParams["ID"])) {
	$arFilter["ID"] = intval($arParams["ID"]);
}

$arSelect = array("ID", "NAME", "IBLOCK_ID", "DETAIL_PAGE_URL", "PROPERTY_" . $arParams['LINK_IB_PROP_CODE']);

$rsItems = CIBlockElement::GetList(array(), $arFilter, false, array('nTopCount' => 1), $arSelect);
if (! $arItem = $rsItems->GetNext()) {
	ShowError('Cant open iblock elem, sorry ((');
	return;
}

/**
 * ID поста в блоге (связанного с элементом инфоблока)
 *
 * @var int
 */
$BLOG_POST_ID = intval($arItem["PROPERTY_" . ToUpper($arParams['LINK_IB_PROP_CODE']) . "_VALUE"]);


/**
 * ID блога с комментариями к элементам инфоблока
 *
 * @var int
 */
$BLOG_ID = intval($arParams["BLOG_ID"]);


$resBlog = CBlog::GetByID($BLOG_ID);
if (! $resBlog) {
	ShowError('Cant open blog, sorry ((');
	return;
}
$arItem['IBLOCK_TYPE_ID'] = $resBlog["URL"];


/**
 * пытаемся выбрать пост в блоге, к которому пишутся комментарии
 */
if ($BLOG_POST_ID) {
	$dbPosts = CBlogPost::GetList(array(), array(
		"ID"       => $BLOG_POST_ID,
		"BLOG_ID"  => $BLOG_ID
	), false, false, array("NUM_COMMENTS"));
	$arResult["arPost"] = $dbPosts->GetNext();
}



escape_post2result($arResult);


if ($arResult["arPost"]["NUM_COMMENTS"] < 1) {

	/**
	 * Добавление темы в блог
	 */
	if (!$BLOG_POST_ID && strlen($arResult["error_msg"]) <= 0) {

		$arFieldsNewBlogPost = array(
			"TITLE"              => $arItem["NAME"],
			"DETAIL_TEXT"        => $arItem["NAME"],
			"BLOG_ID"            => $arParams["BLOG_ID"],
			"AUTHOR_ID"          => 1,
			"=DATE_CREATE"       => $DB->GetNowFunction(),
			"DATE_PUBLISH"       => ConvertTimeStamp(false, "FULL"),
			//"PUBLISH_STATUS"   => BLOG_PUBLISH_STATUS_PUBLISH,
			"ENABLE_TRACKBACK"   => 'N',
			"ENABLE_COMMENTS"    => 'Y',
			"CATEGORY_ID"        => $arParams['BLOG_CATEGORY_ID']
		);

		$newID = CBlogPost::Add($arFieldsNewBlogPost);

		if (IntVal($newID) > 0) {
			CIBlockElement::SetPropertyValueCode($arItem["ID"], $arParams['LINK_IB_PROP_CODE'], $newID);
			$BLOG_POST_ID = $arItem["PROPERTY_" . ToUpper($arParams['LINK_IB_PROP_CODE']) . "_VALUE"] = $newID;
		} else if ($ex = $APPLICATION->GetException()) {
			$arResult["error_msg"] .= $ex->GetString();
		}
	}

	/*
	if (strlen($arResult["error_msg"]) <= 0) {
		LocalRedirect($APPLICATION->GetCurPageParam("", array("ID", "PAGE")));
	}*/
}

$arResult["BLOG"] = $arItem;
$this->IncludeComponentTemplate();




?>