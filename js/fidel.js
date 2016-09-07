(function($) {

	//region mousewheel-event
	/**
	 $(op).mousewheel(function(e, d, dx, dy){
			//if($.browser.mozilla){
			//d = (e.originalEvent.detail<0);
			//} else{
			//d = (dy>0);
			//}
			d = (dy>0);
			if(d){
				console.log('scroll up');
			} else{
				console.log('scroll down');
			}
			return false;
		});
	 */
	var types = ['DOMMouseScroll', 'mousewheel'];

	if ($.event.fixHooks) {
		for ( var i=types.length; i; ) {
			$.event.fixHooks[ types[--i] ] = $.event.mouseHooks;
		}
	}

	$.event.special.mousewheel = {
		setup: function() {
			if ( this.addEventListener ) {
				for ( var i=types.length; i; ) {
					this.addEventListener( types[--i], handler, false );
				}
			} else {
				this.onmousewheel = handler;
			}
		},

		teardown: function() {
			if ( this.removeEventListener ) {
				for ( var i=types.length; i; ) {
					this.removeEventListener( types[--i], handler, false );
				}
			} else {
				this.onmousewheel = null;
			}
		}
	};

	$.fn.extend({
		mousewheel: function(fn) {
			return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
		},

		unmousewheel: function(fn) {
			return this.unbind("mousewheel", fn);
		}
	});


	function handler(event) {
		var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0;
		event = $.event.fix(orgEvent);
		event.type = "mousewheel";

		// Old school scrollwheel delta
		if ( event.wheelDelta ) { delta = event.wheelDelta/120; }
		if ( event.detail     ) { delta = -event.detail/3; }

		// New school multidimensional scroll (touchpads) deltas
		deltaY = delta;

		// Gecko
		if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
			deltaY = 0;
			deltaX = -1*delta;
		}


		// Webkit
		var userAgent = navigator.userAgent.toLowerCase();

		var wheelDeltaScaleFactor = 1;

		if (orgEvent.wheelDeltaY !== undefined) {
			deltaY = orgEvent.wheelDeltaY / 120 / wheelDeltaScaleFactor;
		}
		if (orgEvent.wheelDeltaX !== undefined) {
			deltaX = -1*orgEvent.wheelDeltaX / 120 / wheelDeltaScaleFactor;
		}

		// Add event and delta to the front of the arguments
		args.unshift(event, delta, deltaX, deltaY);

		return ($.event.dispatch || $.event.handle).apply(this, args);
	}
	//endregion
})(jQuery);

$(function(){
	$('.fidel').fidel();
});

/**
 * Reloader
 * Перезагружает выбранные блоки при помощи аякс, нужным блокам обязательно указывать id, и в селектор передавать его же.
 * @param o = {
 * 		time: 500 // время в миллисекундах через которое происходит перезагрузка
 * 		css: {'outline': '1px dashed #ccc'} // стиль которым выделяется перегружаемый элемент во время обновления
 * }
 */
jQuery.fn.reloader = function(o){
	if(!$(this).length){
		return false;
	}
	var iam = $(this);
	var s = iam.selector;
	var o = jQuery.extend({
		time:500,
		css: {'outline': '1px dashed #ccc'}
	},o);
	var op = $('<div class="_stop_" style="position:fixed;bottom:51px;left:2px;z-index:100000010;width:13px;height:13px;background:#5C99D6;border:1px solid #345073; border-radius:6px;cursor:pointer;"></div>');
	var me_stopped = false;
	$('body').append(op);
	var ms = getCookie('me_stopped');
	if(ms == 'y'){
		me_stopped = true;
		$('._stop_').data('checked', 'n').css('background', '#3DF5F5');
	}
	$('._stop_').click(function(){
		if($(this).data('checked') == 'n'){
			me_stopped = false;
			setCookie('me_stopped', 'n');
			$(this).data('checked', 'y').css('background', '#5C99D6');
		} else{
			me_stopped = true;
			setCookie('me_stopped', 'y');
			$(this).data('checked', 'n').css('background', '#3DF5F5');
		}
	});

	function reloadme(){
		if(!me_stopped){
			$.ajax({
				type: "GET",
				url: location.href,
				data: {},
				cache: false,
				beforeSend: function(){
					$(s).css(o.css);
				},
				complete: function(){
					$(s).css('outline', '0');
				},
				error: function(){
					alert('Релоадер сломался! Дайте в рог разработчику.');
				},
				success: function(html){
					removeme(html);
				}
			});
		} else{
			removeme('');
		}
	}

	function ReloadCSSLink(item) {
		var value = item.getAttribute('href');
		var cutI = value.lastIndexOf('?');
		if (cutI != -1)
			value = value.substring(0, cutI);
		item.setAttribute('href', value + '?t=' + new Date().valueOf());
		return this;
	}

	function removeme(html){
		setTimeout(function(){
			if(!me_stopped){
				$(s).each(function(){
					var ar = $(html);
					var th = $(this);
					var nevv = false;
					var x = false;

					if($('a[rel="'+th.attr('id')+'"]').length){
						x = $('a[rel="'+th.attr('id')+'"]');
					} else{
						x = $('<a rel="'+th.attr('id')+'"></a>');
						th.before(x);
					}
					if(th[0] && th[0].nodeName == 'LINK'){
						for(i=0;i<=ar.length;i++){
							if(ar[i] && ar[i].nodeName == 'LINK' && ar[i].href == th[0].href.split('?')[0]){
								setTimeout(function(){ReloadCSSLink(th[0]);},0);
								break;
							}
						}
					} else{
						nevv = ar.find('#'+th.attr('id'));
						var ob = x.next();
						if(nevv && ob.attr('id') == nevv.attr('id')){
							x.after(nevv);
							ob.remove();
						}
					}
				});
			}
			reloadme();
		}, o.time);
	}

	reloadme();
};

/**
 * Простейший релоадер который тупо перезагружает страницу с интервалом
 *
 **/
jQuery.fn.reloader_simple = function(o){
	var op = $('<input type="checkbox" class="_stop_" style="position:fixed;bottom:45px;left:0px;z-index:100000010;"/>');
	var me_stopped = false;
	jQuery('body').append(op);
	var ms = getCookie('me_stopped');
	if(ms == 'y'){
		me_stopped = true;
		jQuery('._stop_').attr('checked', 'checked');
	}

	jQuery('._stop_').click(function(){
		if(jQuery(this).filter(':checked').length>0){
			me_stopped = true;
			setCookie('me_stopped', 'y');
		} else{
			me_stopped = false;
			setCookie('me_stopped', 'n');
		}
	});
	var o = jQuery.extend({
		time:500
	},o);

	function _tm_(){
		setTimeout(function(){if(!me_stopped){location.reload();}_tm_();}, o.time);
	};
	_tm_();

};

/*
 перемещение скрина
 options - опции плагина, описаны внутри
 */

jQuery.fn.fidel = function(o){
	var o = jQuery.extend({
		feel:10 // на сколько пикселей передвигать с зажатым shift - при работе стрелками
	},o);
	var L = false;
	var T = false;

	return this.each(function() {
		var cl = getCookie($(this).attr('src')+'_left');
		var ct = getCookie($(this).attr('src')+'_top');
		var chinf = getCookie($(this).attr('src')+'_inf');
		var chma = getCookie($(this).attr('src')+'_main');
		var chun = getCookie($(this).attr('src')+'_under');
		if(cl!=null){
			$(this).css('left', cl+'px');
		}
		if(ct!=null){
			$(this).css('top', ct+'px');
		}
		var inf;
		var inpt;
		var chb;
		var op;
		var inf = $('<div style="display:none;position:fixed;bottom:0;left:0;width:160px;height:10px;padding:2px 5px 8px 40px;background:#F2F2F2;/*border:1px solid gray;*/border-radius:0 6px 0 0;z-index:100000000;color:#000;font-size:12px;"></div>');
		var inpt = $('<div class="show_prototype" style="position:fixed;bottom:3px;left:2px;z-index:100000010;width:13px;height:13px;background:#5C99D6;border:1px solid #345073; border-radius:6px;cursor:pointer;"></div>');
		var chb = $('<div class="show_infobox" style="position:fixed;bottom:21px;left:2px;z-index:100000010;width:13px;height:13px;background:#5C99D6;border:1px solid #345073; border-radius:6px;cursor:pointer;"></div>');
		var op = $('<div class="opacity" style="position:fixed;bottom:3px;left:20px;z-index:100000010;width:13px;height:13px;background:#5C99D6;border:1px solid #345073; border-radius:6px;cursor:pointer;"></div>');
		var under = $('<div class="under" style="position:fixed;bottom:21px;left:20px;z-index:100000010;width:13px;height:13px;background:#5C99D6;border:1px solid #345073; border-radius:6px;cursor:pointer;"></div>');
		var obs = $('body > *').not(inf).not(inpt).not(chb).not(op).not(under).not($(this));
		$('body').prepend(inf);
		$('body').prepend(inpt, chb, op, under);
		$('body').prepend($(this));
		$(this).css({
			'position': 'absolute',
			'opacity':'0.5',
			'z-index':'100000'
		});

		var me = $(this);

		$(chb).click(function(){
			if($(this).data('clicked') == 1){ // ВКЛ
				$(this).data('clicked', 0).css('background', '#5C99D6');
				$(inf).hide();
				setCookie($(me).attr('src')+'_inf', 0);
			} else{ // ВЫКЛ
				$(this).data('clicked', 1).css('background', '#3DF5F5');
				$(inf).show();
				setCookie($(me).attr('src')+'_inf', 1);
			}
		});

		$(inpt).click(function(){
			if($(this).data('clicked') == 1){ // ВКЛ
				$(this).data('clicked', 0).css('background', '#5C99D6');
				$(me).hide();
				setCookie($(me).attr('src')+'_main', 0);
			} else{ // ВЫКЛ
				$(this).data('clicked', 1).css('background', '#3DF5F5');
				$(me).show();
				setCookie($(me).attr('src')+'_main', 1);
			}
		});

		$(op).click(function(){
			if($(this).data('clicked') == 1){ // ВКЛ
				$(this).data('clicked', 0).css('background', '#5C99D6');
				$(me).css('opacity', '0.5');
			} else{ // ВЫКЛ
				$(this).data('clicked', 1).css('background', '#3DF5F5');
				$(me).css('opacity', '1');
			}
		});

		$(under).click(function(){
			if($(this).data('clicked') == 1){ // ВЫКЛ
//				console.log('выкл');
				$(this).data('clicked', 0).css('background', '#5C99D6');
				setCookie($(me).attr('src')+'_under', 0);

				$(me).css({
					'z-index':'10000'
				});
				obs.css({
					'opacity':'1'
				});
			} else{ // ВКЛ
//				console.log('вкл');
				$(this).data('clicked', 1).css('background', '#3DF5F5');
				setCookie($(me).attr('src')+'_under', 1);

				$(me).css({
					'z-index':'1'
				});
				obs.animate({
					'position':'relative',
					'opacity':'0.5',
					'z-index':'100'
				},'slow').css('position','relative');
			}
		});

		$(op).mousewheel(function(e, d, dx, dy){
			 if($.browser.mozilla){
			 	d = (e.originalEvent.detail<0);
			 } else{
			 	d = (dy>0);
			 }
//			d = (dy>0);
			if(d){
				$(me).css('opacity', (parseFloat($(me).css('opacity'))+0.1).toFixed(2));
			} else{
				$(me).css('opacity', (parseFloat($(me).css('opacity'))-0.1).toFixed(2));
			}
			return false;
		});

		if(chinf==1){
			$(chb).data('clicked', 1).css('background', '#3DF5F5');
			$(inf).show();
		} else if(chinf==0){
			$(chb).data('clicked', 0).css('background', '#5C99D6');
			$(inf).hide();
		}

		if(chma==1){
			$(inpt).data('clicked', 1).css('background', '#3DF5F5');
			$(me).show();
		} else if(chma==0){
			$(inpt).data('clicked', 0).css('background', '#5C99D6');
			$(me).hide();
		} else{
			$(inpt).data('clicked', 1).css('background', '#3DF5F5');
		}

		if(chun==1){
			$(under).data('clicked', 0);
			$(under).click();
		} else if(chun==0){
			$(under).data('clicked', 1);
			$(under).click();
		} else{
			$(under).data('clicked', 1);
			$(under).click();
		}

		$(window).keydown(function(e){
			if(e.which!=16){
				if(e.shiftKey){
					var m = o.feel;
				} else{
					var m = 1;
				}
				if(e.which==38){
					$(me).css('top', Number($(me).css('top').split('px')[0])-m+'px');
					return false;
				} else if(e.which==40){
					$(me).css('top', Number($(me).css('top').split('px')[0])+m+'px');
					return false;
				} else if(e.which==37){
					$(me).css('left', Number($(me).css('left').split('px')[0])-m+'px');
					return false;
				} else if(e.which==39){
					$(me).css('left', Number($(me).css('left').split('px')[0])+m+'px');
					return false;
				}
			}
		});
		$(window).keyup(function(e){
			if(e.which>=37 && e.which<=40){
				$(inf).html('style="left: '+$(me).css('left')+'; top: '+$(me).css('top')+';"');
				setCookie($(me).attr('src')+'_left', $(me).css('left').split('px')[0]);
				setCookie($(me).attr('src')+'_top', $(me).css('top').split('px')[0]);
				return false;
			}
		});

		onMove = function(e){
			var lo = e.pageX-L;
			var to = e.pageY-T;
			$(this).css({'left': lo+'px', 'top': to+'px'});
			$(inf).html('style="left: '+lo+'px; top: '+to+'px;"');
		};

		onMouseUp = function(){
			document.ondragstart = function() { return true; };
			document.body.onselectstart = function() { return true; };
			setCookie($(this).attr('src')+'_left', $(this).css('left').split('px')[0]);
			setCookie($(this).attr('src')+'_top', $(this).css('top').split('px')[0]);
			$(this).unbind('mousemove');
		};

		$(this).mousedown(function(e){
			var ofs = $(this).offset();
			L = e.pageX - ofs.left;
			T = e.pageY - ofs.top;

			document.ondragstart = function() { return false; };
			document.body.onselectstart = function() { return false; };
			$(this).bind('mousemove', onMove).mouseup(onMouseUp).mouseleave(function(){
				$(this).unbind('mousemove');
			});
		});
	});
};

function setCookie (name, value, expires, path, domain, secure) {
	document.cookie = name + "=" + escape(value) +
		((expires) ? "; expires=" + expires : "") +
		((path) ? "; path=" + path : "") +
		((domain) ? "; domain=" + domain : "") +
		((secure) ? "; secure" : "");
}

function getCookie(name) {
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0) {
		offset = cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = cookie.indexOf(";", offset);
			if (end == -1) {
				end = cookie.length;
			}
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	return(setStr);
}
