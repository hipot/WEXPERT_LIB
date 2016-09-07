<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<div class="form_wrap orders_form">
	<div class="lt"></div>
	<div class="rt"></div>
	<div class="rb"></div>
	<div class="lb"></div>
	<? if ($_REQUEST['READY'.$arParams['POST_NAME']] == 'Y'):?>
	<div class="ready">Ваш запрос отправлен! <br /> Мы обязательно свяжемся с вами.</div>
	<? else:?>
	<form class="inner" method="post" name="<?=$arParams['POST_NAME']?>">
		<?=bitrix_sessid_post();?>
		<div class="HeaderTitle">Заказ коммерческого предложения</div>

		<div class="dot0-0"><label class="req of_name" for="of_name">*</label></div>
		<input type="text" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['name'])?$arParams['_POST'][$arParams['POST_NAME']]['name']:'Имя';?>" name="<?=$arParams['POST_NAME']?>[name]" id="of_name" />

		<input type="text" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['phone'])?$arParams['_POST'][$arParams['POST_NAME']]['phone']:'Телефон';?>" name="<?=$arParams['POST_NAME']?>[phone]" />

		<div class="dot0-0"><label class="req of_email" for="of_email">*</label></div>
		<input type="text" value="<?=($arParams['_POST'][$arParams['POST_NAME']]['mail'])?$arParams['_POST'][$arParams['POST_NAME']]['mail']:'E-mail';?>" name="<?=$arParams['POST_NAME']?>[mail]" id="of_email" />

		<textarea rows="4" name="<?=$arParams['POST_NAME']?>[comment]"><?=($arParams['_POST'][$arParams['POST_NAME']]['comment'])?$arParams['_POST'][$arParams['POST_NAME']]['comment']:'Комментарий';?></textarea>
		<p class="frm_help_txt"><span class="req">*</span> Заполните обязательно</p>

		<div class="but"><span>Заказать</span></div>
		<br clear="all" />
	</form>
	<? endif;?>
</div>