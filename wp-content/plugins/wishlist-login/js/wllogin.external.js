jQuery(document).ready(function($){
	//Variables
	var trigger = $('.wl-login-pop a'),
		container = $('#wl_login_popup_container'),
		inner = $('#wl_login_popup_inner'),
		close = $('span.wl_login_popup_close');

		//Show popup
		trigger.click(function(){
			container.show();
			inner.show(400);

			return false;
		});

		//Hide popup on X click
		close.click(function(){
			inner.hide(400,function(){
				container.hide();
			});
		});

		//Hide popup on click outside div
		$('html').click(function(){
			inner.hide(400,function(){
				container.hide();
			});
		});

		//Keep a inner popup click from closing div
		inner.click(function(e){
			e.stopPropagation();
		});

		$('.wllogin2_form').find('form').on('submit', function(ev) {
			var el = $(this);
			$(this).find('input[name=redirect_to]').attr('name', 'wlm_redirect_to');
		});
});