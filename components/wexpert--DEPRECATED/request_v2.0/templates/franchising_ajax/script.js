$(function(){	
	// корректность телефона
	$('#franchising_form .phone').keypress(function(e){		
		return isPhone(e.which);
	});
});