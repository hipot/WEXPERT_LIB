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

	// проверка перед отправкой
	$('.orders_form .but').click(function(){
		$('.orders_form input[name="commercial_order[name]"], .orders_form input[name="commercial_order[mail]"]').mouseout();
		var allow = true;

		// непустота почты и имени
		var var1 = $('.orders_form input[name="commercial_order[name]"], .orders_form input[name="commercial_order[mail]"]');
		if(isEmpty($(var1).val()) || $(var1).val() == 'Имя' || $(var1).val() == 'E-mail'){
			$(var1).addClass('fail_input');
			allow = false;
		} else{
			$(var1).removeClass('fail_input');			
		}

		// корректность почты
		var var2 = $('.orders_form input[name="commercial_order[mail]"]');
		if(!isMail($(var2).val())){
			$(var2).addClass('fail_input');
			allow = false;
		} else{
			$(var2).removeClass('fail_input');			
		}

		if(allow)
			$(this).parents('form').submit();
	});

});
