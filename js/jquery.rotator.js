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
			me: $(this), 												// я
			prev_btn: $(this).find('.prev:first span'), 				// предыдущий
			next_btn: $(this).find('.next:first span'), 				// следующий
			lst: $(this).find('.list:first > span'), 					// список
			lst_i: $(this).find('.list:first > span > div'),			// элемент списка
			main: $(this).find('.main:first'), 							// главный блок
			slider: $(this).find('.main:first .slider:first'), 			// слайдер
			slide: $(this).find('.main:first .slider:first > div'), 	// слайд
			img: $(this).find('.main:first .slider:first > div img'), 	// картинка в слайде
			click: true,												// смена по клику на картинку
			clicker: $(this).find('.main:first .slider:first > div img'),	// объект кликанья - смена по клику на картинку
			circle: true,												// цикличная смена
			tm:200,														// время анимации
			automate:false,												// автоматически переключать
			delay:2400,													// время задержки
			type:r_type.slide_horiz,									// время задержки
			timeSet:false
		},o);

		var mw=0; // ширина основного блока(уст. в init())
		var mh=0; // высота основного блока(уст. в init())

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
					if(mh < rh){
						mh = rh;
					}
					if(mw < rw){
						mw = rw;
					}
				});

				switch(o.type){
					case r_type.fade:

						o.main.add(o.slide, o.slider).height(mh).width(mw);
						o.slide.addClass('fade_itm');
						o.slider.children('br').remove();
						o.slider.addClass('fade_outer');
						for(var i=0; i < o.slide.length; i++){
							o.slider.append(o.slide.filter(':eq('+i+')'));
						}
						break;

					case r_type.slide_horiz:

						o.main.add(o.slide).height(mh).width(mw);
						o.slider.width(o.slide.length * mw);
						break;

					case r_type.slide_vert:

						o.main.add(o.slide).height(mh).width(mw);
						o.slider.height(o.slide.length * mh);
						break;

				}

				o.lst_i.filter(':first').addClass('a');
				o.slide.filter(':first').addClass('a');
			}
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

		return this.each(function() {
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

			if(o.click){
				o.clicker.on('click', function(e){
					if(e.ctrlKey){
						change(Number($(o.slide).has($(this)).prev().index()));
					} else{
						change(Number($(o.slide).has($(this)).next().index()));
					}
				}).css('cursor', 'pointer');
			}
		});
	};
}(jQuery));

/*
<style type="text/css">
.rotator { position:relative;}
.rotator .hd {}
.rotator .hd table { width:100%; height:20px; margin-bottom:5px; border-bottom:1px solid #a2d7f2; border-top:1px solid #a2d7f2; }
.rotator .hd table td { vertical-align:middle;}
.rotator .hd table td span { cursor:pointer; text-transform:uppercase; color:#0492dc; padding:0 4px; font-style:11px;}
.rotator .hd table td.prev { text-align:left;}
.rotator .hd table td.next { text-align:right;}
.rotator .list { text-align:center;}
.rotator .list > span { display:inline-block; margin:auto auto; padding:0;}
.rotator .list > span > div {float:left; margin:0; padding:0; width:8px; height:8px; background: url(images/cube_lst.png) 0 0 no-repeat; cursor:pointer; margin:0 2px;}
.rotator .list > span > div.a { background-position:-8px 0;}
.rotator .main { overflow:hidden; position:relative;}
.rotator .main .slider { position:absolute;}
.rotator .main .slider > div { float:left; text-align:center; overflow:hidden;}
.rotator .main .slider > div img {}
.rotator .preloader { position:absolute; left:0; top:0; width:100%; height:100%; background:#fff; z-index:100;}
.rotator .fade_outer { position:relative;}
.rotator .fade_itm { display:none; position:absolute; left:0; top:0; background:#fff;}
.rotator .fade_outer .fade_itm.a { display:block;}
</style>
<div class="rotator">
	<div class="hd">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="prev"><span>предыдущее</span></td>
				<td class="list">
					<span>
						<div></div>
						<div></div>
						<div></div>
						<div></div>
					</span>
				</td>
				<td class="next"><span>следующее</span></td>
			</tr>
		</table>
	</div>
	<div class="main">
		<div class="slider">
			<div>
				<img src="<?=SITE_TEMPLATE_PATH?>/images_tmp/cd1.jpg" />
			</div>
			<div>
				<img src="<?=SITE_TEMPLATE_PATH?>/images_tmp/cd2.jpg" />
			</div>
			<div>
				<img src="<?=SITE_TEMPLATE_PATH?>/images_tmp/cd3.jpg" />
			</div>
			<div>
				<img src="<?=SITE_TEMPLATE_PATH?>/images_tmp/cd4.jpg" />
			</div>
			<br clear="all" />
		</div>
	</div>
	<br clear="all" />
</div>
 */
