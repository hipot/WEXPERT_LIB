/**
 * Form maker framework
 * @version 2.0
 * @author weXpert, 2012
 */
$(function(){
	
	/**
	 * основной js-чекер всех форм
	 */
	var __rebornCheckers = function(){
		
		$(".ajax_form").each(function(){
			
			var __formId = this.id,
				__form	 = this;
			
			// проверка перед отправкой		
			$(".submit", __form).unbind('click').click(function(){
				
				var allow = true,
					__submitButton = this;			
				
				// непустота полей
				var var1 = $('.req_inpt', __form);			
				$(var1).each(function(){			
					if (isEmpty($(this).val()) 
						|| ($(this).hasClass('email_inpt') && !isMail($(this).val()))
					) { 
						$(this).addClass('fail_input');
						allow = false;					
					} else {
						$(this).removeClass('fail_input');
					}		
				});			
				
				if (! allow) {
					return false;
				}			
					
				// get all post data
				var js_data = {};			
				$('.ajax_form_wrapper', __form).wrap('<form></form>');			
				js_data = $('form', __form).serializeJSON();
							
				// lock click
				$(__submitButton).data('posted', true).fadeTo(0, 0.3);
				
				js_data['__form__'] = __formId.replace(/_form$/, "");			
				
				$.ajax({
					async: true,
					cache: false,
					data:  js_data,
					dataType: 'html',
					timeout: 8000,
					type: 'POST',
					url: '/bitrix/components/wexpert/request/ajax/request.php',				
					error: function(jqXHR, textStatus, errorThrown){					
						$(__submitButton).data('posted', false).fadeTo(0, 1);					
					},
					success: function(data, textStatus, jqXHR){
						var container = $(__form).parent();  					
						
						// удаляем скрипт шаблона формы и саму старую форму
						$('#' + __formId + ' ~ script').remove();
						$('#' + __formId).remove();
						
						$(container).append(data);	
						__rebornCheckers();
					}
				});
					
				
			});		
		});
	};
	// first run
	__rebornCheckers();	
	
});
