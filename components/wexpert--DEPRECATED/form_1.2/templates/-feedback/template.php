<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<?
global $FORM;			// оглабаливаем $FORM
$FORM->Start();			// НАЧАЛО формы
$FORM->ShowErrors();	// ошибки заполнения
if($FORM->Ready){		// если форма готова, т.е. обработана?>

	<div class="ready">Ваш запрос отправлен. Спасибо.</div>

<?} else{?>
	<table width="100%" class="feedback" cellspacing="3" cellpadding="3">
		<tbody>
		<tr>
			<td class="title_cell" width="30%">
				<?=$FORM->Label('name')?>
			</td>
			<td class="field_cell">
				<?=$FORM->Field('name')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Label('email')?>
			<td class="field_cell">
				<?=$FORM->Field('email')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Label('msg')?>
			<td class="field_cell">
				<?=$FORM->Field('msg')?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<?=$FORM->Field('submit')?>
			</td>
		</tr>
		</tbody>
	</table>
<?}

$FORM->End();			// КОНЕЦ формы
?>
