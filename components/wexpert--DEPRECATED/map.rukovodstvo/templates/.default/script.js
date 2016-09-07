var additionalFunctions = {};
/**
 * padding-left + padding-right
 * @param jQuery $t
 * @returns int
 */
additionalFunctions['specialpaddingslr'] = function($t){
	return ((($t.css('padding-left').split('px')[0])>>0)+(($t.css('padding-right').split('px')[0])>>0));
};
/**
 * padding-top + padding-bottom
 * @param jQuery $t
 * @returns int
 */
additionalFunctions['specialpaddingstb'] = function($t){
	return ((($t.css('padding-top').split('px')[0])>>0)+(($t.css('padding-bottom').split('px')[0])>>0));
};



$(function(){
	if (rukovodstvo_map_component != 'undefined') {
		$('body').on('click', '.mngr-box > a', function () {
			var id = $(this).data('id');
			var $pp = $('.mngr-popup');
			$('body').css('overflow', 'hidden');
			$pp.addClass('fadeIn');

			$.ajax({
				type: "POST",
				url: rukovodstvo_map_component.path + "/ajax/index.php",
				data: {'id': id},
				dataType: 'html',
				error: function (error) {
					console.error('Ошибка AJAX: ', error);
				},
				success: function (html, status) {
	//				console.log('ajax : ', html);
					if (html=='') {
						alert("Ошибка :( \nИнформация о руководителе не найдена");
					}
					$('.window > .inner',$pp).html(html);
					$('.window',$pp).trigger('resize');
				}
			});

			return false;
		});

		$('.mngr-popup .window').on('resize', function () {
			var $th = $(this);
			setTimeout(function(){
				var ml = Math.ceil((($th.width() >> 0) + (additionalFunctions['specialpaddingslr']($th))) / 2)
				var mt = Math.ceil((($th.height() >> 0) + (additionalFunctions['specialpaddingstb']($th))) / 2);
				var pos = $th.position();
				if(mt>pos.top){
					mt=pos.top;
				}
				if(ml>pos.left){
					ml=pos.left;
				}
				$th.animate({
					'margin-left': '-' + ml + 'px',
					'margin-top': '-' + mt + 'px'
				}, 'fast', function(){
					$('.inner',$th).addClass('fadeIn')
				})
			},100);

		});

		$('.mngr-popup').on('hide', function (e) {
			$(this).removeClass('fadeIn');
			$('.window .inner',this).html('').parent().trigger('resize');
			$('body').css('overflow', 'auto');
		});

		$('.mngr-popup').on('click', function (e) {
			var $tg = $(e.target);
			if (
				( $tg.filter('.window').length || $tg.parents('.window').length )
					&& !$tg.hasClass('closer')
				) return false;
			$(this).trigger('hide');
		});
		// закрытие попапа покнопке ESC
		$(window).keyup(function (e) {
			if (e.keyCode == 27) {
				$('.mngr-popup').trigger('hide');
			}
		});
	}
});

/**
 * Инициализация карты с точками
 * @param arPlacemarks массив точек как они передаются в конструктор класса ymaps.Placemark
 */
function rukovodstvoMapInit(arPlacemarks){
	var map = new ymaps.Map("rukovodstvo-map", {
		center: [55.76, 37.64]
	});
	// АФТОРРРР не поместился :(
	// map.copyrights.add('&copy;  Группа Компаний KDL - <a href="/">клинико-диагностические лаборатории</a>');

	// элементы управления
	var myControls = [
		new ymaps.control.MiniMap({expanded:false}),
		'mapTools','scaleLine','trafficControl','typeSelector','zoomControl','smallZoomControl'
	];
	for(var i in myControls){
		map.controls.add(myControls[i]);
	}
	// точки
	var arBounds = [[false,false],[false,false]];
	for(var i in arPlacemarks){
		var v = arPlacemarks[i];
		// находим наименьшую и наибольшую точку для метода setBounds (устанавливает расположение и зум карты вмещающий все точки)
		for(var k1=0;k1<=1;k1++){
			if(arBounds[0][k1]==false || arBounds[0][k1] > v[0][k1]){
				arBounds[0][k1] = v[0][k1];
			}
			if(arBounds[1][k1]==false || arBounds[1][k1] < v[0][k1]){
				arBounds[1][k1] = v[0][k1];
			}
		}
		var p = new ymaps.Placemark(v[0],v[1],v[2]);
		map.geoObjects.add(p);
	}
	// центровка карты по точкам
	map.setBounds(arBounds, { checkZoomRange: false});

	// вся эта колбаса нужна чтоб соорудить предзагрузку изображений в балуне
	map.events.add('balloonopen',function(event){
		$bal = $(event.get('balloon').getOverlay().getElement());
		var ars = [];
		$('img',$bal).each(function(){
			ars.push($(this).attr('src'));
		});
		imgPreloader(ars,false,function(){
			setTimeout(function(){
				$('.mngr-box',$bal).addClass('fadeIn');
			},300);
//			$('.mngr-box',$bal).fadeIn('slow');
		})
	});
}
