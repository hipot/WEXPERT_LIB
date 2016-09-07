<?function ShowForm($id='', $input_id='TEXT', $ar=array(), $Params=array(), $arResult=array()){ global $APPLICATION; global $USER;?>
<form class="<?=$id?> com_f_block" method="POST" action="<?=$APPLICATION->GetCurPage();?>">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr class="fil_me">
			<td>Имя:</td>
			<td><input type="text" name="arCommentFields[AUTHOR_NAME]" value="<?=$ar['AUTHOR_NAME'];?>"/></td>
		</tr>
		<tr class="fil_me">
			<td>E-Mail:</td>
			<td><input type="text" name="arCommentFields[AUTHOR_EMAIL]" value="<?=$ar['AUTHOR_EMAIL'];?>"/></td>
		</tr>
				<?if ($arResult["USE_CAPTCHA"] == "Y"):?>
		<tr class="captcha">
			<td colspan="2">
			    Защита от автоматического заполнения
			</td>
		</tr>
		<tr>
			<td>
			    Введите слово на картинке <span class="starrequired">*</span>:<br />
			    <input type="text" name="captcha_word" maxlength="50" value="" />
			</td>
			<td>
			    <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
			    <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /> <br />
			</td>
		</tr>
		<?endif;?>
		<tr>
			<td colspan="2">
				<br clear="all" />
				Комментарий:
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<?
			if($Params['USE_EDITOR']=='Y'){
				$APPLICATION->IncludeComponent("bitrix:fileman.light_editor", "", array(
					"CONTENT" => $ar['TEXT'],
					"INPUT_NAME" => "arCommentFields[TEXT]",
					"INPUT_ID" => $input_id,
					"WIDTH" => "500px",
					"HEIGHT" => "300px",
					"RESIZABLE" => "N",
					"VIDEO_ALLOW_VIDEO" => "N",
					"USE_FILE_DIALOGS" => "Y",
					"ID" => $id,
					"JS_OBJ_NAME" => ""
					),
					false
				);
			} else{?>
				<textarea name="TarCommentFields[TEXT]" id="TEXT" cols="50" rows="10"><?=$ar['TEXT']?></textarea>
			<?}?>
			</td>
		</tr>
		<tr>
			<td class="submit"><input type="submit" value="Отправить"/></td>
			<td></td>
		</tr>
	</table>
	<input type="hidden" name="arCommentFields[DATE]" value="<?=$ar['DATE']?>"/>
	<input type="hidden" name="arCommentFields[DEL]" value="<?=$ar['DEL']?>"/>
	<input type="hidden" name="arCommentFields[PARENT_ID]"  value="<?=$ar['PARENT_ID']?>"/>
	<input type="hidden" name="arCommentFields[ID]" value="<?=$ar['ID']?>"/>
	<input type="hidden" name="arCommentFields[IBLOCK_ELEMENT_ID]" value="<?=$ar['IBLOCK_ELEMENT_ID']?>"/>
	<input type="hidden" name="arCommentFields[USER_ID]" value="<?=$ar['USER_ID']?>"/>
</form>
<?}?>