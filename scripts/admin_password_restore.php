<?
require($_SERVER['DOCUMENT_ROOT']."/bitrix/header.php");

$passw = 'we123456';

if ($passw == 'we123456') {
	die('Error: Set Another Password For admin [1]!');
}

echo $USER->Update(1, array("PASSWORD" => $passw));
echo $USER->LAST_ERROR;
echo 'DONE!';

require($_SERVER['DOCUMENT_ROOT']."/bitrix/footer.php");
?>