/*
окно по центру экрана
options - опции плагина, описаны внутри
*/

jQuery.fn.center = function(options){
	var options = jQuery.extend({
		win: jQuery(window), // по какому обьекту центровать
		resize: false, // подгонять ли размер
		resizePadding: 0, // отступы подгоняемого обьекта
		resizedOb: this, // подгоняемый обьект
		resizedBy: jQuery(document), // по какому обьекту подгонять
		position: 'absolute', // CSS position
		animate: 0, // скорость анимации
		callback: null // callback 	функция принимает обьект this
	},options);

	var height;
	var width;
	var left;
	var top;
	var winOffset;
	var me = $(this);
	var dh = jQuery(document).height();
	return this.each(function() {
		if(options.resize === true)
		{
			if(jQuery(options.resizedOb).height() != jQuery(options.resizedBy).height() - (options.resizePadding*2)){
				height = jQuery(options.resizedBy).height() - (options.resizePadding*2);}
			else
				height = jQuery(options.resizedOb).height();

			if(jQuery(options.resizedOb).width() != jQuery(options.resizedBy).width() - (options.resizePadding*2))
				width = jQuery(options.resizedBy).width() - (options.resizePadding*2);
			else
				width = jQuery(options.resizedOb).width();

			if(options.animate <= 0)
			{
				jQuery(options.resizedOb).css({
					'height': height+'px',
					'width': width+'px'
				});
			}
		}

		winOffset = jQuery(options.win).offset();
		if(winOffset == null)
		{
			winOffset = new Object();
			winOffset.top = 0;
			winOffset.left = 0;
		}
		left = (options.win.width()/2)-(jQuery(this).width()/2)+winOffset.left;
		top = winOffset.top + ((jQuery(options.win).height() - jQuery(window).scrollTop())/2) - (jQuery(this).height()/2);

		if(!options.resize){
			top = (jQuery(window).scrollTop() + (jQuery(options.win).height()/2)) - (jQuery(this).height()/2);
		}

		// последние правки
		if(top + Number(jQuery(this).height()) > dh)
			top = top -  ((top + Number(jQuery(this).height())) - dh);
		// -----------------

		if(left<0) left = 0;
		if(top<0) top = 0;
		top = top;

		jQuery(this).css('position', options.position);

		if(width == undefined || height == undefined){
			var oban = {'opacity':'1'};
		} else{
			var oban = {'height': height+'px','width': width+'px'};
		}

		if(options.animate > 0)
		{
			jQuery(options.resizedOb).animate(oban,
			options.animate, 'swing', function(){
				winOffset = jQuery(options.win).offset();
				if(winOffset == null)
				{
					winOffset = new Object();
					winOffset.top = 0;
					winOffset.left = 0;
				}
				left = (options.win.width()/2)-(jQuery(this).width()/2)+winOffset.left;
				top = winOffset.top + ((jQuery(options.win).height() - jQuery(window).scrollTop())/2) - (jQuery(this).height()/2);

				if(!options.resize){
					top = (jQuery(window).scrollTop() + (jQuery(options.win).height()/2)) - (jQuery(this).height()/2);
				}

				// последние правки
				if(top + Number(jQuery(this).height()) > dh)
					top = top -  ((top + Number(jQuery(this).height())) - dh);
				// -----------------

				if(left<0) left = 0;
				if(top<0) top = 0;
				top = top;

				jQuery(me).animate({
					'left': left+'px',
					'top': top+'px'
				}, options.animate);
			});
		}
		else
		{
			jQuery(this).css({
				'left': left+'px',
				'top': top+'px'
			});
		}

		if (typeof options.callback == 'function') {
			return options.callback(this);
		}

		});
};
