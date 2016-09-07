/**
 * Предзагрузчик картинок, исполняет функцию then и thenAll после загрузки, соответственно, каждой картинки и всех картинок.
 * @param src {string|array} Путь к картинке, может быть строкой(одна картинка), или массивом строк(несколько картинок).
 * @param then {function} Callback функция исполняется после загрузки каждой из картинок. Здесь this - загруженная картинка(объект Image()).
 * @param thenAll { function(ar) } Callback функция исполняется после загрузки всех картинок. Здесь ar - массив всех значений возвращаемых функцией then.
 */
function imgPreloader(src, then, thenAll) {
	if(typeof src == 'object'){
		var len = src.length,
		res = [];
		var listimg = function(){
			for(var i in src){
				var img = new Image();
				if('addEventListener' in img){
					img.addEventListener('load', function (){
						var ret = true;
						if(typeof then == 'function'){
							ret = then.call(this);
						}
						res.push(ret);
					}, false);
				} else{
					img.attachEvent('onload', function (){
						var ret = true;
						if(typeof then == 'function'){
							ret = then.call(this);
						}
						res.push(ret);
					});
				}
				img.src = src[ i ];
			}
		};
		listimg();
		setTimeout(function(){
			if(res.length >= len){
				if(typeof thenAll == 'function')
					thenAll(res);
			} else{
				listimg();
				setTimeout(arguments.callee, 1);
			}
		}, 1);
	} else{
		var img = new Image();
		if('addEventListener' in img){
			img.addEventListener('load', function(){then.call(this);}, false);
		} else{
			img.attachEvent('onload', function(){then.call(this);});
		}
		img.src = src;
	}
}

/**
 * JS-подобный аналог PHP функции in_array()
 * @type {*}
 */
Array.prototype.in_array = function(p_val) {
	for(var i in this)	{
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
};

function inArraySearch(needle, arr, flag) {
	var html = '';
	if(!$.trim(needle)){
		var cou = 0;
		for(var i in arr){
			if(flag){
				html += '<li><a href="/banks/bank/?ID=' + i + '">' + arr[i] + '</a></li>';
			} else{
				html += '<li><a href="javascript:void(0);">' + arr[i] + '</a></li>';
			}

			cou++;
			if(cou == 12){
				break;
			}
		}
		return html;
	}
	re = new RegExp(needle, 'i');

	for(var i in arr){
		if(re.test(arr[i])){
			if(flag){
				html += '<li><a href="/banks/bank/?ID=' + i + '">' + arr[i] + '</a></li>';
			} else{
				html += '<li><a href="javascript:void(0);">' + arr[i] + '</a></li>';
			}
		}
	}

	return html;
}


/**
 * Создает постраничку
 * @param all   Всего страниц
 * @param cur   Текущая страница
 * @param by    По сколько страниц за раз
 */
paginationSetted=false;
var bldpgnsn=false;
function buildPagination(all,cur,by, url, replaceble){
	all = all>>0; cur = cur>>0; by = by>>0;
	var STR = '<div class="pages js" data-all="'+all+'" data-by="'+by+'" data-pre="'+url+'" ><p>Страницы:</p><ul>';
	var from = Math.ceil(cur - (by/2));
	if(from<1) from = 1;
	var to = from + by;
	if(to>=all){
		to=all+1;
		from = to - by;
		if(from<1) from = 1;
	}
	if(cur>1){
	    if(from>1){

	        STR += '<li><a page="1" href="' + url.replace('###',1) + '">1</a></li>';
	    }
		if(from>2){
			var n = Math.ceil(from/2);

			STR += '<li><a class="dots" page="'+n+'" href="' + url.replace('###',n) + '">...</a></li>';
		}
	}

	for(var i=from;i<to;i++){
		if(i==cur){
			STR += '<li><span>'+i+'</span></li>';
		} else{
			STR += '<li><a page="'+i+'" href="' + url.replace('###',i) + '">'+i+'</a></li>';
		}
	}

	if(to<all){
		var n = to+Math.floor((all-to)/2);
		STR += '<li><a class="dots" page="'+n+'" href="' + url.replace('###',n) + '">...</a></li>';
		STR += '<li><a page="'+all+'" href="' + url.replace('###',all) + '">'+all+'</a></li>';
	}

	STR += '</ul><ul class="nav">';
	if(cur<=1){
		STR += '<li><span>ПРЕДЫДУЩАЯ</span></li>';
	} else{
		var n = cur-1;
		STR += '<li><a id="PrevLink" page="'+n+'" href="' + url.replace('###',n) + '">ПРЕДЫДУЩАЯ</a></li>';
	}
	if(cur>=all){
		STR += '<li><span>СЛЕДУЮЩАЯ</span></li>';
	} else{
		var n = cur+1;
		STR += '<li><a id="NextLink" page="'+n+'" href="' + url.replace('###',n) + '">СЛЕДУЮЩАЯ</a></li>';
	}
	STR += '</li></ul>';
	STR += '<div class="clear"></div></div>';

	replaceble.each(function(){
		var pagination = $(STR);
		$(this).replaceWith(pagination);
	});

	if(!paginationSetted){
		paginationSetted=true;
		$('ul li a',replaceble.selector).live('click',function(){
			var p = ($(this).attr('page')>>0)-1;
			$('.tour-lent .pager-block:eq('+p+'), .tour-lent .block-pages:eq('+p+')').show().siblings('.pager-block:visible, .block-pages:visible').hide();
//			var $as = $('.pages').find('ul li:eq('+p+') > a');
//			$as.addClass('active').parent().siblings().find('.active').removeClass('active');
//			return false;

			var dt = $(this).parents('pages').data();
			buildPagination(all,$(this).attr('page'),by,url,$(replaceble.selector));
			return false;
		});
	}
}

/**
 * выбирает из массива координатных точек верхнюю левую и нижнюю правую
 * @param {Array} points массив координатных точек [ [ lt(float), ln(float) ], [ lt, ln ], [ lt, ln ] ]
 * @returns {Array}
 */
function mapGetBounds(points){
	var b = [[false,false],[false,false]];
	for(var i in points){
		var p = points[i];
		// находим наименьшую и наибольшую точку для метода setBounds (устанавливает расположение и зум карты вмещающий все точки)
		for(var k1=0;k1<=1;k1++){
			if(b[0][k1]==false || b[0][k1] > p[0][k1]){
				b[0][k1] = p[0][k1];
			}
			if(b[1][k1]==false || b[1][k1] < p[0][k1]){
				b[1][k1] = p[0][k1];
			}
		}
	}
	return b;
}

/**
 * Разбивает хеш строку, сформированную по типу GET параметов, в объект [key=val]
 * @returns {Object}
 */
function hashAsObject() {
	var hs = location.hash.substr(1);
	var har = hs.split('&');
	var opts = {};
	for (var i in har) {
		var v = har[i].split('=');
		opts[v[0]] = v[1];
	}
	return opts;
}