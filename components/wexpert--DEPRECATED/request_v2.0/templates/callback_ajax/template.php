<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?// эти две дивки вместо тега form ?>
<div class="ajax_form" id="<?=$arParams['POST_NAME']?>_form">
<div class="ajax_form_wrapper">



	<? if ($_SESSION['READY_request_'.$arParams['POST_NAME']] == 'Y'):?>
		
		<div class="request_ready"><?=GetMessage('CALL_FRM_OK_SEND')?></div>
		<?unset($_SESSION['READY_request_'.$arParams['POST_NAME']]);?>
	
	<? else:?>
		
		<?
		// вывод ошибок
		$err = '';
		foreach ($arResult["error"] as $er) {
			if ($er == '') {
				continue;
			}
			$err .= $er . '<br />';
		}
		if (trim($err) != '') {
			echo '<div class="alert-errors">';
			echo GetMessage('CALL_REQUIRE_ERRORS');
			echo $err;
			echo '</div>';
		}
		?>
		
		<?=bitrix_sessid_post();?>
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					<label for="name"><?=GetMessage('RT_name')?> <span class="req">*</span></label>
					<br />
					<input type="text" maxlength="450"
						class="req_inpt"
						name="<?=$arParams['POST_NAME']?>[name]"
						value="<?=($arParams['_POST'][ $arParams['POST_NAME'] ]['name']) ? $arParams['_POST'][$arParams['POST_NAME']]['name'] : '';?>"
					/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="surename"><?=GetMessage('RT_fio')?> <span class="req">*</span></label>
					<br />
					<input type="text" maxlength="450"
						class="req_inpt"
						name="<?=$arParams['POST_NAME']?>[fio]"
						value="<?=($arParams['_POST'][ $arParams['POST_NAME'] ]['fio']) ? $arParams['_POST'][$arParams['POST_NAME']]['fio'] : '';?>"
					/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="country"><?=GetMessage('RT_country')?> <span class="req">*</span></label>
					<br />
					<input type="text" maxlength="450"
						class="req_inpt"
						name="<?=$arParams['POST_NAME']?>[country]"
						value="<?=($arParams['_POST'][ $arParams['POST_NAME'] ]['country']) ? $arParams['_POST'][$arParams['POST_NAME']]['country'] : '';?>"
					/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="city"><?=GetMessage('RT_city')?> <span class="req">*</span></label>
					<br />
					<input type="text" maxlength="450"
						class="req_inpt"
						name="<?=$arParams['POST_NAME']?>[city]"
						value="<?=($arParams['_POST'][ $arParams['POST_NAME'] ]['city']) ? $arParams['_POST'][$arParams['POST_NAME']]['city'] : '';?>"
					/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="mail"><?=GetMessage('RT_mail')?> <span class="req">*</span></label>
					<br />
					<input type="text" maxlength="450"
						class="req_inpt email_inpt"
						name="<?=$arParams['POST_NAME']?>[mail]"
						value="<?=($arParams['_POST'][ $arParams['POST_NAME'] ]['mail']) ? $arParams['_POST'][$arParams['POST_NAME']]['mail'] : '';?>"
					/>
				</td>
			</tr>
			
			<tr>
				<td>
					<label for="msg"><?=GetMessage('RT_msg')?> <span class="req">*</span></label><br>
					<textarea class="req_inpt"
						name="<?=$arParams['POST_NAME']?>[msg]"><?=($arParams['_POST'][ $arParams['POST_NAME'] ]['msg']) ? $arParams['_POST'][$arParams['POST_NAME']]['msg'] : '';?></textarea>
				</td>
			</tr>
			
			<tr>
				<td>
					<div class="note"><span class="req">*</span> <?=GetMessage('CALL_REQUIRE_TITLE_GO')?></div>
				</td>
			</tr>
			<tr>
				<td class="bot">
					<span class="submit button raised_btn"><?=GetMessage('CALL_FRM_GO')?></span>
				</td>
			</tr>
		</table>
		
	<?endif;?>
	
	

</div>
</div>

