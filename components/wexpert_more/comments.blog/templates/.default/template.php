<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?
ShowError($arResult["error_msg"]);

$APPLICATION->IncludeComponent("bitrix:blog.post.comment", $arParams['BLOG_POST_COMMENT_TEMPLATE'], array(
	"ID" => $arResult["BLOG"]["PROPERTY_".ToUpper($arParams["LINK_IB_PROP_CODE"])."_VALUE"],
	"BLOG_URL" => $arResult["BLOG"]["IBLOCK_TYPE_ID"],
	"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
	"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
	"SMILES_COUNT" => "4",
	"IMAGE_MAX_WIDTH" => "600",
	"IMAGE_MAX_HEIGHT" => "600",
	"EDITOR_RESIZABLE" => "Y",
	"EDITOR_DEFAULT_HEIGHT" => "200",
	"EDITOR_CODE_DEFAULT" => "N",
	"PATH_TO_BLOG" => "",
	"PATH_TO_USER" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"PATH_TO_SMILE" => "",
	"SIMPLE_COMMENT" => "Y",
	"USE_ASC_PAGING" => "N",
	"SHOW_RATING" => "N",
	"ALLOW_VIDEO" => "N",
	"SHOW_SPAM" => "N",
	"NO_URL_IN_COMMENTS" => "L",
	"NO_URL_IN_COMMENTS_AUTHORITY" => "",
	"BLOG_VAR" => "",
	"POST_VAR" => "",
	"USER_VAR" => "",
	"PAGE_VAR" => "pagen",
	"COMMENT_ID_VAR" => "",
	"SEO_USER" => "N",
	"NOT_USE_COMMENT_TITLE" => "Y"
	),
	false
);
?>