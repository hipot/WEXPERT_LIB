if(CFValidators == undefined){
	var CFValidators = {};
}
CFValidators.set_error = function (th, id, msg) {
	$th = $(th);
	var $frm = $th.parents('form');
	var fdt = $frm.data('settings');
	var fls = $th.data('fails');
	fls = (fls == null || !fls)?{}:fls;
	fls[id] = true;
	$th.addClass('fail');
	$frm.find('label[for=' + $th.attr('id') + ']').addClass('fail');

	if(fdt.js_error_tooltips){
		$th.attr('title',msg);
	}
	if(fdt.js_error_list){
		var erul = $frm.find('ul.errors');
		if(!erul.length){
			$frm.prepend('<ul class="errors"></ul>');
			erul = $frm.find('ul.errors');
		}
		var li = '<li id="' + id + '">' + msg + '</li>';
		if(!erul.find('#' + id).length){
			erul.append(li);
		} else{
			erul.find('#' + id).show();
		}
	}
	$th.data('fails', fls);
};
CFValidators.unset_error = function (th, id, msg) {
	$th = $(th);
	var $frm = $th.parents('form');
	var fdt = $frm.data('settings');
	var fls = $th.data('fails');
	fls = (fls == null || !fls)?{}:fls;
	fls[id] = undefined;
	var fla = false;
	for(var a in fls){
		if(fls[a] != undefined) fla = true;
	}
	if(!fla){
		$th.removeClass('fail');
		$frm.find('label[for=' + $th.attr('id') + ']').removeClass('fail');
	}
	if(fdt.js_error_tooltips){
		$th.removeAttr('title');
	}
	if(fdt.js_error_list){
		var erul = $frm.find('ul.errors');
		if(erul.length){
			erul.find('"#' + id + '"').hide();
		}
	}
	$th.data('fails', fls);
};


CFValidators.filled = function (msg, id) {
	var me = this;
	$(this).parents('form').submit(function () {
		if($.trim($(me).val()).length <= 0){
			CFValidators.set_error(me, id, msg);
			return false;
		} else{
			CFValidators.unset_error(me, id, msg);
		}
	});
	return true;
};
CFValidators.mail = function (msg, id) {
	var me = this;
	$(this).parents('form').submit(function () {
		if(!/^[=_.0-9a-z+~-]+@(([-0-9a-z_]+\.)+)([a-z]{2,10})$/i.test($(me).val()) && $.trim($(me).val()).length > 0){
			CFValidators.set_error(me, id, msg);
			return false;
		} else{
			CFValidators.unset_error(me, id, msg);
		}
	});
};
CFValidators.phone = function (msg, id) {
	$(this).keypress(function (e) {
		var c = e.which;
		return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8 || c == 32 || c == 43 || c == 45 || c == 41 || c == 40;
	});
};
CFValidators.stringsize = function (msg, id, sz) {
	$(this).keypress(function (e) {
		if($(this).val().length >= sz[0])
			return false;
	});
};
CFValidators.number = function (msg, id) {
	$(this).keypress(function (e) {
		var c = e.which;
		return (c >= 48 && c <= 57) || (c >= 17 && c <= 20) || c == 27 || c == 0 || c == 127 || c == 8;
	});
};
