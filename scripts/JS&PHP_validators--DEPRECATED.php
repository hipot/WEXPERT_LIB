<?
$str = 'DSdfgf_.d-fsd@rg.erg';
$pattern = '#^[\S_]+@[\S-]+\.[\S]+$#';
if(preg_match($pattern, $str, $ar)){
	echo 'yes';
};
?>
<script>
$(function(){
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
//-----------------------------------------------------
function isMailPhone(){
	if(/@/.test(String.fromCharCode(cCode)))
		return /^[0-9a-z_]+@[0-9a-z_^.]+.[a-z]+$/i.test(String.fromCharCode(cCode));
	else
		return /[0-9@\.]/.test(String.fromCharCode(cCode));
}

function isNum(cCode){
    return /[0-9@\.]/.test(String.fromCharCode(cCode));
}
//------------------------------------------------------
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
</script>