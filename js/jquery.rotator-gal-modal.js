var r_type = {
	fade: 'fade',
	slide_horiz: 'slide_horiz',
	slide_vert: 'slide_vert'
};
(function($){
	/*
	 * Special event for image load events
	 *
	 * Usage:
	 * $(images).bind('load', function (e) {
	 *   // Do stuff on load
	 * });
	 *
	 */
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


	/**
	 * @author Matiash Sergei (WEXPERT)
	 * params inside
	 */
	$.fn.rotator = function(o) {
		var o = $.extend({
			to:false,													// на какой слайд переключить
			iam: $(this), 												// я - блок с оверлеем
			me: $(this).find('.rotator'), 								// я - ротатор
			nav_btns: $(this).find('.rotator .main-gal-wrapper > .prev-btn:first, .rotator .main-gal-wrapper > .next-btn:first'),	// предыдущий/следующий
			preloader_img: $(this).find('.bg'),						// картинка прелоадера
			prev_btn: $(this).find('.prev-btn:first'), 					// предыдущий
			next_btn: $(this).find('.next-btn:first'), 					// следующий
			lst: $(this).find('.thumbs-gal-wrapper:first'),				// превьюшки картинок (список)
			lst_wrap: $(this).find('.thumbs-gal-wrapper:first'),		// превьюшки картинок (список)
			lst_i: $(this).find('.thumbs:first .t-slider > div'),		// превьюшка картинок (элемент)
			main_wrap: $(this).find('.main-gal-wrapper:first'), 		// главный блок
			main: $(this).find('.main-gal-wrapper:first'), 				// главный блок
			slider: $(this).find('.main:first .slider:first'), 			// слайдер
			slide: $(this).find('.main:first .slider:first > div'), 	// слайд
			img: $(this).find('.main:first .slider:first > div img'), 	// картинка в слайде
			click: true,												// смена по клику на картинку
			clicker: $(this).find('.main:first .slider:first > div img'),	// объект кликанья - смена по клику на картинку
			lst_slider: $(this).find('.thumbs:first .t-slider'),		// превьюшки передвиженец
			th_nav: $(this).find('.prev-btn.th:first,.next-btn.th:first'),	// превьюшка кнопки вперед/назад
			th_next: $(this).find('.next-btn.th:first'),				// превьюшка кнопка вперед
			th_prev: $(this).find('.prev-btn.th:first'),				// превьюшка кнопка назад
			esc: $(this).find('.closer'),								// унопка ESC
			circle: true,												// цикличная смена
			tm:200,														// время анимации
			thumbs_tm:200,												// время анимации
			automate:false,												// автоматически переключать
			delay:2400,													// время задержки
			type:r_type.slide_horiz,									// тип анимации
			timeSet:false
		},o);

		// o.iam.data('mw') ширина основного блока(уст. в init())
		if(o.iam.data('mw')==undefined){
			o.iam.data('mw',0);
		}
		// o.iam.data('mh') высота основного блока(уст. в init())
		if(o.iam.data('mh')==undefined){
			o.iam.data('mh',0);
		}
		// o.iam.data('middle_list_i') средняя ширина превьюшки
		if(o.iam.data('middle_list_i')==undefined){
			o.iam.data('middle_list_i',0);
		}
		// o.iam.data('thumbs_list_max') минимальное смещение списка привьюшек влево
		if(o.iam.data('thumbs_list_max')==undefined){
			o.iam.data('thumbs_list_max',0);
		}
		o.iam.data('thumbs_list_min',0);  // максимальное смещение списка привьюшек влево

		var check_thumbs_active = function(l){
			if(l <= o.iam.data('thumbs_list_max')){
				o.th_next.addClass('n');
			} else{
				o.th_next.removeClass('n');
			}
			if(l >= o.iam.data('thumbs_list_min')){
				o.th_prev.addClass('n');
			} else{
				o.th_prev.removeClass('n');
			}
		};

		var pos_main_rows = function(){
			var mh = o.main_wrap.height();
			var bh = o.nav_btns.outerHeight(true);
			o.nav_btns.css('top', ((mh/2)-(bh/2))+'px');
		};

		var check_thumbs_pos = function(th){
			var l = th.position().left+th.outerWidth(true)+o.lst_slider.position().left;
			if(l > o.lst.width()){
				var l = l - o.lst.width();
				o.lst_slider.animate({'left':'-='+l+'px'}, function(){
					check_thumbs_active(o.lst_slider.position().left);
				});
			}
			var ll = th.position().left+o.lst_slider.position().left;
			if(ll < 0){
				var l = Math.abs(ll);
				o.lst_slider.animate({'left':'+='+l+'px'}, function(){
					check_thumbs_active(o.lst_slider.position().left);
				});
			}
		};

		var init = function(){
			if(o.me.find('.preloader').length <= 0){
				o.me.prepend('<div class="preloader"></div>');
			}
			var cim = o.img.length - 1;
			var ka = 0;
			o.img.bind('load', function(){
				ka++;
				if(ka >= cim){
					$('.preloader').fadeOut('slow');
					o.preloader_img.css('background','#000');
					o.me.css('display','block');
					initer(this);
				}
			});

			function initer(th){
				o.slide.each(function(){
					var rh = 0;
					var rw = 0;
					rh = $(this).outerHeight(true);
					rw = $(this).outerWidth(true);
					/*$(this).children().each(function(){
						rh += $(this).outerHeight(true);
						rw += $(this).outerWidth(true);
					});*/
					if(o.iam.data('mh') < rh){
						o.iam.data('mh',rh);
					}
					if(o.iam.data('mw') < rw){
						o.iam.data('mw',rw);
					}
				});

				o.slide.each(function(){
					var i = $(this).find('img');
					var t = (o.iam.data('mh') - i.height())/2;

					i.css('margin-top', t+'px');
				});

				if(o.slide.length > 1){
					o.nav_btns.show('fast',function(){
						pos_main_rows();
					});
				}

				o.lst.css('width', o.iam.data('mw')+'px');
				var th_w = 0;
				o.lst_i.each(function(){
					th_w += $(this).outerWidth(true);
				});
				o.iam.data('middle_list_i',Math.ceil(th_w/o.lst_i.length));
				o.iam.data('thumbs_list_max',-(th_w - o.lst.width()));
				o.lst_slider.css('width', (th_w)+'px');
				if(th_w > o.iam.data('mw')){
					o.th_nav.show('fast');
				}
				check_thumbs_active(o.lst_slider.position().left);

				var sum_w = o.iam.data('mw') + (Math.abs(o.nav_btns.filter(':first').css('left').split('px')[0]>>0)*2);
				var sum_h = o.iam.data('mh') + (o.lst.height()>>0) + 100;
				var ww = $(window).width();
				var wh = $(window).height();

				if(sum_h > wh){
					o.iam.data('mh', o.iam.data('mh')-(sum_h-wh));
				}
				if(sum_w > ww){
					o.iam.data('mw', o.iam.data('mw')-(sum_w-ww));
				}
				o.me.css('margin-top', '-'+((o.iam.data('mh')/2)+60)+'px');

				switch(o.type){
					case r_type.fade:

						o.main.add(o.slide, o.slider).height(o.iam.data('mh')).width(o.iam.data('mw'));
						o.slide.addClass('fade_itm');
						o.slider.children('br').remove();
						o.slider.addClass('fade_outer');
						for(var i=0; i < o.slide.length; i++){
							o.slider.append(o.slide.filter(':eq('+i+')'));
						}
						break;

					case r_type.slide_horiz:

						o.main.add(o.slide).height(o.iam.data('mh')).width(o.iam.data('mw'));
						o.slider.width(o.slide.length * o.iam.data('mw'));
						break;

					case r_type.slide_vert:

						o.main.add(o.slide).height(o.iam.data('mh')).width(o.iam.data('mw'));
						o.slider.height(o.slide.length * o.iam.data('mh'));
						break;

				}

				o.lst_i.filter(':first').addClass('a');
				o.slide.filter(':first').addClass('a');
			}
			o.me.data('was_inited',true);
		};

		/**
		 * движение слайдов
		 * @param to индекс элемента до кокого слайда двигать
		 */
		var change = function(to){
			if(to < 0 || to >= o.slide.length){
				if(o.circle){
					if(o.slide.filter('.a').index() == 0){
						to = o.slide.length-1;
					} else{
						to = 0;
					}
				} else{
					return;
				}
			}
			var $to = o.slide.filter(':eq('+to+')');
			check_thumbs_pos(o.lst_i.filter(':eq('+to+')'));
			if($to.hasClass('a')) return;
			var tmt = 0;
			var $from = o.slide.filter('.a');

			switch(o.type){
				case r_type.fade:

					$to.fadeIn(o.tm);
					o.slide.filter('.a').fadeOut(o.tm);
					tmt = o.tm;
					break;

				case r_type.slide_horiz:

					o.slider.animate({left: '-'+$to.position().left+'px'}, o.tm);
					break;

				case r_type.slide_vert:

					o.slider.animate({top: '-'+$to.position().top+'px'}, o.tm);
					break;

			}

			setTimeout(function(){
				$to.add(o.lst_i.filter(':eq('+to+')')).addClass('a').siblings().removeClass('a');
			}, tmt);
		};

		function go(){
			change(Number(o.lst_i.filter('.a').next().index()));
			play();
		}

		function play(){
			o.timeSet = setTimeout(go, o.delay);
		}

		function stop(){
			clearTimeout(o.timeSet);
		}

		function hide_gallery(){
			o.iam.hide();
		}
		o.iam.show();
		return this.each(function() {
			if(o.me.data('was_inited')){
				o.me.show();
			} else{
				init();
				if(o.automate){
					play();
					o.main.on({
						hover: function(){
							stop();
						},
						mouseleave: function(){
							play();
						}
					});
				}
				o.lst_i.on('click', function(){
					change($(this).index());
				});

				o.prev_btn.on('click', function(){
					change(Number(o.slide.filter('.a').prev().index()));
				});

				o.next_btn.on('click', function(){
					change(Number(o.slide.filter('.a').next().index()));
				});

				o.th_next.on('click', function(){
					if($(this).hasClass('n')) return false;
					var l = o.lst_slider.position().left - o.iam.data('middle_list_i');
					if(l < o.iam.data('thumbs_list_max')){
						l = o.iam.data('thumbs_list_max');
					}
					check_thumbs_active(l);
					o.lst_slider.animate({'left':l+'px'});
				});

				o.th_prev.on('click', function(){
					if($(this).hasClass('n')) return false;
					var l = o.lst_slider.position().left + o.iam.data('middle_list_i');
					if(l > o.iam.data('thumbs_list_min')){
						l = o.iam.data('thumbs_list_min');
					}
					check_thumbs_active(l);
					o.lst_slider.animate({'left':l+'px'});
				});

				o.esc.click(function(){
					hide_gallery();
				});

				$(window).keyup(function(e){
					if(e.keyCode == 27)
						hide_gallery();
				});

				if(o.click){
					o.clicker.on('click', function(e){
						if(e.ctrlKey){
							change(Number($(o.slide).has($(this)).prev().index()));
						} else{
							change(Number($(o.slide).has($(this)).next().index()));
						}
					}).css('cursor', 'pointer');
				}
			}
			if(o.to!==false){
				change(o.to);
			}
		});
	};
}(jQuery));

/*
<style type="text/css">
.o-gal {opacity:1 !important; display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%;}
.o-gal .bg { position:absolute; left:0; top:0; width:100%; height:100%; opacity:0.8; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity = 80); background:#000 url(images/preloader.gif) center center no-repeat; z-index:1;}
.r-wrapper { position:relative; top:50%; z-index:10; width:920px; height:720px; margin:0 auto;}
.rotator { display:none; margin-top:-48%;}
.rotator .closer {position: absolute; top:-28px; right: -5px; width: 40px; height: 12px; background: url(images/gal_esc.png) 0 0 no-repeat; cursor: pointer;}
.rotator .main-gal-wrapper { position:relative; margin:0 auto;}
.rotator .main { overflow:hidden; position:relative; }
.rotator .main .slider { position:absolute;}
.rotator .main .slider > div { float:left; text-align:center; overflow:hidden; }
.rotator .main .slider > div img {}
.rotator .preloader { display:none; position:absolute; left:0; top:0; width:100%; height:100%; background:#fff; z-index:100;}
.rotator .fade_outer { position:relative;}
.rotator .fade_itm { display:none; position:absolute; left:0; top:0; background:#fff;}
.rotator .fade_outer .fade_itm.a { display:block;}
.rotator .thumbs-gal-wrapper { position:relative; margin:0 auto; }
.rotator .thumbs { position:relative;  overflow:hidden; width:100%; height:135px;}
.rotator .thumbs .t-slider { position:absolute; margin:2px; }
.rotator .thumbs .t-slider > div {float: left; margin: 0 5px 0 0; cursor: pointer; }
.rotator .thumbs .t-slider > div:hover,
.rotator .thumbs .t-slider > div.a { outline:2px solid #fff;}
.rotator .thumbs .t-slider > div img { display:block;}
.rotator .prev-btn,
.rotator .next-btn { display:none; background: url(images/rows_gal.png) 0 0 no-repeat; position:absolute; top:300px; width:20px; height:42px; cursor:pointer;}
.rotator .prev-btn.n,
.rotator .next-btn.n { opacity:0.5; cursor:default;}
.rotator .prev-btn { left:-35px;}
.rotator .next-btn { right:-35px; background-position:-20px 0;}
.rotator .prev-btn.th,
.rotator .next-btn.th { top:auto; bottom:37%;}
.rotator .prev-btn.th.n,
.rotator .next-btn.th.n { opacity:0.5; cursor:default;}
</style>
<div class="overlay o-gal">
	<div class="bg"></div>
	<div class="r-wrapper">
		<div class="rotator">
			<div class="closer"></div>
			<div class="main-gal-wrapper">
				<div class="prev-btn"></div>
				<div class="next-btn"></div>
				<div class="main">
					<div class="slider">
						<?foreach($arResult["PROPERTIES"]["gallery"] as $gallery): ?>
						<div>
							<img src="<?=CImg::Resize($gallery["VALUE"], 900)?>" />
						</div>
						<? endforeach;?>
						<br clear="all" />
					</div>
				</div>
			</div>
			<br clear="all" />
			<div class="thumbs-gal-wrapper">
				<div class="prev-btn th"></div>
				<div class="next-btn th"></div>
				<div class="thumbs">
					<div class="t-slider">
						<?foreach($arResult["PROPERTIES"]["gallery"] as $gallery): ?>
						<div>
							<img src="<?=CImg::Resize($gallery["VALUE"], 130, 130, 'CROP')?>" />
						</div>
						<? endforeach;?>
						<br clear="all" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
 */
