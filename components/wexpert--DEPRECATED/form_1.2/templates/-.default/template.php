<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<?
global $FORM;			// оглабаливаем $FORM
$FORM->Start();			// НАЧАЛО формы
$FORM->ShowErrors();	// ошибки заполнения
if($FORM->Ready){		// если форма готова, т.е. обработана?>

	<div class="ready">Ваш запрос отправлен. Спасибо.</div>

<?} else{?>
<form method="post" name="adminForm" id="adminForm">

	<table width="100%" class="os_table" cellspacing="3" cellpadding="3">
		<tbody>
		<tr>
			<td class="title_cell" width="30%">
				<?=$FORM->Name('first_name')?>
			</td>
			<td class="field_cell">
				<?=$FORM->Field('first_name')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('last_name')?>
			<td class="field_cell">
				<?=$FORM->Field('last_name')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('organization')?>
			<td class="field_cell">
				<?=$FORM->Field('organization')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('phone')?>
			<td class="field_cell">
				<?=$FORM->Field('phone')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('fax')?>
			<td class="field_cell">
				<?=$FORM->Field('fax')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('email')?>
			<td class="field_cell">
				<?=$FORM->Field('email')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('eb_posit')?>
			<td>
				<?=$FORM->Field('eb_posit')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('eb_sphere')?>
			<td>
				<?=$FORM->Field('eb_sphere')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('eb_programs')?>
			<td>
				<?=$FORM->Field('eb_programs')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('eb_variant')?>
			<td>
				<?=$FORM->Field('eb_variant')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('eb_city')?>
			<td>
				<?=$FORM->Field('eb_city')?>
			</td>
		</tr>
		<tr>
			<td class="title_cell">
				<?=$FORM->Name('comment')?>
			<td>
				<?=$FORM->Field('comment')?>
			</td>
		</tr>

		<tr>
			<td colspan="2" align="left">
				<?=$FORM->Field('btnSubmit')?>
			</td>
		</tr>
		</tbody>
	</table>
</form>

<?}

$FORM->End();			// КОНЕЦ формы
?>
