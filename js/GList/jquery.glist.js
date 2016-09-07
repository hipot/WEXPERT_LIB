/*
 * Special event for image load events
 * Needed because some browsers does not trigger the event on cached images.

 * MIT License
 * Paul Irish     | @paul_irish | www.paulirish.com
 * Andree Hansson | @peolanha   | www.andreehansson.se
 * 2010.
 *
 * Usage:
 * $(images).bind('load', function (e) {
 *   // Do stuff on load
 * });
 *
 * Note that you can bind the 'error' event on data uri images, this will trigger when
 * data uri images isn't supported.
 *
 * Tested in:
 * FF 3+
 * IE 6-9
 * Chromium
 * Opera 9-10
 */
(function ($) {
	$.event.special.load = {
		add: function (hollaback) {
			if ( this.nodeType === 1 && this.tagName.toLowerCase() === 'img' && this.src !== '' ) {
				// Image is already complete, fire the hollaback (fixes browser issues were cached
				// images isn't triggering the load event)
				if ( this.complete || this.readyState === 4) {
					hollaback.handler.apply(this);
				}

				// Check if data URI images is supported, fire 'error' event if not
				else if ( this.readyState === 'uninitialized' && this.src.indexOf('data:') === 0 ) {
					$(this).trigger('error');
				}

				else {
					setTimeout(function(){
						$('<img/>').attr('src', this.src);
						$(this).bind('load', hollaback.handler);
					}, 0);
				}
			}
		}
	};
}(jQuery));

/**
GList - jQuery Plugin
Галлерейка
options - опции плагина, описаны внутри
Скрин и верстка - 'пример.html' в папке с плагином
*/

$.fn.glist = function(o){

	var o = jQuery.extend({
		ob: $(this),
		ob_pano: $(this).find('.pano'),						// коробка для слайдов
		ob_slide: $(this).find('.pano .itm'),				// слайд
		left: $(this).find('.list .lb'),					// кнопка 'влево'
		right: $(this).find('.list .rb'), 					// кнопка 'вправо'
		inner: $(this).find('.list .inner'),				// объект ограничитель для кликабельных объектов
		ob_move: $(this).find('.list .inner > ul'),			// подвижный обьект
		ob_clicked: $(this).find('.list .inner > ul li'),	// кликабельный обьект
		act: 2,												// индекс активной иконки(в начальной позиции)
		speed: 300,											// скорость анимации иконки
		speed_slide: 300,									// скорость смены слайдов
		hide_thumbs: true,									// скрыть превьюшки и отобразить только при наведении
		hide_speed: 200,									// скорость скрывания превьюшек
		hover: $(this).find('.hover'),						// объект на hover которого срабатывает скрывание превьюшек
		autolist: 0,										// время задержки слайда при автоматическом перелистывании (если 0, то не листается)
		slider: false										// если rtue, тогда слайды будут меняться передвижением, иначе фейдами
		},o);

		var itm_w = Number($(o.ob_clicked).width())+Number($(o.ob_clicked).css('margin-left').split('px')[0])+Number($(o.ob_clicked).css('margin-right').split('px')[0])+Number($(o.ob_clicked).css('padding-left').split('px')[0])+Number($(o.ob_clicked).css('padding-right').split('px')[0]);
		var init_bot = $(o.inner).css('bottom').split('px')[0];

//		o.ob_slide.css('width', o.ob_slide.width()+'px');

		$(o.ob_move).width( (Number($(o.ob_clicked).length)+3)*itm_w).css('left','-'+itm_w+'px');
		$(o.ob_move.selector).prepend($(o.ob_clicked.selector).filter(':last'));
		$(o.ob_pano.selector).prepend($(o.ob_slide.selector).filter(':last'));

		var cnt = Math.floor(Number($(o.inner).width()-$(o.inner).css('margin-left').split('px')[0])/(itm_w))+1;

		played = false;

		if(o.slider){
			var wi = Number(o.ob_slide.filter(':first').width()); // ширина слайда
			var wm = 0;
			wm = Number(wi * o.ob_slide.length); // ширина блока pano (сумма ширин всех слайдов)
			var vis_w = Number(o.ob.children('.inner').width()); // ширина видимой области
			var mrg = (vis_w - wi)/2;
			o.ob_slide.addClass('slide not');
//			console.log(o.ob_pano);
			o.ob_pano.css({
				'position': 'absolute',
				'width': wm+'px',
				'height': o.ob_slide.filter(':first').height()+'px',
				'left': '-'+(wi-mrg)+'px'
			});
			o.ob.children('.inner').css({
				'height': o.ob_pano.height()-10+'px'
			});
		}

		var hidePreloader = function(){
			var cim = $('.lg_block img').length - 1;
			var ka = 0;
			$('.lg_block img').bind('load', function(){
				ka++;
				if(ka >= cim){
					$('.preloader').fadeOut('fast');
				}
			});
		};

		var changeSlide = function(th, anim){
			if(anim == undefined){
				anim = true;
			}
			if($(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').length > 0){
				var to = $(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]');
				var fi = $(o.ob_slide.selector+'.act');
				if(fi.length <= 0){
					var fi = $(o.ob_slide.selector+':eq(1)');
				}

				var l_to = 0;
				$(to).prevAll().each(function(){
					l_to -= $(this).width();
				});
				l_to = l_to + ((vis_w - $(to.selector).width())/2);

				var ti = $(to).index();
				var fri = $(o.ob_slide.filter('.act')).index();

				if(ti >= fri && (fri > 0 && !anim)){
					// первую в конец
//					console.log('первую в конец');
					var l = Number(o.ob_pano.css('left').split('px')[0]) + $(o.ob_slide.selector+':first').width();
					o.ob_pano.append($(o.ob_slide.selector+':first'))/*.css('left', l+'px')*/;
					if(anim){
						o.ob_pano.css('left', l+'px');
					}
				}
//				console.log(l_to);
				o.ob_pano.animate({'left': l_to+'px'}, o.speed_slide, 'linear', function(){
					if(ti < fri){
						// последнюю в начало
//						console.log('последнюю в начало');
						var l = Number(o.ob_pano.css('left').split('px')[0]) - $(o.ob_slide.selector+':last').width();
						o.ob_pano.prepend($(o.ob_slide.selector+':last')).css('left', l+'px');
					} else if(anim){
//						console.log('первую в конец');
						var l = Number(o.ob_pano.css('left').split('px')[0]) + $(o.ob_slide.selector+':first').width();
						o.ob_pano.append($(o.ob_slide.selector+':first')).css('left', l+'px');
					}
				});

				$(th).siblings().add(o.ob_slide).removeClass('act');
				$(th).add($(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]')).addClass('act');
			}
		};

		var changeFade = function(th, anim){
			if(anim == undefined){
				anim = true;
			}
			if($(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').length > 0){
				var from = $(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').siblings();
				var to = $(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]');
				if(anim){
					from.fadeOut(o.speed_slide);
					to.fadeIn(o.speed_slide);
				} else{
					from.hide();
					to.show();
				}
				$(th).siblings().removeClass('act');
				$(th).addClass('act');
			}
		};

		var toRight = function(move){
			if(change == undefined){
				change = true;
			}
			var front = Number($(o.ob_move).css('left').split('px')[0]) + itm_w;
			if(change){
				$(o.ob_move.selector).append($(o.ob_clicked.selector).filter(':first')).css('left', front+'px');
			}
			$(o.ob_move).animate({'left': '-='+itm_w+'px'}, o.speed, function(){
				played = false;
			});
		};

		var toLeft = function(move){
			if(change == undefined){
				change = true;
			}
			var back = Number($(o.ob_move).css('left').split('px')[0]) - itm_w;
			if(cnt+1 != o.ob_clicked.length && change){
				$(o.ob_move.selector).prepend($(o.ob_clicked.selector).filter(':last')).css('left', back+'px');
			}
			$(o.ob_move).animate({'left': '+='+itm_w+'px'}, o.speed, function(){
				if(cnt+1 == o.ob_clicked.length && change){
					$(o.ob_move.selector).prepend($(o.ob_clicked.selector).filter(':last')).css('left', back/2+'px');
				}
				played = false;
			});
		};

		var toggleThumbs = function(speed, show){
			if(show == undefined){
				show = true;
			}
			if(speed == undefined){
				speed = 0;
			}
			var obr = o.ob.find('.list');
			var h = obr.height();

			if(obr.css('bottom') == undefined){
				obr.css('bottom', '0px');
			}
			var bot = obr.css('bottom').split('px')[0];
			if(Math.abs(bot) == h && show){
				var to = init_bot;
			} else{
				var to = Number('-'+h);
			}
//			to = to - 10;
			if(speed > 0){
				obr.animate({'bottom':to+'px'}, speed);
			} else{
				obr.css('bottom',to+'px');
			}
		};

		// запускает автолистинг
		var timeID = false;
		var autolist = function(){
			if(o.autolist > 0){
				timeID = setTimeout(function(){
					var me = $(o.ob_clicked.selector).filter('.act').next();
					if(me.length < 1){
						me = $(o.ob_clicked.selector).filter(':first')
					}
					if(me.index() == cnt){
						toRight();
					}
					changeStep(me);
					autolist();
				}, o.autolist);
			}
		};

		// остонавливает автолистинг
		var stoplist = function(){
			if(timeID && o.autolist > 0){
				clearTimeout(timeID);
			}
		};

		// решаем какой ФУНКЦИЕЙ пользоваться ДЛЯ СМЕНЫ КАДРОВ
		var changeStep = function(th, anim){ changeFade(th, anim) };
		if(o.slider){
			changeStep = function(th, anim){ changeSlide(th, anim) };
		}


	hidePreloader();
	return this.each(function() {
		if(o.hide_thumbs){
			toggleThumbs(false, false);
			$(o.ob.selector).hover(function(){
				toggleThumbs(o.hide_speed);
				stoplist();
			}, function(){
				toggleThumbs(o.hide_speed, false);
				autolist();
			});
		}

		// в начальную позицию
		changeStep($(o.ob_clicked.selector+':eq('+o.act+')'), false);

		// слайд миниатюра
		$(o.ob_clicked).click(function(){
			changeStep($(this));
		});

		if(o.slider){
			// слайд
			$(o.ob_slide.selector).filter('.not').click(function(){
				if($(this).hasClass('act')){
					return false;
				}
				changeStep($(o.ob_clicked.selector+'[lg='+$(this).attr('lg')+']'));
			});
		}

		if(cnt < o.ob_clicked.length){ // если есть куда листать
			// влево
			$(o.left).click(function(){
				if(!played){
					played = true;
					toLeft();
				}
			});

			// вправо
			$(o.right).click(function(){
				if(!played){
					played = true;
					toRight();
				}
			});
		} else{
			changeStep($(o.ob_clicked.selector+':eq('+Number(Number(o.act)-1)+')'), false);
			toLeft(false);
		}

		// включаем автолистинг
		if(o.autolist > 0){
			autolist();
		}

	});
};
