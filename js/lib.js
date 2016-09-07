/**
 * Обновлено:
 * isNum - добавлены коды цифр с NumPad`а + TAB
 * isPhone - добавлены коды знаков "-", "+" основной и цифровой клавиатур
 *
 * weXpert js lib
 * @version 1.2 2016
 */

(function ($) {
	/**
	 * Плагин для сокрытия емейлов
	 * @memberOf JQuery
	 */
	$.fn.mailme = function () {
		var at = / AT /;
		var dot = / DOT /g;

		return this.each(function () {
			var text = $(this).text(),
				span_class = $(this).attr('class'),
				addr = text.replace(at, '@').replace(dot, '.'),
				rgx = new RegExp(text),
				html = $(this).html().replace(rgx, addr),
				link = $('<a href="mailto:' + addr + '">' + html + '</a>');
			link.addClass(span_class);
			$(this).after(link);
			$(this).remove();
		});
	};

	/**
	 * сериализует форму в объект JSON
	 * @usage $('form').serializeJSON();
	 */
	$.fn.serializeJSON = function () {
		var json = {};
		jQuery.map($(this).serializeArray(), function (n, i) {
			json[n['name']] = n['value'];
		});
		return json;
	};

	/**
	 * Проверяет код нажатой клавиши для полей типа "телефон"
	 * Разрешены символы: 0-9 + - \s ( )
	 * Разрешены комбинации: Backspace, ctrl + v, ctrl + c, ctrl + r
	 * 
	 * @returns {Boolean}
	 */
	$.fn.checkPhone = function () {
		return this.each(function () {
			$(this).unbind().keydown(function (e) {
				if ((e.ctrlKey == true && (e.keyCode != 67 || e.keyCode != 86 || e.keyCode != 82)) || e.key == 'Backspace') {
					return true; // пускаем ctrl + ( v c r )
				}
				if (e.key.search(/[^0-9\(\)\+\-\s]/i) != -1) {
					return false;
				}
			})
		}).keyup(function (e) {
			$(this).val($(this).val().replace(/[^0-9\(\)\+\-\s]+/gi, ''));
		});
	};

})(jQuery);


/**
 * Закрывает открытое окно и убирает овелей
 * @param {obj} th Объект jQuery который необходима закрыть
 */
function HideWin(th, speed) {
	if (speed == undefined) {
		$(th).hide();
		$('#overlay').hide();
	} else {
		$(th).animate({'opacity': '0'}, speed, function () {
			$('#overlay').hide();
			$(this).hide();
		});
	}
}

/**
 * Открывает открытое окно и убирает овелей
 * @param {obj} th Объект jQuery который необходима закрыть
 */
function ShowWin(th, speed) {
	//$("body").prepend('<div id="overlay"></div>');
	if (speed == undefined) {
		$("#overlay").center({'resize': true});
		$('#overlay').show();
		$(th).center().show();
	} else {
		$("#overlay").center({'resize': true});
		$('#overlay').show(speed, function () {
			$(th).css({'opacity': '0', 'display': 'block'}).center().animate({'opacity': '1'}, speed);
		});
	}
}

/**
 * отображает ошибку заполненности формы
 * @param {jQuery} layer родитель-форма, в которой после заголовка .headess нужно вставить ошибку
 * @param {String} errorHtml Ошибка в виде html
 */
function ShowFillFormErrorMess(layer, errorHtml) {
	if ($.trim(errorHtml) == '') {
		return;
	}

	var html = '<div class="alert-errors">';
	html += errorHtml;
	html += '</div>';
	$(html).insertAfter($('.headess', layer));
}

/**
 * убирает ошибку заполненности формы
 * @param {jQuery} layer родитель-форма, в которой после заголовка .HeaderTitle нужно вставить ошибку
 */
function ClearFillFormErrorMess(layer) {
	$('.alert-errors', layer).remove();
}


/**
 * Проверяет строку на пустоту
 * @param str строка для проверки
 * @returns {Boolean}
 */
function isEmpty(str) {
	if ($.trim(str).length > 0) {
		return false;
	} else {
		return true;
	}
}

/**
 * Проверяет код нажатой клавиши, и возвращает true, если это цифра
 * @param int cCode код клавиши
 * @returns {Boolean}
 */
function isNum(cCode) {
	if ((cCode >= 48 && cCode <= 57) || (cCode >= 96 && cCode <= 105) || (cCode >= 17 && cCode <= 20) || cCode == 27 || cCode == 0 || cCode == 127 || cCode == 8 || cCode == 9) {
		return true;
	} else {
		return false;
	}
}
/**
 * Проверяет код нажатой клавиши для полей типа "телефон"
 * @param int cCode код клавиши
 * @returns {Boolean}
 *
 * FIXME перестала работать
 */
function isPhone(cCode) {
	// позволяю пробел, скобки () и знак + - (и комбинацию "вставить" - нельзя)
	if (cCode == 32 || cCode == 40 || cCode == 41 || cCode == 43 || cCode == 45 || cCode == 107 || cCode == 109 || cCode == 189 || cCode == 187) {
		return true;
	} else {
		return isNum(cCode);
	}
}

/**
 * Проверяет емайл по регулярке (строго, но не жадно!)
 * @param {String} str строка для проверки
 * @returns {Boolen}
 * @version 1.0
 */
function isMail(str) {
	return /^[=_.0-9a-z+~-]+@(([-0-9a-z_]+\.)+)([a-z]{2,10})$/i.test(str);
}

/**
 * получить данные по урлу, передав ему параметры и выполнив функцию при получении данных
 * @param string url
 * @param object params
 * @param callback success function(data, textStatus, jqXHR){}
 */
function getResultFromUrl(url, params, success) {
	$.ajax({
		async: true,
		cache: false,
		data: params,
		dataType: 'html',
		timeout: 8000,
		type: 'POST',
		url: url,
		error: function (jqXHR, textStatus, errorThrown) {
		},
		success: success
	});
}

/**
 * Analog PHP htmlspecialchars
 * @param text
 * @returns
 * @see http://stackoverflow.com/questions/1787322/htmlspecialchars-equivalent-in-javascript
 */
function escapeHtml(text) {
	var map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return text.replace(/[&<>"']/g, function (m) {
		return map[m];
	});
}
