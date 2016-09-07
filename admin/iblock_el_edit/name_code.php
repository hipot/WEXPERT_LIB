<?CUtil::InitJSCore(array('translit'));?>
<script>
var linked=<?if($bLinked) echo 'true'; else echo 'false';?>;
function set_linked()
{
	linked=!linked;

	var name_link = document.getElementById('name_link');
	if(name_link)
	{
		if(linked)
			name_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
		else
			name_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
	}
	var code_link = document.getElementById('code_link');
	if(code_link)
	{
		if(linked)
			code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
		else
			code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
	}
	var linked_state = document.getElementById('linked_state');
	if(linked_state)
	{
		if(linked)
			linked_state.value='Y';
		else
			linked_state.value='N';
	}
}
var oldValue = '';
function transliterate()
{
	if(linked)
	{
		var from = document.getElementById('NAME');
		var to = document.getElementById('CODE');
		if(from && to && oldValue != from.value)
		{
			BX.translit(from.value, {
				'max_len' : <?echo intval($arTranslit['TRANS_LEN'])?>,
				'change_case' : '<?echo $arTranslit['TRANS_CASE']?>',
				'replace_space' : '<?echo $arTranslit['TRANS_SPACE']?>',
				'replace_other' : '<?echo $arTranslit['TRANS_OTHER']?>',
				'delete_repeat_replace' : <?echo $arTranslit['TRANS_EAT'] == 'Y'? 'true': 'false'?>,
				'use_google' : <?echo $arTranslit['USE_GOOGLE'] == 'Y'? 'true': 'false'?>,
				'callback' : function(result){to.value = result; setTimeout('transliterate()', 250);}
			});
			oldValue = from.value;
		}
		else
		{
			setTimeout('transliterate()', 250);
		}
	}
	else
	{
		setTimeout('transliterate()', 250);
	}
}
transliterate();
</script>




<?
if($arTranslit["TRANSLITERATION"] == "Y")
{
?>
	<tr id="tr_NAME">
		<td><span class="required">*</span><?echo GetMessage("IBLOCK_NAME")?></td>
		<td nowrap>
			<input type="text" name="NAME" id="NAME" size="50" maxlength="255" value="<?echo $str_NAME?>">
			<image id="name_link" title="<?echo GetMessage("IBEL_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
		</td>
	</tr>
	<tr id="tr_CODE">
		<td><span class="required">*</span>Символьный код</td>
		<td nowrap>
			<input type="text" name="CODE" id="CODE" size="50" maxlength="255" value="<?echo $str_CODE?>">
			<image id="code_link" title="<?echo GetMessage("IBEL_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
		</td>
	</tr>
<?}
else
{?>
    <tr>
		<td><span class="required">*</span><?echo GetMessage("IBLOCK_NAME")?></td>
	<td>
		<input type="text" size="20" name="NAME" maxlength="255" value="<?echo $str_NAME?>">
		</td>
	</tr>
<?}?>

<?
if ($view!="Y" && CModule::IncludeModule("catalog") && CCatalog::GetByID($IBLOCK_ID))
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/templates/product_edit.php");
}
?>