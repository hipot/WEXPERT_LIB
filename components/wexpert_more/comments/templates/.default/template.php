<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/form.php");
//echo "<pre>"; print_r($arResult); echo "</pre>";
?>
<script type="text/javascript">
	var COMMENTS_COMPONENT_COMPONENT_PATH = '<?=$componentPath?>';
</script>
<?if(!empty($arResult['errors'])):?>
<script type="text/javascript">
$(function(){
	var loc = ''+window.location;
	var r = loc.replace(/#\w+$/g, '');
	location.assign(r+'#er');
});
</script>
<?endif;?>
<?if(!empty($arResult['err'])):?>
<ul class="com_error">
	<?foreach($arResult['err'] as $er):?>
	<li><?=$er?></li>
	<?endforeach;?>
</ul>
<br clear="all" />
<br clear="all" />
<?endif;?>
<a class="anchor" name="comments">&nbsp;</a>
<div class="comments">
	<?if(count($arResult['COMMENTS'])>0 && (!$arResult['ALL_COMMENTS_IS_HIDDEN'] || $arResult['canModerate'])):?>
	<h2>Отзывы</h2>
	<br />
		<div class="itm review">
		<?function recoursiveCom($comments, $arResult, $arParams, $depth=0){ global $USER; global $APPLICATION;
			foreach($comments as $k=>$com):
			if($arParams['PRE_MODER']=='Y' && !$arResult['canModerate'] && $com['STATUS']!='P'){
				continue;
			}

			if($com['PARENT_ID']<=0){
				$depth=0;
			}
		?>
			<a class="anchor" name="com<?=$com['ID']?>">&nbsp;</a>
			<div class="tab" style="margin-left: <?=$depth*$arParams['TAB_SIZE']?>px;">
				<div class="head_line">
					<div class="ident">
						<?if($arResult['canModerate']):?>
							<a href="mailto:<?=$com['AUTHOR_EMAIL']?>"><?=$com['AUTHOR_NAME']?></a>
						<?else:
							echo $com['AUTHOR_NAME'];
						endif;?>

						<span class="date">
							<?=(isset($com['DATE_PARSE']))?$com['DATE_PARSE']['DD'].' '.strtolower(GetMessage('MONTH_'.(int)$com['DATE_PARSE']['MM'].'_S')):'';?> в <?=$com['DATE_PARSE']['HH'].':'.$com['DATE_PARSE']['MI']?>
						</span>

						<?if($arResult['canModerate']):?>
							<span class="status">
							<?if($com['STATUS']=='N'){
								echo '<span class="new">(новый)</span>';
							} elseif($com['STATUS']=='H'){
								echo '<span class="hidden">(скрытый)</span>';
							}?>
							</span>
						<?endif;?>

						<?if($arParams['USE_RATE'] == 'Y' && $arResult['canComments']): ?>
						<div class="voice" cid="<?=$com['ID']?>">
							<?if($arResult['canModerate']): ?>
							<span class="clear_rate" title="Обнулить отзывы">x</span>
							<? endif; ?>
							<span class="thank">Спасибо за ваш голос</span>
							<span class="wrong">С вашего IP-адреса уже голосовали.</span>
							Отзыв полезен?
							<span class="positive">Да</span>
							<span class="positive_num"><?=(sizeof($com['RATE']['GOOD']))?sizeof($com['RATE']['GOOD']):'';?></span>
							/ <span class="negative">Нет</span>
							<span class="negative_num"><?=(sizeof($com['RATE']['BAD']))?sizeof($com['RATE']['BAD']):'';?></span>
						</div>
						<? endif;?>
					</div>
				</div>
				<?if($com['GOOD']): ?>
				<p class="plus"><?=$com['GOOD']?></p>
				<? endif;?>
				<?if($com['BAD']): ?>
				<p class="minus"><?=$com['BAD']?></p>
				<? endif;?>
				<?if($com['TEXT']): ?>
				<div class="com_txt"><?=$com['TEXT']?></div>
				<? endif;?>
				<br clear="all" />
				<?if($arResult['canComments'] || $arResult['canModerate']):?>
				<div class="com_tools">
					<?if($arResult['canCommentComment'] && $depth<$arParams['MAX_DEPTH']):?>
					<a class="do_comment" ident="<?=$com['ID']?>" href="javascript:void(0)">Ответить</a>
					<?endif;?>
						<?if($arResult['canModerate']):?>
							<?if($com['PARENT_ID']>0 && $arParams['nPageSize']>0):?>
								<a href="<?=$APPLICATION->GetCurPage().'#com'.$com['PARENT_ID']?>">Родитель</a>
							<?endif;?>
						<a class="do_edit" ident="<?=$com['ID']?>" href="javascript:void(0)">Редактировать</a>
						<a class="do_delete" ident="<?=$com['ID']?>" href="javascript:void(0)">Удалить</a>
						<?if($arParams['PRE_MODER']=='Y'):?>
							<?if($com['STATUS']=='N' || $com['STATUS']=='H'):?>
							<a class="do_show" ident="<?=$com['ID']?>" href="javascript:void(0)">Отобразить</a>
							<?elseif($com['STATUS']=='P'):?>
							<a class="do_hide" ident="<?=$com['ID']?>" href="javascript:void(0)">Скрыть</a>
							<?endif;
						endif;
					endif;?>
				</div>
				<?endif;?>
				<?if($arParams['WRITE_COM_COM']=='Y' || $arResult['canModerate']):?>
				<div class="comment_comment">
					<div class="ed">
						<?
						$iid = 'lc'.$com['ID'];
						$id = 'leav_comment_'.$com['ID'];
						ShowForm($iid.'_ed', $id.'_ed', array('AUTHOR_NAME'=>$com['AUTHOR_NAME'], 'AUTHOR_EMAIL'=>$com['AUTHOR_EMAIL'], 'STATUS'=>$com['STATUS'], 'PARENT_ID'=>$com['PARENT_ID'], 'DATE'=>$com['DATE_FORMAT'], 'IBLOCK_ELEMENT_ID'=>$arParams['ELEMENT_ID'], 'ID'=>$com['ID'], 'TEXT'=>$com['TEXT'], 'BAD'=>$com['BAD'], 'GOOD'=>$com['GOOD'], 'USER_ID'=>$USER->GetID()), $arParams, $arResult);
						?>
					</div>
					<div class="com">
						<?
						$st = ($arParams['PRE_MODER']=='Y')?'N':'P';
						ShowForm($iid.'_com', $id.'_com', array('AUTHOR_NAME'=>$arResult['arUser']['NAME'], 'AUTHOR_EMAIL'=>$arResult['arUser']['EMAIL'], 'STATUS'=>$st, 'PARENT_ID'=>$com['ID'], 'IBLOCK_ELEMENT_ID'=>$arParams['ELEMENT_ID'], 'USER_ID'=>$USER->GetID()), $arParams);
						?>
					</div>
				</div>
				<?endif;?>
			</div>
			<?
			if(!empty($com['COMMENTS'])){
				$depth++;
				recoursiveCom($com['COMMENTS'], $arResult, $arParams, $depth);
			}
			?>
		<?endforeach; }
		recoursiveCom($arResult['COMMENTS'], $arResult, $arParams);?>
		</div>
	<?endif;?>
	<br clear="all" />
	<?=$arResult['NAV_STRING']?>
	<br clear="all" />

</div>
<?if($arParams['PRE_MODER']=='Y'):?>
<form class="show_hide_form" method="POST" style="display: none;">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="arCommentFields[ID]" value=""/>
	<input type="hidden" name="arCommentFields[IBLOCK_ELEMENT_ID]" value="<?=$arParams['ELEMENT_ID']?>"/>
	<input type="hidden" name="arCommentFields[STATUS]" value=""/>
	<input type="hidden" name="arCommentFields[SET_STATUS]" value="Y"/>
</form>
<?endif;?>

<?if($arResult['canComments']):?>
<div class="leav_com">
	<a class="anchor" name="er">&nbsp;</a>
	<h2>Оставить отзыв</h2>
	<br />
	<?if(!empty($arResult['errors'])):?>
	<ul class="com_error">
		<?foreach($arResult['errors'] as $er):?>
		<li><?=$er?></li>
		<?endforeach;?>
	</ul>
	<br clear="all" />
	<br clear="all" />
	<?endif;?>
	<?if($USER->IsAuthorized()):?>
	<style type="text/css">
		.fil_me {display: none !important;}
	</style>
	<?endif;?>
	<?
	$a_n=($_POST['arCommentFields']['AUTHOR_NAME']!='')?$_POST['arCommentFields']['AUTHOR_NAME']:$arResult['arUser']['NAME'];
	$a_m=($_POST['arCommentFields']['AUTHOR_EMAIL']!='')?$_POST['arCommentFields']['AUTHOR_EMAIL']:$arResult['arUser']['EMAIL'];
	$tx=($_POST['arCommentFields']['TEXT']!='')?$_POST['arCommentFields']['TEXT']:'';
	$bad=($_POST['arCommentFields']['BAD']!='')?$_POST['arCommentFields']['BAD']:'';
	$good=($_POST['arCommentFields']['GOOD']!='')?$_POST['arCommentFields']['GOOD']:'';
	ShowForm('lc', 'leav_comment_', array('AUTHOR_NAME'=>$a_n, 'AUTHOR_EMAIL'=>$a_m, 'IBLOCK_ELEMENT_ID'=>$arParams['ELEMENT_ID'], 'USER_ID'=>$USER->GetID(), 'TEXT'=>$tx, 'BAD'=>$bad, 'GOOD'=>$good), $arParams, $arResult);?>
</div>
<?endif;?>


