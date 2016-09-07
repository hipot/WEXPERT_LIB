/**
 * Плагин для работы с выпадающими списками SELECT
 *
 * Примеры:
 * // очистка селекта (вариантов option)
 * $("select").clearSelect();
 *
 * // заполнение селекта (вариантами option)
 * $("select").fillSelect([
 * 	{name : '123_name', value : 123},
 * 	{name : '456_name', value : 456}
 * ]);
 *
 *
 * @version 1.0
 * @author weXpert, 17.10.2012
 */
jQuery.extend(jQuery.fn, {
	/** @memberOf jQuery */
	clearSelect: function(defaultOption) {
		return this.each(function(){
			if (this.tagName == 'SELECT') {
				this.options.length = 0;
				if (! defaultOption) {
					return;
				}
				if ($.support.cssFloat) {
					this.add(defaultOption, null);
				} else {
					this.add(defaultOption);
				}
			}
		});
	},
	/** @memberOf jQuery */
	fillSelect: function(dataArray, defaultOption) {
		return this.clearSelect(defaultOption).each(function(){
			if (this.tagName == 'SELECT') {
				var currentSelect = this;
				$.each(dataArray, function(index, data) {
					var option = new Option(data.name, data.value);
					if($.support.cssFloat) {
						currentSelect.add(option, null);
					} else {
						currentSelect.add(option);
					}
				});
			}
		});
	}
});