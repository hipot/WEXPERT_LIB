/*
// автовращающаяся карусель
options - опции плагина, описаны внутри
*/

/* шаблон и стили галлереи
<style>
	.main_view {position:relative;}
	.main_view .listener {position:relative; top:50px; height:200px; width:500px; overflow: hidden;}
	.main_view .listener .img_box {position:absolute; top:0; left:0;}
	.main_view .listener .img_box div {display:none; position:absolute; }
	.main_view .listener .img_box div img {border:none;}

	.main_view .listener .paging	{position:absolute; left:5px; bottom:6px; height:20px; z-index:10;}
	.main_view .listener .paging > div {float:left; width:20px; height:20px; margin-right:5px; border:1px solid red; background:white; cursor:pointer;}
	.main_view .listener .paging > div.active {background:red;}
	.main_view .listener .stop, .play {position:absolute; top:10px; left:10px; width:20px; height:20px; border:1px solid red; background:#fff; z-index:100; cursor:pointer;}
	.main_view .listener .play {background:red;}
	.flash .link {position:absolute; left:33px; bottom:17px; width:87px; height:29px; text-align:center; background:url("img/more-btn.png") left top no-repeat; line-height:24px; font-weight:normal; font-size:13px; text-decoration:none; text-shadow:#943033 1px -1px 0; color:#fff; z-index:10;}
</style>
<div class="main_view">
	<div class="listener">
		<a class="link" href="#">Подробнее</a>
		<div class="paging">
			<div>1</div>
			<div>2</div>
			<div>3</div>
			<div>4</div>

		</div><div class="stop"></div>
		<div class="img_box">
			<div><img src="img1.jpg" alt="" /></div>
			<div><img src="img2.jpg" alt="" /></div>
			<div><img src="img3.jpg" alt="" /></div>
			<div><img src="img4.jpg" alt="" /></div>
		</div>
	</div>
</div>
*/

jQuery.fn.autolist = function(options){
	var options = jQuery.extend({
		timeout: 2000, // время останова
		animate: 500, // скорость анимации
		link: false // сслыка на подробный текущего банера
  	},options);

	return this.each(function() {
		var stopped = false;
		var sel = $(this);
		var img = $(sel).width();
		var img_amount = $(sel).find(".img_box img").size();
		var img_box = img * img_amount;
		$(sel).find(".img_box div:first").css("display", "block");
		$(sel).find(".paging div:first").addClass("active");
		$(sel).find(".img_box").css("width", img_box);

		move = function(){
			trigger = active.index();

			$(sel).find(".paging div").removeClass("active");
			active.addClass("active");

			$(sel).find(".img_box div:eq("+trigger+")").fadeIn(options.animate);
			$(sel).find(".img_box div:eq("+trigger+")").siblings().fadeOut(options.animate);
			if(options.link !== false)
				$(sel).find(".link").attr("href", $(sel).find(".img_box div:eq("+trigger+") img").attr("link"));
		};

		moveSwitch = function(){
			$(sel).find(".play").removeClass("play").addClass("stop");
			stopped = false;
			play = setInterval(function(){
				active = $(sel).find(".paging div.active").next();
				if(active.length === 0){
					active = $(sel).find(".paging div:first");
				}
				move();
			}, options.timeout);
		};
		moveSwitch();

		$(sel).find(".paging div").click(function(){
			$(this).siblings().removeClass("active");
			$(this).addClass("active");
			active = $(this);
			clearInterval(play);
			move();
			moveSwitch();
		});

		$(sel).find(".stop").click(function(){
			if(stopped == false){
				stopped = true;
				$(this).removeClass("stop").addClass("play");
				clearInterval(play);
			}
			else{
				stopped = false;
				moveSwitch();
			}
		});
  	});

}