<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
global $FORM;			// оглабаливаем $FORM
$FORM->Start();			// НАЧАЛО формы
$FORM->ShowErrors();	// ошибки заполнения
if($FORM->Ready){?>		// если форма готова, т.е. обработана

	<div class="ready">Ваш запрос отправлен. Спасибо.</div>

<?} else{?>
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<?=$FORM->Label('name')// подпись к полю с именем "name"?>
		</td>
		<td>
			<?=$FORM->Field('name')// поле с именем "name"?>
		</td>
	</tr>
	<tr>
		<td>
			<?=$FORM->Label('country')?>
		</td>
		<td>
			<?=$FORM->Field('country')?>
		</td>
	</tr>
	<tr>
		<td>
			<?=$FORM->Label('img')?>
		</td>
		<td>
			<?=$FORM->Field('img')?>
		</td>
	</tr>
	<tr>
		<td>
			<?=$FORM->Label('mail')?>
		</td>
		<td>
			<?=$FORM->Field('mail')?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<?=$FORM->Field('send')// кнопка?>
		</td>
	</tr>
</table>
<?}

$FORM->End();			// КОНЕЦ формы
?>
