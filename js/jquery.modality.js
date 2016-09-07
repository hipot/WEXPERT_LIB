(function($){
	/**
	 * Плагин для модальных окон <br>
	 * Опции внутри.
	 */
	$.fn.modality = function (o) {
		var o = $.extend({
			/**
			 * идентификатор окна
			 */
			id:$(this).attr('id') + '-modality',
			/**
			 * класс окна
			 */
			clas:'',
			/**
			 * позиционирование
			 */
			pos:'center',
			/**
			 * по какому объекту центровать
			 */
			from:$(window),
			/**
			 * показывать ли оверлей
			 */
			lock:false,
			/**
			 * оверлей с прокруткой
			 */
			scroll_lock:true,
			/**
			 * убирать ли прокрутку при показаном оверлее
			 */
			lock_body:true,
			/**
			 * z-index
			 */
			'z-index':1001,
			/**
			 * Объект jQuery - закрывальщик
			 */
			closer: $('<div>&times;</div>'),
			/**
			 * Показывает окно
			 * @param modal Окно
			 * @param over Оверлей
			 */
			show:function (modal, over) {
				if(o.lock_body){
					$('body').css('overflow','hidden');
				}
				modal.show();
				over.show();
				over.css({width:$(document).width()+'px', height:$(document).height()+'px'});
			},
			/**
			 * Скрывает окно
			 * @param modal Окно
			 * @param over Оверлей
			 */
			hide:function (modal, over) {
				if(o.lock_body){
					$('body').css('overflow','auto');
				}
				modal.hide();
				over.hide();
				over.css({width:$(document).width()+'px', height:$(document).height()+'px'});
			}
		}, o);
		var me = $(this);
		var body = $('body');
		var modal = $('#' + o.id);
		var over = $('.overlay[for="' + o.id + '"]');
		return this.each(function () {
			var posing = function () {
				var l = 0;
				var t = 30;

				if(o.pos == 'center'){
					var from_ofs = o.from.offset();
					if(!from_ofs){
						from_ofs = {top:0, left:0};
					}
					t = Math.ceil(from_ofs.top + (o.from.outerHeight() / 2) - (modal.outerHeight() / 2))+o.from.scrollTop();
					l = Math.ceil(from_ofs.left + (o.from.outerWidth() / 2) - (modal.outerWidth() / 2))+o.from.scrollLeft();
				}
				if(modal.css('position')=='fixed'){
					t -= o.from.scrollTop();
					l -= o.from.scrollLeft();
				}
				if(o.animate > 0){
					modal.animate({left:l + 'px', top:t + 'px'}, 'linear', o.animate);
				} else{
					modal.css({left:l+'px', top:t+'px'});
				}

				if(o.lock){
					over.css({width:$(document).width()+'px', height:$(document).height()+'px'});
				}
			};
			var init = function () {
				modal = $('#' + o.id);
				over = $('.overlay[for="' + o.id + '"]');
				if(modal.length <= 0){
					modal = $('<div class="modality '+o.clas+'" id="' + o.id + '"><div class="inner"></div><div class="bg"></div></div>');
					if(!o.closer.selector){
						modal.append(o.closer.addClass('closer'));
					}
					modal.css({
						position:'absolute',
						display:'none',
						left:'50%',
						top:'50%',
						padding:'15px',
//						color:'#fff',
						'z-index':o['z-index']
					});
					o.closer.css({
						position:'absolute',
						right:'5px',
						top:'2px',
						cursor:'pointer'
					});
					modal.find('.bg').css({
						position:'absolute',
						left:'0',
						top:'0',
						width:'100%',
						height:'100%'
//						opacity:'0.8',
//						filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity = 50)'
					});
					modal.find('.inner').css({
						background:'white',
						'position':'relative',
						'z-index':'10'
					});
					modal.find('.inner').append(me);
					if(o.scroll_lock){
						var wrap = $('<div class="modality-wrapper" />');
						wrap.css({
							position:'absolute',
							display:'none',
							left:'0',
							top:'0',
							width:'100%',
							height:'100%',
							'max-height':'100%',
							overflow: auto
						});
						modal.wrap(wrap);
						modal = wrap;
					}
					$('body').append(modal);
				}
				if(o.lock){
					if(over.length <= 0){
						over = $('<div class="overlay"><div class="loader"></div></div>');
						over.attr('for', o.id).css({
							position:'absolute',
							left:'0',
							top:'0',
							width:$(document).width()+'px',
							height:$(document).height()+'px',
							background:'black',
							opacity:'0.5',
							filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity = 50)',
							'z-index':o['z-index'] - 1
						});
						body.append(over);
					}
				}
				me.triggerHandler('modality.inited');
				posing();
			};
			init();
			$(window).resize(function () {
				posing();
			});
			if(!$(this).data('no-first')){
				// закрывальщик
				o.closer.add(modal.find('.closer:first')).live('click', function () {
					o.hide.call(this, modal, over);
				});
				// закрывать по ESC
				$(window).keyup(function (e) {
					if(e.keyCode == 27){
						o.hide.call(this, modal, over);
					}
				});
			}
			o.show.call(this, modal, over);
			$(this).data('no-first', true);
		});

	};
	$('body').triggerHandler('modality.loaded');
})(jQuery);
