<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<div class="regform" id="regform">
	<div class="inner">
		<div class="top"></div>
		<div class="mid">
			<div class="title">Регистрация</div>
			<div class="close"></div>
			<? if(!empty($arResult['error'])):?>
			<ul style="color:red;">
				<?foreach ($arResult['error'] as $er):?>
				<li><?=$er?></li>
				<?endforeach;?>
			</ul>
			<?endif;?>
			<? if ($_REQUEST['READY'.$arParams['POST_NAME']] == 'Y'):?>
			<div class="ready">Ваш запрос отправлен! <br /> Мы обязательно свяжемся с вами.</div>
			<? else:?>
			<form method="post" name="<?=$arParams['POST_NAME']?>">
				<table>
					<tr>
						<td><label>Мероприятие</label></td>
						<td>
							<select name="<?=$arParams['POST_NAME']?>[courses]">
								<? foreach($arParams['EVENTS'] as $id=>$e):?>
									<option value="<?=$e['ID']?>" <?=($arParams['_POST'][$arParams['POST_NAME']]['course'] == $e['ID'])?'selected':'';?>><?=$e['NAME']?></option>
								<? endforeach;?>
							</select>
							<input type="hidden" id="course_name" name="course_name" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['course_name']?>"/>
						</td>
					</tr>
					<tr>
						<td><label>Дата проведения</label></td>
						<td>
							<select name="<?=$arParams['POST_NAME']?>[date]" disabled>
								<? if($arParams['_POST'][$arParams['POST_NAME']]['date']):?>
								<option value="<?=$arParams['_POST'][$arParams['POST_NAME']]['date']?>"><?=$arParams['_POST'][$arParams['POST_NAME']]['date']?></option>
								<? endif;?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="empty"></td>
						<td></td>
					</tr>
					<tr>
						<td><label>Количество участников</label><span class="red">*</span></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[cnt]" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['cnt']?>" /></td>
					</tr>
					<tr>
						<td><label>Фамилия и Имя</label><span class="red">*</span></td>
						<td><input type="text"name="<?=$arParams['POST_NAME']?>[name]"  value="<?=$arParams['_POST'][$arParams['POST_NAME']]['name']?>" /></td>
					</tr>
					<tr>
						<td><label>E-mail</label><span class="red">*</span></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[mail]" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['mail']?>" /></td>
					</tr>
					<tr>
						<td><label>Телефон</label></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[phone]" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['phone']?>" /></td>
					</tr>
					<tr>
						<td><label>Компания</label><span class="red">*</span></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[company]" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['company']?>" /></td>
					</tr>
					<tr>
						<td><label>Должность</label></td>
						<td><input type="text" name="<?=$arParams['POST_NAME']?>[position]" value="<?=$arParams['_POST'][$arParams['POST_NAME']]['position']?>" /></td>
					</tr>
					<tr>
						<td><label>Комментарий</label></td>
						<td><textarea rows="8" name="<?=$arParams['POST_NAME']?>[comment]"><?=$arParams['_POST'][$arParams['POST_NAME']]['comment']?></textarea></td>
					</tr>
				</table>
				<div class="req_txt"><pan class="red">*</pan>Заполните обязательные поля</div>
				<div class="no_form">
					<div class="but"><span>Зарегистрироваться</span></div>
				</div>

			</form>
			<? endif;?>
			<br clear="all" />
		</div>
		<div class="bot"></div>
	</div>
</div>

