$(function(){
	$('#regform select[name="regform[courses]"]').change(function(){
		var c_id = $(this).find('option:selected').val();
		$(this).parent().find('input#course_name').val( $(this).find('option:selected').html() );
		console.debug(SCHEDULE);
		$('#regform select[name="regform[date]"]').html('');
		for(var i in SCHEDULE[c_id]){
			$('#regform select[name="regform[date]"]').append('<option value="'+SCHEDULE[c_id][i]['NAME']+'">'+SCHEDULE[c_id][i]['NAME']+'</option>');
		};
		$('#regform select[name="date"]').removeAttr('disabled');
	});

	// корректность телефона
	$('#regform input[name="regform[phone]"]').keypress(function(e){
		return isNum(e.which);
	});

	// открывает форму регистрации в семинарах
	$('.training_detail > .no_form .but, .training_detail .tab_wrapper table td b a').click(function(){
		$('#regform select[name="regform[courses]"] option').removeAttr('selected');
		$('#regform select[name="regform[courses]"] option[value="'+$(this).attr('valid')+'"]').attr('selected', 'selected');
		$('#regform select[name="regform[courses]"]').change();
		ShowWin($('#regform'));
		return false;
	});

	// проверка перед отправкой
	$('#regform .but').click(function(){
		var allow = true;

		// непустота обязательных полей
		var var1 = $('#regform input[name="regform[name]"], #regform input[name="regform[mail]"], #regform input[name="regform[cnt]"], #regform input[name="regform[company]"]');
		if(isEmpty($(var1).val()) || $(var1).val() == 'Имя' || $(var1).val() == 'E-mail'){
			$(var1).addClass('fail_input');
			allow = false;
		} else{
			$(var1).removeClass('fail_input');
		}

		// корректность почты
		var var2 = $('#regform input[name="regform[mail]"]');
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