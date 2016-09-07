<?
/**
 * Скрипт удаленной авторизации, бекэенд /bitrix/extauth.php
 *
 * Шаги:
 * 1/ login&action=salt получение соли, она передается во втором шаге
 * 2/ login&password&utf=Y&domain - авторизация
 *
 * @author wexpert, 2014
 * @version 2.0
 */

include ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (trim($_REQUEST['login']) == '') {
	die();
}

// performance trick
if (! in_array( ToLower(trim($_REQUEST['login'])), GetBaseLoginsWithCache(false) )) {
	exit;
}

//file_put_contents(__DIR__ . '/extauth_log', serialize($_REQUEST) . "\n\n", FILE_APPEND);
$arUser = CUser::GetByLogin( trim($_REQUEST['login']) )->Fetch();

if ($_REQUEST['action'] == "salt") {

	if ($arUser['ACTIVE'] == 'Y') {
		echo substr($arUser["PASSWORD"], 0, strlen($arUser["PASSWORD"]) - 32);
		die();
	}

} else {

	if ($arUser['ACTIVE'] == 'Y' && substr($arUser['PASSWORD'], -32) == $_REQUEST['password']) {
		if ($_REQUEST['utf'] == "Y") {
			$arUser["NAME"]			= iconv("windows-1251", "utf-8", $arUser["NAME"]);
			$arUser["LAST_NAME"]	= iconv("windows-1251", "utf-8", $arUser["LAST_NAME"]);
		}
		$arFields = Array(
			"ID",
			"XML_ID",
			"LOGIN",
			"PASSWORD",
			"NAME",
			"LAST_NAME",
			"EMAIL"
		);

		$arSaveUser = array();
		foreach ($arFields as $field) {
			$arSaveUser[ $field ] = $arUser[ $field ];
		}

		$arUserGroups			= CUser::GetUserGroup($arUser['ID']);

		// проверка на пришедший домен $_REQUEST["domain"]
		if (trim($_REQUEST["domain"]) != ''
				&& !CheckDomainByUser($_REQUEST["domain"], $arUser['UF_ALLOW_DOMAINS'])
		) {
			$arUserGroupsEx		= array();
			foreach ($arUserGroups as $grId) {
				if ($grId == 3 || $grId == 1) {
					continue;
				}
				$arUserGroupsEx[] = $grId;
			}
			$arUserGroups		= $arUserGroupsEx;
		}
		$arSaveUser["GROUP_ID"]	= $arUserGroups;

		//file_put_contents(__DIR__ . '/extauth_log', serialize($arSaveUser) . "\n\n", FILE_APPEND);

		echo serialize($arSaveUser);
		die();
	}
}


function GetBaseLoginsWithCache($cacheTime = false)
{
	if ($cacheTime === false) {
		$cacheTime = 3600 * 24 * 31;
	}

	$arResult			= array();

	$componentName		= 'extauth';
	$CACHE_ID			= "php/$componentName".md5(serialize($cacheTime));
	$CACHE_DIR			= "php/$componentName/";
	$obPageCache		= new CPHPCache;

	if ($obPageCache->StartDataCache($cacheTime, $CACHE_ID, $CACHE_DIR)) {

		$rsU = CUser::GetList(($by = "id"), ($order = "asc"));
		while ($aU = $rsU->Fetch()) {
			$arResult[] = ToLower( $aU['LOGIN'] );
		}

		if (empty($arResult)) {
			$obPageCache->AbortDataCache();
		} else {
			$obPageCache->EndDataCache(array("arResult" => $arResult));
		}

	} else {
		$arVars		= $obPageCache->GetVars();
		$arResult	= $arVars["arResult"];
	}

	return $arResult;
}


function CheckDomainByUser($domain, $valueIds)
{
	$values = GetDomainLists();
	foreach ($valueIds as $val) {
		if (CheckDomainsMatch($domain, $values[$val])) {
			return true;
		}
	}
	return false;
}

function GetDomainLists()
{
	$arR = array();
	$rs = CUserFieldEnum::GetList(array(), array(
		"USER_FIELD_ID" => 1
	));
	while ($ar = $rs->GetNext()) {
		$arR[ $ar['ID'] ] = trim($ar['VALUE']);
	}
	return $arR;
}

function CheckDomainsMatch($domain1, $domain2)
{
	$domain1 = str_replace(array('www.'), '', $domain1);
	$domain2 = str_replace(array('www.'), '', $domain2);

	// проверка на domain.ru
	$domain1Ex = array_reverse(explode('.', $domain2));
	$domain1Ex = $domain1Ex[1] . '.' . $domain1Ex[0];

	//file_put_contents(__DIR__ . '/extauth_log', serialize(array($domain1, $domain2, $domain1Ex)) . "\n\n", FILE_APPEND);

	if ($domain1 == $domain2 || $domain1Ex == $domain2 || preg_match('#(wexpert.ru)|(dev.mnwb.com)#', $domain1)) {
		return true;
	}
	return false;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>