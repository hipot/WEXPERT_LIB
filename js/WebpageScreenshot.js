/* This file minified to make it smaller as I can.
 *  If interesting you something about this extension, you are welcome to contact me, 
 *  at: extensions@bubbles.co.il
 */
background = chrome.extension.getBackgroundPage().background;
var popup = {
	changeWindow:function (a) {
		$(".window").hide();
		$(".window[tag=" + a + "]").show()
	},
	window:window,
	cancel:false
};
function abc() {
	function c() {
		$(".msg").each(function () {
			$(this).html(chrome.i18n.getMessage($(this).attr("tag")))
		})
	}

	function b() {
		var e = ",en,he,pl,de,ja,zh-CN,pt,ml,it,zh-TW,es,nl,cz,hu,ar,sl,sl-SL,ca,ko,ru,no,nb,id,vi,tr,el,sv,da,";
		chrome.i18n.getAcceptLanguages(function (h) {
			var f = "";
			for(var g = 0; g < h.length; g++){
				if(e.indexOf("," + h[g].substring(0, 2) + ",") >= 0){
					continue
				}
				f += "<a href=\"javascript:chrome.tabs.create({url:'http://spreadsheets.google.com/viewform?cfg=true&formkey=dGluSllVMUdBdkVCVUdRemZObDhOcnc6MA&entry_0=" + h[g] + "'})\" style=display:block;text-decoration:underline;color:blue;cursor:pointer> Translate Into My Language (" + h[g] + ")</a>"
			}
			$("#transarea").html(f)
		})
	}

	$(function () {
		$(".resizer").click(function () {
			$(".resizers").toggle()
		});
		if(localStorage.resizeOption != 0 && localStorage.resizeOption){
			$(".resizers").show()
		}
		if(localStorage.autosave == "true"){
			$("#autosave")[0].checked = true
		}
		$("#autosave").change(function () {
			localStorage.autosave = $("#autosave")[0].checked?"true":"false"
		});
		disableScroll = function () {
			$("#noall").show();
			$(".startWhole").hide();
			$(".editcontent").hide()
		};
		c();
		if(localStorage.installText){
			$("#installedby").html("Installed By " + localStorage.installText)
		} else{
			if(!localStorage.webmaster){
				$("#installedby").html("<small>Are you a webmaster?<br><a href=# id=webyes>Yes</a> | <a href=# id=webno> No </a></a></small>");
				$("#webno").click(function () {
					localStorage.webmaster = "no";
					$("#installedby").hide()
				});
				$("#webyes").click(function () {
					localStorage.webmaster = "yes";
					chrome.tabs.create({url:"http://www.webpagescreenshot.info/?t=Are%20you%20a%20webmaster"})
				})
			}
		}
		chrome.permissions.contains({origins:["http://*/*"]}, function (e) {
			if(e){
				background.tryGetUrl(function (f) {
					if(f.indexOf("chrome://") >= 0 || f.indexOf("chrome-extension:") >= 0 || f.indexOf("https://chrome.google.com") >= 0){
						disableScroll()
					}
					if(f.indexOf("file:") == 0){
						wNoExternal = window.setTimeout(function () {
							$("#nolocal").show();
							$(".startWhole").hide()
						}, 500);
						chrome.extension.sendRequest(background.externalId, {type:"checkExist"}, function () {
							window.clearTimeout(wNoExternal)
						})
					}
				});
				cb = function (f) {
					$("#asd").attr("src", f).attr("width", "200").click(function () {
						localStorage.fast = f;
						chrome.tabs.create({url:chrome.extension.getURL("fast.html"), selected:true})
					})
				};
				chrome.tabs.captureVisibleTab(null, {format:"png"}, cb)
			}
		});
		$(".startVisible").click(function () {
			a(background.startWithoutScroll())
		});
		$(".startWhole").click(function () {
			a(background.startWithScroll)
		});
		$(".editcontent").click(background.editcontent);
		$("#justresize").click(a);
		$(".mhtml").click(background.mhtml);
		$(".cancel").click(function () {
			popup.cancel = true;
			window.close()
		});
		$("[name=width]").val(localStorage.width);
		$("[name=height]").val(localStorage.height);
		$("[name=resize][value=" + localStorage.resizeOption + "]").attr("checked", true);
		$("[name=width]").add("[name=height]").click(function () {
			$("[name=resize][value=3]").attr("checked,true")
		});
		if(navigator.platform.toLowerCase().indexOf("win") >= 0){
			$(".windows").show()
		}
	});
	function a(e) {
		if(!$.isFunction(e)){
			e = function () {
			}
		}
		chrome.tabs.getSelected(null, function (f) {
			url = f.url
		});
		background.resizeBack = false;
		resValue = $("[name=resize]:checked")[0].value;
		if(resValue == 0){
			e();
			localStorage.resizeOption = 0;
			return
		} else{
			if(resValue == 1){
				width = 800;
				height = 600;
				localStorage.resizeOption = 1
			} else{
				if(resValue == 2){
					width = 1024;
					height = 768;
					localStorage.resizeOption = 2
				} else{
					if(resValue == 3){
						width = parseFloat($("[name=width]")[0].value);
						height = parseFloat($("[name=height]")[0].value);
						localStorage.resizeOption = 3
					}
				}
			}
		}
		localStorage.width = width;
		localStorage.height = height;
		chrome.windows.getCurrent(function (f) {
			background.resizeBack = true;
			background.currentWidth = f.width;
			background.currentHeight = f.height;
			background.currentWindow = f.id;
			console.log("h");
			console.log(f.id, {width:width, height:height}, e);
			console.log("h2");
			chrome.windows.update(f.id, {width:width, height:height}, e)
		})
	}

	function d(e) {
		$(".window").hide();
		$(".window[tag=" + e + "]").show()
	}
}
abc();
