$(function(){
	$('#regform_vacancy .but').click(function(){
		var allow = true;
		var form = $(this).parents('form');
		
		// заполненность файла
		$('.file', form).val('');
		if ( isEmpty($('.file_path', form).val()) ) {
			allow = false;
			$('.file_path', form).addClass('fail_input');
		} else {
			$('.file', form).val('Y');
			$('.file_path', form).removeClass('fail_input');
		}
		
		// не пустота почты и имени
		var var1 = $('input[name="regform_vacancy[name]"], input[name="regform_vacancy[mail]"]', form);
		if (isEmpty($(var1).val())) {
			$(var1).addClass('fail_input');
			allow = false;
		} else {
			$(var1).removeClass('fail_input');			
		}

		// корректность почты
		var var2 = $('input[name="regform_vacancy[mail]"]', form);
		if (! isMail($(var2).val()) ) {
			$(var2).addClass('fail_input');
			allow = false;
		} else{
			$(var2).removeClass('fail_input');			
		}
		
		if (allow) {
			form.submit();
		}
	});
});