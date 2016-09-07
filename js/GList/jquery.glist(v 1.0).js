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
		speed_slide: 300									// скорость смены слайдов
		},o);

		var itm_w = Number($(o.ob_clicked).width())+Number($(o.ob_clicked).css('margin-left').split('px')[0])+Number($(o.ob_clicked).css('margin-right').split('px')[0]);

		$(o.ob_move).width( (Number($(o.ob_clicked).length)+3)*itm_w ).css('left','-'+itm_w+'px');
		$(o.ob_move.selector).prepend($(o.ob_clicked.selector).filter(':last'));
		var cnt = Math.floor(Number($(o.inner).width())/itm_w);

		played = false;

		changeSlide = function(th){
			if($(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').length > 0){
				$(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').siblings().fadeOut(o.speed_slide);
				$(o.ob_slide.selector+'[lg="'+$(th).attr('lg')+'"]').fadeIn(o.speed_slide);
				$(th).siblings().removeClass('act');
				$(th).addClass('act');
			}
		};

		toRight = function(){
			var front = Number($(o.ob_move).css('left').split('px')[0]) + itm_w;
			$(o.ob_move.selector).append($(o.ob_clicked.selector).filter(':first')).css('left', front+'px');
			$(o.ob_move).animate({'left': '-='+itm_w+'px'}, o.speed, function(){
				played = false;
			});
		};

		toLeft = function(){
			var back = Number($(o.ob_move).css('left').split('px')[0]) - itm_w;
			$(o.ob_move.selector).prepend($(o.ob_clicked.selector).filter(':last')).css('left', back+'px');
			$(o.ob_move).animate({'left': '+='+itm_w+'px'}, o.speed, function(){
				played = false;
			});
		};

	return this.each(function() {
		// в начальную позицию
		changeSlide($(o.ob_clicked.selector+':eq('+o.act+')'));

		// слайд
		$(o.ob_clicked).click(function(){
			changeSlide($(this));
		});

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
		}
	});
};