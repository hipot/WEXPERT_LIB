<?
/**
 * Файл с классом отправки почтовых сообщений с вложениями
 *
 *
 * @author hipot
 * @copyright 2011, WebExpert
 */

require_once dirname(__FILE__) . "/pmailer51/class.phpmailer.php";

/**
 * Класс отправки почтовых сообщений с вложениями, расширяет массив $arEventFields ключем 'FILES' для определения
 * файлов-вложений
 *
 * @example
 * <code>
 * $arFields = array(
 * 		'TO_EMAIL'      => 'hipot@ya.ru, some@domain.com',
 * 		'CUSTOM_FIELD'  => 'Такой вот <b>html</b>',
 * 		'FILES' => array(
 * 			array(
 * 				'SRC'   => $_SERVER['DOCUMENT_ROOT'] . '/upload/iblock/016/0_4ed7_7e61ad7b_l.jpg',
 * 				'NAME'  => ''
 * 			)
 * 		)
 * );
 *
 * $mailer = new AttachesBmailer();
 * if ($mailer->sendMessage('DEBUG_SEND', $arFields)) {
 * 		echo 'SENDED!';
 * } else {
 * 		echo 'ERROR SENDING!';
 * }
 * </code>
 *
 * @author hipot
 * @copyright 2011, WebExpert
 * @uses PHPMailer
 * @link http://phpmailer.codeworxtech.com/
 * @link http://sourceforge.net/projects/phpmailer/files/phpmailer%20for%20php5_6/PHPMailer%20v5.1/
 * @version 0.12b
 */
class AttachesBmailer extends PHPMailer
{
	/**
	 * Конструктор, устанавливает способ кодирования и кодировку файла
	 */
	function __construct()
	{
		$this->CharSet  = 'utf-8'; // 'utf-8' or 'windows-1251'
		$this->Encoding = 'base64';
		// $this->SetLanguage("ru", $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/classes/pmailer51/language/');
	}

	/**
	 * Отплавляет письмо по почтовому событию, умеет прикреплять файлы
	 *
	 * @param string $templateType тип почтового сообщения
	 * @param array $arFields массив для подачи в CEvent::Send(...), дополнительный ключ FILES - прикрепляемые файлы (массив массивов)
	 * вложеный массив должен иметь формат array("SRC" => 'полный путь к файлу', 'NAME' => 'имя файла')
	 * @param string $lid = SITE_ID Идентификатор сайта [optional]
	 * @return bool
	 */
	function sendMessage($templateType, $arFields, $lid = SITE_ID)
	{
		global $DB;

		if ($templateType == '' || !is_array($arFields)) {
			return false;
		}

		$rsMess = CEventMessage::GetList($byMs = "site_id", $orderMs = "desc", array(
			"TYPE"     => $templateType,
			"SITE_ID"  => $lid,
			"ACTIVE"   => "Y"
		));

		if ($arMessTpl = $rsMess->Fetch()) {
			// fix 2013-02-18 crus
			if ($arMessTpl['EVENT_NAME'] != $templateType) {
				continue;
			}

			// окончание строки
			$eol  = CAllEvent::GetMailEOL();

			// additional params
			if (! isset($arFields["DEFAULT_EMAIL_FROM"])) {
				$arFields["DEFAULT_EMAIL_FROM"] = COption::GetOptionString("main", "email_from", "admin@" . $GLOBALS["SERVER_NAME"]);
			}
			if (! isset($arFields["SITE_NAME"])) {
				$arFields["SITE_NAME"] = COption::GetOptionString("main", "site_name", $GLOBALS["SERVER_NAME"]);
			}
			if (! isset($arFields["SERVER_NAME"])) {
				$arFields["SERVER_NAME"] = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
			}

			// replace
			$from    = CAllEvent::ReplaceTemplate($arMessTpl["EMAIL_FROM"], $arFields);
			$to      = CAllEvent::ReplaceTemplate($arMessTpl["EMAIL_TO"], $arFields);
			$bcc     = CAllEvent::ReplaceTemplate($arMessTpl["BCC"], $arFields);
			$subj    = CAllEvent::ReplaceTemplate($arMessTpl["SUBJECT"], $arFields);
			$message = CAllEvent::ReplaceTemplate($arMessTpl["MESSAGE"], $arFields);

			$from  = trim($from, "\r\n");
			$to    = trim($to,   "\r\n");
			$subj  = trim($subj, "\r\n");
			$bcc   = trim($bcc,  "\r\n");

			// дополнительные преобразования
			if (COption::GetOptionString("main", "convert_mail_header", "Y") == "Y") {
				// get charset
				$strSql = "SELECT CHARSET FROM b_lang WHERE LID='" . $arMessTpl['SITE_ID'] . "' ORDER BY DEF DESC, SORT";
				$dbCharset = $DB->Query($strSql, false, "FILE: " . __FILE__ . "<br>LINE: " . __LINE__);
				$arCharset = $dbCharset->Fetch();
				$charset   = $arCharset["CHARSET"];

				$from = CAllEvent::EncodeMimeString($from, $charset);
				$to   = CAllEvent::EncodeMimeString($to, $charset);
				$subj = CAllEvent::EncodeMimeString($subj, $charset);
			}

			if (COption::GetOptionString("main", "CONVERT_UNIX_NEWLINE_2_WINDOWS", "N") == "Y") {
				$message = str_replace("\n", "\r\n", $message);
			}

			// дублировать все сообщения на ящик
			$all_bcc = COption::GetOptionString("main", "all_bcc", "");

			// заполняем поля для отправки
			$this->From = $from;
			$this->FromName = $arFields["SITE_NAME"];

			// адресов можно добавить много, через запятую
			$arToMails = explode(',', $to);
			foreach ($arToMails as $toV) {
				$toV = trim($toV);
				if ($toV != '') {
					$this->AddAddress($toV);
				}
			}

			if (! empty($bcc)) {
				$this->AddBCC($bcc);
			}
			if (! empty($all_bcc)) {
				$this->AddBCC($all_bcc);
			}

			$this->Subject = $subj;
			$this->Body    = $message;
			if ($arMessTpl["BODY_TYPE"] == "text") {
				$this->IsHTML(false);
			} else {
				$this->IsHTML(true);
			}

			// прикрепляем файлы
			if (array_key_exists('FILES', $arFields) && is_array($arFields['FILES'])) {
				foreach ($arFields['FILES'] as $arFile) {
					if ($arFile['TYPE'] == 'I') {
						$this->AddEmbeddedImage($arFile['SRC'], basename($arFile['SRC']), $arFile['NAME']);
					} else {
						$this->AddAttachment($arFile['SRC'], $arFile['NAME']);
					}
				}
			}

			return $this->Send();
		}
		return false;
	}
}
?>