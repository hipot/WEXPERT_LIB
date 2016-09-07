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

	$('.comments .voice .clear_rate').click(function(){
		setRate(
			$(this),
			{
				'comment_id': $(this).parents('.voice').attr('cid'),
				'clear': 'Y'
			},
			''
		);
	});

	$('.comments .voice .positive, .comments .voice .negative').click(function(){
		if($(this).hasClass('positive')){
			var rate = '1';
			var r = 'positive';
		} else{
			var rate = '0';
			var r = 'negative';
		}
		setRate(
			$(this),
			{
				'comment_id': $(this).parents('.voice').attr('cid'),
				'rate': rate
			},
			r
		);
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
			$(name).addClass('errortext');
		} else{
			$(name).removeClass('errortext');
		}

		if(isEmpty($(mail).val())){
			allow = false;
			$(mail).addClass('errortext');
		} else{
			$(mail).removeClass('errortext');
		}

		if(!isMail($(mail).val())){
			allow = false;
			$(mail).addClass('errortext');
		} else{
			$(mail).removeClass('errortext');
		}

		if($(lhetxt).length>0){
			if(isEmpty($(lhetxt).val())){
				allow = false;
				$(me).parents('form').find('.bxlhe-frame').parent().addClass('errortext');
			} else{
				$(me).parents('form').find('.bxlhe-frame').parent().removeClass('errortext');
			}
		}
		if($(captcha).length>0){
			if(isEmpty($(captcha).val())){
				allow = false;
				$(captcha).addClass('errortext');
			} else{
				$(captcha).removeClass('errortext');
			}
		}
		if($(txt).length>0){
			if(isEmpty($(txt).val())){
				allow = false;
				$(txt).addClass('errortext');
			} else{
				$(txt).removeClass('errortext');
			}
		}


		if(allow){
			return true;
		} else{
			return false;
		}
	});
});

function setRate(th, Data, rate){
	$.ajax({
		type: "POST",
		url: COMMENTS_COMPONENT_COMPONENT_PATH+'/rate.php',
		data: Data,
		dataType: "json",
		jsonp: "jsoncallback",
		error: function(er){
			alert(er.responseText);
		},
		success: function(data){
			if(data.error){
				alert(data.error);
				return false;
			}
			if(data.cleared){
				clearRate(th);
				return false;
			}
			if(data.warny){
				showWarn(th);
			} else if(data.num > 0){
				showThank(th);
				$(th).siblings('.'+rate+'_num').html(data.num);
			}
		}
	});
}

function showWarn(th){
	$(th).parents('.voice').find('.wrong').fadeIn('fast');
	setTimeout(function(){
		$(th).parents('.voice').find('.wrong').fadeOut('fast');
	}, 2000);
}

function showThank(th){
	$(th).parents('.voice').find('.thank').fadeIn('fast');
	setTimeout(function(){
		$(th).parents('.voice').find('.thank').fadeOut('fast');
	}, 2000);
}

function clearRate(th){
	$(th).parents('.voice').find('.positive_num, .negative_num').html('');
}

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
