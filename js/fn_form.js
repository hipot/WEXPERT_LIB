$(function(){
	setTimeout("HideWin('#identifier');", 3000);

	// обработка баннера
	$(".main_visio .banner").autolist({link:false});

	// позиционирование оверлея и формы
	$("#overlay").center({'resize':true});
	$(".regform").center();

	// центровка попапа и ресайз оверлея при ресайзе окна
	$(window).resize(function(){
		$(".regform").center();
		$("#overlay:visible").css("width", $('body').width());
	});

	// закрывальщик попапа и оверлея
	$('.regform .close').click(function(){
		HideWin($(this).parents('.regform'));
	});

	// закрытие попапа покнопке ESC
	$(window).keyup(function(e){
		if(e.keyCode == 27){
			HideWin($('.regform'));
		}
	});

	// телефон
	$('#_point_form form input.phone').keypress(function(e){
		return isPhone(e.which);
	});

	// валидация полей
	$('.btns .submit').click(function(){
		var fill = $('#_point_form form input.fill');
		var mail = $('#_point_form form input.mail');
		var phone = $('#_point_form form input.phone');
		var allow = true;

		$(fill).each(function(){
			if(isEmpty($(this).val())){
				$(this).addClass('fail');
				allow = false;
			} else{
				$(this).removeClass('fail');
			}
		});

		$(mail).each(function(){
			if(!isMail($(this).val())){
				$(this).addClass('fail');
				allow = false;
			} else{
				$(this).removeClass('fail');
			}
		});

		$(phone).each(function(){
			if(isPhone($(this).val())){
				$(this).addClass('fail');
				allow = false;
			} else{
				$(this).removeClass('fail');
			}
		});

		if(!allow)
			return false;
	});
});

/**
 * Закрывает открытое окно и убирает овелей
 * @param {obj} th Объект jQuery который необходима закрыть
 * @param {int} speed Скорость
 */
function HideWin(th, speed){
	if(speed == undefined){
		$(th).hide();
		$('#overlay').hide();
	} else {
		$(th).animate({'opacity':'0'}, speed, function(){ $('#overlay').hide(); $(this).hide(); });
	}

}

/**
 * Открывает открытое окно и убирает овелей
 * @param {obj} th Объект jQuery который необходима закрыть
 * @param {int} speed Скорость
 */
function ShowWin(th, speed) {
	if(speed == undefined){
		$("#overlay").center({'resize':true});
		$('#overlay').show();
		$(th).center().show();
	} else {
		$("#overlay").center({'resize':true});
		$('#overlay').show(speed, function(){
			$(th).css({'opacity':'0', 'display':'block'}).center().animate({'opacity':'1'}, speed);
		});
	}

}

/**
 * Проверка на ввод цифры (с учетом нужных кнопок)
 * @param {int} c  ASCI код символа (e.which, e.keyCode)
 * @returns {Boolean}
 */
function isNum(c) {
    return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8;
}

/**
 * Проверяет ввод цифер, +-(), и пробел.
 * @param c код нажатой кнопки
 * @returns {bool}
 */
function isPhone(c){
		return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8 || c == 32 || c == 43 || c == 45 || c == 41 || c == 40;
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
	return $.trim(str).length <= 0;
}

/**
 * Случайное целое между min и max
 * @param min минимальное
 * @param max максимальное
 * @returns {int}
 */
function getRandomInt(min, max)
{
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Случайное число между min и max
 * @param min минимальное
 * @param max максимальное
 * @returns {float}
 */
function getRandomArbitary(min, max)
{
  return Math.random() * (max - min) + min;
}
