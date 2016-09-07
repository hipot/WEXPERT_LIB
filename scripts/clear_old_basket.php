<?
/**
 * Скрипт чистит старые анонимные брошенные более чем 30 дней корзины.
 * Также необходимо проверить наличие агента CSaleUser::DeleteOldAgent(30, 0);
 *
 * Прекратить выполнение, когда итерация будет достаточно быстро выполняться (визуально)
 * или не будет уменьшаться кол-во строк в таблице b_sale_fuser.
 *
 * @version 1.0
 * @author www.wexpert.ru, 2014
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$nDays			= 30;		// период в днях, за 30 дней
$onStepIterCnt	= 15; 		// сколько итераций вызвать, за одну 300 строк чистит (в версии 14.5)

if (! $USER->IsAdmin()) {
	die('no access');
}

CModule::IncludeModule('sale');

$t = gettimeofday();
$_start = $t['sec'] * 1000000.0 + $t['usec'];

for ($i = 0; $i < $onStepIterCnt; $i++) {
	CSaleUser::DeleteOldAgent($nDays, 0);
}

$t = gettimeofday();
$_stop = $t['sec'] * 1000000.0 + $t['usec'];
$elapsed = ($_stop - $_start) / 1000000.0;

$c = $DB->Query('SELECT COUNT(*) AS CNT FROM b_sale_fuser')->Fetch();

echo 	'<code>CLEARNING OLD CSaleUser...<br />'
		. 'STEP DONE ON <b>' . $elapsed . '</b> sec.<br />'
		. '~' . intval($c['CNT']) . ' ROWS ON b_sale_fuser TABLE</code>';

?>
<title>CLEARNING OLD CSaleUser... <?=intval($c['CNT'])?> ROWS</title>
<script type="text/javascript">
window.setTimeout(function(){
	window.location.reload(true);
}, 1000);
</script>
<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>