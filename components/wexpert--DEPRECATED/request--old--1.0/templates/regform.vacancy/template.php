<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="regform" id="regform_vacancy">
	<div class="inner">
		<div class="top"></div>
		<div class="mid">
			<div class="title">Отклик на вакансию &ndash; <span class="vacancy_name"></span></div>
			<div class="close"></div>
			
			<? if ($_SESSION[ "READY" . $arParams["POST_NAME"] ] == 'Y'):?>
				<div class="ready">Ваш запрос отправлен! <br /> Мы обязательно свяжемся с вами.</div>
				<script type="text/javascript">
				$(function(){
					
				});
				</script>
				<?unset($_SESSION[ "READY" . $arParams["POST_NAME"] ]);?>
			<?else:?>
			<form method="post" enctype="multipart/form-data">
				<?=bitrix_sessid_post();?>
				
				<?if (!empty($arResult['error'])) {?>
					<div class="errors">
					<?foreach ($arResult['error'] as $err) {?>
						<?=$err?><br />
					<?}?>
					</div>
				<?}?>
				
				<input type="hidden" value="" class="vacancy_id" name="<?=$arParams['POST_NAME']?>[vacancy_id]" />
				<table>
					<tr>
						<td><label>Ваше Имя</label><span class="red">*</span></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[name]" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['name'])?$arParams['_POST'][$arParams['POST_NAME']]['name']:'';?>" /></td>
					</tr>
					<tr>
						<td><label>E-mail</label><span class="red">*</span></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[mail]" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['mail'])?$arParams['_POST'][$arParams['POST_NAME']]['mail']:'';?>" /></td>
					</tr>
					<tr>
						<td><label>Телефон</label></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[phone]" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['phone'])?$arParams['_POST'][$arParams['POST_NAME']]['phone']:'';?>" /></td>
					</tr>
					<tr>
						<td><label>Немного о себе</label></td>
						<td><textarea rows="8" name="<?=$arParams['POST_NAME']?>[about_yourself]"><?=($arParams['_POST'][$arParams['POST_NAME']]['about_yourself'])?$arParams['_POST'][$arParams['POST_NAME']]['about_yourself']:'';?></textarea></td>
					</tr>
					<tr>
						<td><label>Файл с резюме</label><span class="red">*</span></td>
						<td>
							<input type="hidden" value="" class="file" name="<?=$arParams['POST_NAME']?>[file]" />
							<input type="file" name="<?=$arParams['POST_NAME']?>_file_path" class="file_path" />
						</td>
					</tr>
				</table>
				<div class="req_txt"><pan class="red">*</pan>Заполните обязательные поля</div>
				<div class="no_form">
					<div class="but"><span>Зарегистрироваться</span></div>
				</div>
			</form>
			<?endif;?>
						
			<br clear="all" />
		</div>
		<div class="bot"></div>
	</div>
</div>

