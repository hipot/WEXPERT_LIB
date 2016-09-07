<?
/**
 * Просмотр параметров сервера
 * @author wexpert.ru, 2015
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$APPLICATION->SetTitle('Ресурсы сервера');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array("DIV" => "edit1", "TAB" => "Актуальный Размер кеша", "ICON"=>"main_user_edit", "TITLE"=>""),
);
$tabControl = new CAdminTabControl("sales_mail_select_tabControl", $aTabs);

/*$aMenu = array(
	array(
		"TEXT"			=> "",
		"TITLE"			=> "",
		"LINK"			=> "",
		"ICON"			=> "",
		"SEPARATOR"		=> true
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();*/

?>


<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>?lang=ru" ENCTYPE="multipart/form-data" name="post_form" class="params">
<?=bitrix_sessid_post();?>
<input type="hidden" value="<?=$from_form?>" />
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<style>
	tr.sample-tr > td:FIRST-CHILD {text-align: right; padding-right: 15px;}
	.left-td {text-align: right; padding-right: 20px;}

	.params pre {font-size:14px;}
</style>


<colgroup>
	<col width=20% />
	<col width=80% />
</colgroup>
<tr class="heading">
	<td colspan="2">Размер файлов кеша (временные папки, которые можно чистить, но ухуджается производительность)</td>
</tr>
<tr class="sample-tr">
	<td></td>
	<td nowrap><strong><?system('du -sh ' . $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache');?></strong></td>
</tr>
<tr class="sample-tr">
	<td></td>
	<td nowrap><strong><?system('du -sh ' . $_SERVER['DOCUMENT_ROOT'] . '/bitrix/managed_cache');?></strong></td>
</tr>
<tr class="sample-tr">
	<td></td>
	<td nowrap><strong><?system('du -sh ' . $_SERVER['DOCUMENT_ROOT'] . '/upload/cimg_cache');?></strong></td>
</tr>

<tr class="heading">
	<td colspan="2">Объем оперативной памяти (чем больше свободно - тем лучше, см. колонку free)</td>
</tr>

<tr>
	<td class="left-td">
	<b>RAM (Gb)</b>
	</td>
	<td nowrap>
		<?
		echo '<pre>';
		system('free -g');
		echo '</pre>';
		?>
	</td>
</tr>

<tr class="heading">
	<td colspan="2">Жесткие диски, обратите внимание на процент занятости, колонка Use%</td>
</tr>

<tr>
	<td class="left-td">
	<b>HDD (Gb)</b>
	</td>
	<td nowrap>
		<?
		echo '<pre>';
		system('df -h');
		echo '</pre>';
		?>
	</td>
</tr>

<tr class="heading">
	<td colspan="2">Кол-во запущенных процессов веб-сервера. Чем больше - тем выше посещаемость.</td>
</tr>

<tr>
	<td class="left-td">
	<b>Apache process (count)</b>
	</td>
	<td nowrap>
		<?
		echo '<pre>';
		// grep httpd
		system('ps axww | grep apache | wc -l');
		echo '</pre>';
		?>
	</td>
</tr>


<tr class="sample-tr">
	<td></td>
	<td nowrap><br /><br /><br /><br /><br /></td>
</tr>




<?
/*
$tabControl->Buttons(array(
	"disabled"	=> false,
	"back_url"	=> ".php?lang=" . LANG,
));
*/
$tabControl->End();
?>

<input type="hidden" name="lang" value="<?=LANG?>">

</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>