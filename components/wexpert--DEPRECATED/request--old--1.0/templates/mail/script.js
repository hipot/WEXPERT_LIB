$(function(){
	/**
	 * @see шаблон сайта/js/jquery.toggleval.js
	 */
	$('.orders_form input, .orders_form textarea').each(function(){
		$(this).toggleVal({hideLabels: true});
	});

	/* remove all def values */
	$(".orders_form form").submit(function() {
		$(this).find(".toggleval").each(function() {
			if($(this).val() == $(this).data("defText") && $(this).attr('id')!=='sessid') {
				$(this).val("");
			}
		});
	});

	// корректность телефона
	$('.orders_form input[name="commercial_order[phone]"]').keypress(function(e){
		return isNum(e.which);
	});

	// непустота почты и имени
	$('.orders_form input[name="commercial_order[name]"], .orders_form input[name="commercial_order[mail]"]').mouseout(function(){
		if(isEmpty($(this).val()) || $(this).val() == 'Имя' || $(this).val() == 'E-mail'){
			$(this).addClass('fail_input');
		} else{
			$(this).removeClass('fail_input');
		}
	});

	// корректность почты
	$('.orders_form input[name="commercial_order[mail]"]').mouseout(function(){
		if(!isMail($(this).val())){
			$(this).addClass('fail_input');
		} else{
			$(this).removeClass('fail_input');
		}
	});

	// проверка перед отправкой
	$('.orders_form .but').click(function(){
		$('.orders_form input[name="commercial_order[name]"], .orders_form input[name="commercial_order[mail]"]').mouseout();
		var allow = true;
		$($(this).parents('form').find('input')).each(function(){
			if($(this).hasClass('fail_input')){
				allow = false;
			}
		});

		if(allow)
			$(this).parents('form').submit();
	});

});

function isNum(cCode){
    if((cCode >= 48 && cCode <= 57) || (cCode>=17 && cCode<=20) || cCode == 27 || cCode == 0 || cCode == 127 || cCode == 8)
    	return true;
    else
    	return false;
}

function isMail(str){
		return /^[0-9a-z_]+@[0-9a-z_^.]+.[a-z]+$/i.test(str);
}

function isEmpty(str){
	if($.trim(str).length > 0)
		return false;
	else
		return true;
}