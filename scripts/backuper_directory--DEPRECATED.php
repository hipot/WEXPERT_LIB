<?
/**
 * Запаковываем нужные директории в архив и отсылаем на email
 *
 *	@author wexpert
 *	@version 0.001 beta
 */

set_time_limit(0);

$_SERVER['DOCUMENT_ROOT'] = '/var';

$toEmail 	= '';
$fromEmail 	= '';

$arFiles = array(
	'/bitrix/components/wexpert/',
	'/bitrix/templates/',

	'/urlrewrite.php',
);

$path = $_SERVER['DOCUMENT_ROOT'] . '/upload/zip_cache/';
foreach (glob($path . 'esky_backup_*.zip') as $filename) {
	unlink($filename);
}

$zip = new ZipArchive();
$zipFile = $path . 'esky_backup_' . date('Y-m-d') . '.zip';
if (! ($zip->open($zipFile, ZIPARCHIVE::OVERWRITE) === true)) {
	exit('D"not create zip archive...');
}

foreach ($arFiles as $path) {
	$path = $_SERVER['DOCUMENT_ROOT'] . $path;
	if (is_file($path)) {
		$zip->addFile($path, basename($path));
	} elseif (is_dir($path)) {
		Search_fix(rtrim($path, '/'));
	}
}

$zip->close();

$r = multi_attach_mail_fix($toEmail, $zipFile, $fromEmail);

/**
 * Немного видоизмененная функция
 * оригинал в комментарии php.net function.mail.html#105661
 *
 * @param string $to
 * @param string $file
 * @param string $sendermail
 * @return boolean
 */
function multi_attach_mail_fix($to, $file, $sendermail)
{
	$from = "<".$sendermail.">";
	$subject = basename($file);
	$message = date("Y.m.d H:i:s");
	$headers = "From: $from";

	// boundary
	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

	// headers for attachment
	$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

	// multipart boundary
	$message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
	"Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

	if(is_file($file)) {
		$message .= "--{$mime_boundary}\n";
		$fp = @fopen($file, "rb");
		$data = @fread($fp,filesize($file));
		@fclose($fp);
		$data = chunk_split(base64_encode($data));
		$message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .
		"Content-Description: ".basename($files[$i])."\n" .
		"Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .
		"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
	}

	$message .= "--{$mime_boundary}--";
	$returnpath = "-f" . $sendermail;
	$ok = @mail($to, $subject, $message, $headers, $returnpath);

	if($ok) {
		return true;
	}
	return false;
}

/**
 * Функция рекурсивного спуска по директории
 *
 * @param string $path - путь
 * @return boolean
 */
function Search_fix($path)
{
	global $zip;

	if (is_dir($path)) {// dir
		$dir = opendir($path);
		while($item = readdir($dir)) {
			if ($item == '.' || $item == '..') {
				continue;
			}
			Search_fix($path . '/' . $item);
		}
		closedir($dir);
	} else {// file
		$zip->addFile($path, str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $path));
	}
}
?>