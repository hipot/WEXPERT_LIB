<?
/**
 * удаленная авторизация, клиент
 *
 * @author wexpert, 2015
 * @version 3.01
 *
 * Для отладки (если не пускает на сайт):
 *
 * 1/ заводим переменную в dbconn.php
 * define("LOG_FILENAME", $_SERVER['DOCUMENT_ROOT'] . '/bx_log.txt');
 *
 * 2/ пытаемся авторизоваться и смотрим файл bx_log.txt в корне сайта
 *
 */

define("BXMODID_WE",								"WEXPERTSITE");
define("SITE_" . BXMODID_WE,						"access.wexpert.ru");
define("URL_" . BXMODID_WE,							"/bitrix/extauth.php");
define("UTF_" . BXMODID_WE,							"&utf=Y");
define("USE_DOMAIN_CHECK_" . BXMODID_WE,			"N");


AddEventHandler("main", "OnUserLoginExternal",	array("weBitrixAuth", "OnUserLoginExternal"));
AddEventHandler("main",	"OnExternalAuthList",	array("weBitrixAuth", "OnExternalAuthList"));


class weBitrixAuth
{
	/**
	 * Вызывается каждый раз при авторизации,
	 * перед тем, как проверить пользователя локально
	 *
	 * @param array $arArgs Массив полей при авторизации
	 * @return void|Ambigous <boolean, number>
	 */
	static public function OnUserLoginExternal(&$arArgs)
	{
		// $LOGIN и $PASSWORD приходит
		extract($arArgs);

		$groups_map = Array(
			/*'Far site Group ID' => 'Local Group ID',*/
			'1' => '1',		//admins
			'2' => '2',		//all users
			'3'	=> '1'		//all has access is admin to
		);

		if (! function_exists('curl_setopt_array')) {
			$fp		= fsockopen(constant("SITE_".BXMODID_WE), 80, $errno, $errstr, 2);
			$bOpen	= ($fp) ? true : false;
			fclose($fp);
		} else {
			$bOpen	= self::checkServerAvailability(
				'http://' . constant("SITE_".BXMODID_WE) . constant("URL_".BXMODID_WE)
			);
			$errstr	= 'CURL ERROR';
			$errno	= '50X';
		}

		if (! $bOpen) {
			AddMessage2Log("fsockopen $errstr ($errno)", "external-login");
			return;
		}

		$salt = QueryGetData(
			constant("SITE_".BXMODID_WE),
			80,
			constant("URL_".BXMODID_WE),
			"login=".urlencode($LOGIN)."&action=salt",
			$error_number,
			$error_text,
			"POST"
		);

		if (trim($salt) == '') {
			return;
		}

		$params = "login=" . urlencode($LOGIN) . "&password=" . md5($salt . $PASSWORD) . constant("UTF_" . BXMODID_WE);

		if (constant("USE_DOMAIN_CHECK_".BXMODID_WE) == 'Y') {
			$params .= '&domain=' . self::getDomain();
		}

		$result = QueryGetData(
			constant("SITE_".BXMODID_WE),
			80,
			constant("URL_".BXMODID_WE),
			$params,
			$error_number,
			$error_text,
			"POST"
		);
		$arUser = unserialize($result);

		if (! is_array($arUser) || $arUser['LOGIN'] != $LOGIN) {
			return;
		}

		global $DB, $USER, $APPLICATION;

		$arFields = $arUser;

		unset($arFields['ID']);
		unset($arFields['GROUP_ID']);
		unset($arFields["TIMESTAMP_X"]);
		unset($arFields["DATE_REGISTER"]);

		$arFields['EXTERNAL_AUTH_ID']	= BXMODID_WE;
		$arFields["ACTIVE"]				= "Y";
		$arFields["PASSWORD"]			= $PASSWORD;
		$arFields["LID"]				= SITE_ID;

		if (constant('BX_UTF') !== true) {
			$arFields['NAME']		= mb_convert_encoding($arFields['NAME'], 		'windows-1251', 'utf-8');
			$arFields['LAST_NAME']	= mb_convert_encoding($arFields['LAST_NAME'], 	'windows-1251', 'utf-8');
		}

		$oUser = new CUser;
		$res = CUser::GetList($O, $B, array("LOGIN_EQUAL_EXACT" => $LOGIN, "EXTERNAL_AUTH_ID" => BXMODID_WE));

		if (! ($ar_res = $res->Fetch())) {
			$ID = $oUser->Add($arFields);
		} else {
			$ID	= $ar_res["ID"];
			$oUser->Update($ID, $arFields);
		}

		if ($ID <= 0) {
			AddMessage2Log("error sync with user base " . $oUser->LAST_ERROR, "external-login");
			return;
		}

		$USER->SetParam(BXMODID_WE . "_USER_ID", $arUser['ID']);
		$user_groups = $arUser["GROUP_ID"];

		$arUserGroups = array();

		if (count($user_groups) > 0) {
			$arUserGroups = CUser::GetUserGroup($ID);
			foreach ($groups_map as $ext_group_id => $group_id) {

				if (in_array($ext_group_id, $user_groups)) {
					$arUserGroups[] = $group_id;
				} else {
					$arUserGroups = array_diff($arUserGroups, array($group_id));
				}
			}
			if (in_array(1, $user_groups) || in_array(3, $user_groups)) {
				$arUserGroups[] = 1;
			}
		}
		$arUserGroups = array_filter($arUserGroups);

		if (count($arUserGroups) <= 0) {
			AddMessage2Log("error no user groups returned", "external-login");
			return;
		}

		CUser::SetUserGroup($ID, $arUserGroups);
		$arArgs["store_password"] = "N";

		return $ID;
	}

	static public function OnExternalAuthList()
	{
		return array(array("ID" => BXMODID_WE, "NAME" => "WE auth"));
	}


	/**
	 * Возвращает доступен ли сайт
	 *
	 * @param string $url - URL для проверки
	 * @param bool|int $port - порт
	 * @return boolean
	 * @use curl
	 */
	static function checkServerAvailability($url, $port = false)
	{
		/**
		 * Таймаут по умолчанию
		 * @var int
		 */
		$timeOut = 2;

		if (($url = trim($url)) == '') {
			return false;
		}
		$port = trim($port);

		$ch = curl_init();

		$options = array(
			CURLOPT_URL 				=> $url,
			CURLOPT_CONNECTTIMEOUT		=> $timeOut,
			CURLOPT_TIMEOUT				=> $timeOut,
			CURLOPT_DNS_CACHE_TIMEOUT	=> $timeOut,
			CURLOPT_FAILONERROR			=> true,
			CURLOPT_NOBODY				=> true,
			CURLOPT_HEADER				=> true,
			CURLOPT_RETURNTRANSFER		=> true,
		);

		if ($port && preg_match('~^[\d]+$~', $port)) {
			$options[ CURLOPT_PORT ] = $port;
		}

		curl_setopt_array($ch, $options);

		$res = trim( curl_exec($ch) );
		curl_close($ch);

		if (
			preg_match('~^HTTP/[\d\.]+\s+([\d]+)\s+~iu', $res, $m)
			&& $m[1] == 200
		) {
			return true;
		}

		return false;
	}

	/**
	 * Получение домена
	 * @return string
	 */
	static function getDomain()
	{
		return str_replace(array(':80', ':8888', ':8080'), '', $_SERVER['HTTP_HOST']);
	}
}


?>