var cooker = {
	set: function(cookie_name, cookie_value, cookie_expires, cookie_path, cookie_domain, cookie_secure) {
		if(cookie_name!==undefined) {
			cookie_expires=cookie_expires || 0;
			var expire_date = new Date;
			expire_date.setTime(expire_date.getTime() + (cookie_expires*1000));
			document.cookie = cookie_name + "=" + escape(cookie_value)+'; ' +
			((cookie_expires === undefined) ? '' : 'expires=' + expire_date.toGMTString()+'; ') +
			((cookie_path === undefined) ? 'path=/;' : 'path='+cookie_path+'; ') +
			((cookie_domain === undefined) ? '' : 'domain='+cookie_domain+'; ') +
			((cookie_secure === true) ? 'secure; ' : '');
		}
	},
	get: function(cookie_name) {
		var cookie = document.cookie, length = cookie.length;
		if(length) {
			var cookie_start = cookie.indexOf(cookie_name + '=');
			if(cookie_start != -1) {
				var cookie_end = cookie.indexOf(';', cookie_start);
				if(cookie_end == -1) {
					cookie_end = length;
				}
				cookie_start += cookie_name.length + 1;
				return unescape(cookie.substring(cookie_start, cookie_end));
			}
		}
	},
	erase: function(cookie_name) {
		cooker.set(cookie_name, '', -1);
	},
	test: function() {
		cooker.set('test_cookie', 'test', 10);
		var work = (cooker.get('test_cookie') === 'test') ? true : false;
		cooker.erase('test_cookie');
		return work;
	}
};
