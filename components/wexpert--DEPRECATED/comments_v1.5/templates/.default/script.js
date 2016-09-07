$(function(){
	$('.do_delete').click(function(){
		if(confirm('Вы уверены что хотите удалить выделенный элемент?')){
			$('.leav_com form.lc input').val('');
			$('.leav_com form.lc input[name="arCommentFields[DEL]"]').val('Y');
			$('.leav_com form.lc input[name="arCommentFields[ID]"]').val($(this).attr('ident'));
			$('.leav_com form.lc').submit();
		} else{
			return false;
		}
	});

	$('.do_edit').click(function(){
		if($(this).parents('.tab').siblings().find('.comment_comment .ed:visible').length > 0){
			$('.comment_comment .ed').slideUp();
		}
		if($(this).parents('.tab').find('.comment_comment .com:visible').length > 0){
			$(this).parents('.tab').find('.comment_comment .com').slideUp();
		} else{
			$(this).parents('.tab').find('.comment_comment .com').hide();
		}
		$(this).parents('.tab').find('.comment_comment .ed').slideToggle();
	});

	$('.do_comment').click(function(){
		if($(this).parents('.tab').siblings().find('.comment_comment .com:visible').length > 0){
			$('.comment_comment .com').slideUp();
		}
		if($(this).parents('.tab').find('.comment_comment .ed:visible').length > 0){
			$(this).parents('.tab').find('.comment_comment .ed').slideUp();
		} else{
			$(this).parents('.tab').find('.comment_comment .ed').hide();
		}
		$(this).parents('.tab').find('.comment_comment .com').slideToggle();
	});

	$('.do_hide').click(function(){
		var mf = $('.show_hide_form');
		$(mf).find('input[name="arCommentFields[ID]"]').val($(this).attr('ident'));
		$(mf).find('input[name="arCommentFields[STATUS]"]').val('H');
		$(mf).submit();
	});

	$('.do_show').click(function(){
		var mf = $('.show_hide_form');
		$(mf).find('input[name="arCommentFields[ID]"]').val($(this).attr('ident'));
		$(mf).find('input[name="arCommentFields[STATUS]"]').val('P');
		$(mf).submit();
	});

	$('.submit input[type="submit"]').click(function(){
		var me = $(this);
		var allow = true;
		var txt = $(this).parents('form').find('#TEXT');
		var lhetxt = $(this).parents('form').find('input[name="arCommentFields[TEXT]"]');
		var name = $(this).parents('form').find('input[name="arCommentFields[AUTHOR_NAME]"]');
		var mail = $(this).parents('form').find('input[name="arCommentFields[AUTHOR_EMAIL]"]');
		var captcha = $(this).parents('form').find('input[name="captcha_word"]');

		if(isEmpty($(name).val())){
			allow = false;
			$(name).addClass('werror');
		} else{
			$(name).removeClass('werror');
		}

		if(isEmpty($(mail).val())){
			allow = false;
			$(mail).addClass('werror');
		} else{
			$(mail).removeClass('werror');
		}

		if(!isMail($(mail).val())){
			allow = false;
			$(mail).addClass('werror');
		} else{
			$(mail).removeClass('werror');
		}

		if($(lhetxt).length>0){
			if(isEmpty($(lhetxt).val())){
				allow = false;
				$(me).parents('form').find('.bxlhe-frame').parent().addClass('werror');
			} else{
				$(me).parents('form').find('.bxlhe-frame').parent().removeClass('werror');
			}
		}
		if($(captcha).length>0){
			if(isEmpty($(captcha).val())){
				allow = false;
				$(captcha).addClass('werror');
			} else{
				$(captcha).removeClass('werror');
			}
		}
		if($(txt).length>0){
			if(isEmpty($(txt).val())){
				allow = false;
				$(txt).addClass('werror');
			} else{
				$(txt).removeClass('werror');
			}
		}


		if(allow){
			return true;
		} else{
			return false;
		}
	});
});


/**
 * Проверка на корректность E-mail
 * @param str Строка для проверки
 * @returns {Boolean}
 */
function isMail(str) {
	return /^[=_.0-9a-z+~-]+@(([-0-9a-z_]+\.)+)([a-z]{2,10})$/i.test(str);
}

/**
 * Проверка на пустоту
 * @param str Строка для проверкм
 * @returns {Boolean}
 */
function isEmpty(str) {
	if($.trim(str).length > 0)
		return false;
	else
		return true;
}