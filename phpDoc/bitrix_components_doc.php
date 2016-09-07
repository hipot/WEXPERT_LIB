<?
if (!isset($_GET['gen'])) {
	exit;
}

set_time_limit(0);

header('Content-Type: text/html; charset=windows-1251');

require_once __DIR__ . '/phpQuery-onefile.php';
phpQuery::$defaultCharset = 'windows-1251';

class Link
{
	static $addedHrefs = array();

	public $name;
	public $href;

	function __construct($n, $l)
	{
		$this->name = trim($n);
		$this->href = $l;

		self::$addedHrefs[] = $l;
	}

	static function getLinksFromUrl($url)
	{
		global $arMainLinks, $host;

		$docCont = phpQuery::newDocumentHTML( file_get_contents($url) );
		$pml = pq('#mainNode a');
		foreach ($pml as $href) {
			if (pq($href)->text() == '' || pq($href)->attr('href') == $url . 'index.php') {
				continue;
			}

			$iterUrl = pq($href)->attr('href');
			if (strpos($iterUrl, 'http') === false) {
				$iterUrl = $host . $iterUrl;
			}

			if (in_array($iterUrl, Link::$addedHrefs) || $iterUrl == 'http://dev.1c-bitrix.ru/user_help/help/terms.php') {
				continue;
			}

			$arMainLinks[] = new Link(mb_convert_encoding(trim(pq($href)->text()), 'windows-1251'), $iterUrl);
		}
	}

	static function sortLinksByUrl(&$arMainLinks)
	{
		usort($arMainLinks, function ($a, $b) {
			return strnatcmp($a->href, $b->href);
		});
	}
}



$host = 'http://dev.1c-bitrix.ru';
//$startUrl = $host . '/api_help/'; // тут не весь набор компонент
$startUrl = $host . '/user_help/';

$arMainLinks = array();

Link::getLinksFromUrl( $startUrl );
foreach ($arMainLinks as $i => $lnk) {
	Link::getLinksFromUrl( $lnk->href );
}

Link::sortLinksByUrl($arMainLinks);


ob_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=windows-1251" />
	<title>Список всех стандартных компонентов одним списком</title>
	<link href="<?=$host?>/bitrix/templates/help/styles.css?1284988419" type="text/css" rel="stylesheet" />
	<link href="<?=$host?>/bitrix/templates/help/template_styles.css?1259762482" type="text/css" rel="stylesheet" />
	<style type="text/css">
		body {overflow:auto !important;}
		h1 {font-size: 140%; padding:30px 0px 10px 0px;}
		.tnormal td {width:33%;}
	</style>
</head>
<body>


<?
foreach ($arMainLinks as $i => $lnk) {
	$docCont = phpQuery::newDocumentHTML(file_get_contents($lnk->href));

	$hasComp = false;
	$compTable = '';

	$pqt = pq("table.tnormal");
	foreach ($pqt as $table) {
		//echo '<div style="padding:50px 0px;">' . pq($table)->htmlOuter() . '</div>';
		if (preg_match('#Компонент#is', pq($table)->htmlOuter())) {
			$hasComp = true;
			$compTable .= pq($table)->htmlOuter();
		}
	}
	if ($hasComp) {
		echo '<h1>' . $lnk->name . '</h1>';
		echo $compTable;
	}
}
?>
</body>
</html>
<?
unlink('index.html');
file_put_contents('index.html', ob_get_contents());
ob_flush();
?>