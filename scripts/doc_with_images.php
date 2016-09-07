<?
/**
 * Генерируем ворд из HTML и прикладываем к нему картинки
 *
 * @see http://sebsauvage.net/wiki/doku.php?id=word_document_generation
 *
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// класс-объединяет документ в один файл
class mime10class
{
	private $data;

	const boundary='----=_NextPart_ERTUP.EFETZ.FTYIIBVZR.EYUUREZ';

	function __construct()
	{
		$this->data="MIME-Version: 1.0\nContent-Type: multipart/related; boundary=\"".self::boundary."\"\n\n";
	}

	public function addFile($filepath, $contenttype, $data)
	{
		$this->data = $this->data.'--'.self::boundary."\nContent-Location: file:///C:/".preg_replace('!\\\!', '/', $filepath)."\nContent-Transfer-Encoding: base64\nContent-Type: ".$contenttype."\n\n";
		$this->data = $this->data.base64_encode($data)."\n\n";
	}

	public function getFile()
	{
		return $this->data.'--'.self::boundary.'--';
	}

	public static function get_mime($file)
	{
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} else if (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else if (!stristr(ini_get("disable_functions"), "shell_exec")) {
			// http://stackoverflow.com/a/134930/1593459
			$file = escapeshellarg($file);
			$mime = shell_exec("file -bi " . $file);
			return $mime;
		} else {
			return false;
		}
	}
}


// документ и картинки в нем
$html = 'Превед  <p></p> <img src="images/image1.png" /> <p></p> <img src="images/image2.png" />';
$arImages = array(
	'image1.png' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/wexpert/iblock.list/templates/spec_index/images/rigth-arr.png',
	'image2.png' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/wexpert/iblock.list/templates/spec_index/images/left-arr.png',
);

// генерация
header('Content-Type: application/msword');
header('Content-disposition: filename=td-eurotrade'.date('d-m-Y').'.doc');
$doc = new mime10class();
$doc->addFile('mydocument.htm','text/html; charset="windows-1251"', mb_convert_encoding($html, 'WINDOWS-1251', 'UTF-8'));
foreach ($arImages as $docName => $img) {
	$doc->addFile('images\\'.$docName, mime10class::get_mime($img), file_get_contents($img));
}

echo $doc->getFile();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>