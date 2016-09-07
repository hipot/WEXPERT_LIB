<?//die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (! $USER->IsAdmin()) {
	die('Bad access!');
}

function chmod_R($path, $perm)
{
	if (is_dir($path)) {
		chmod($path."/".$file, $perm);
		$handle = opendir($path);

		if (! $handle) {
			echo "Error: ".$path;
			return;
		}

		while (false !== ($file = readdir($handle))) {
			if ($file == "." || $file == ".." || $file == '.htaccess') {
				continue;
			}

			chmod_R($path."/".$file, $perm);
		}
		closedir($handle);
		return true;

	} elseif(is_file($path)) {
		$file_perm = $perm ^ 0111;
		return chmod($path, $file_perm);
	}
	return;
}

$path = $_GET["path"];
if (!trim($path))
{
	?>
	<form method=get>
		Путь от корня сайта: <input name=path value='<?=dirname($_SERVER['SCRIPT_NAME'])?>'><br>
		Права: <input name=perm value='0777'><br>
		<input type=submit value='OK'>
	</form>
	<?
}
else
{
	$path = realpath($_SERVER["DOCUMENT_ROOT"] . "/" . $path);

	if (intval($_GET['perm'])) {
		$perm = octdec(intval($_GET['perm']));
	} else {
		$perm = 0777;
	}

	if (chmod_R($path, $perm)) {
		echo "OK: ".$path;
	}
}
?>