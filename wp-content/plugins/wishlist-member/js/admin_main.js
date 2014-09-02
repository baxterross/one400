jQuery(document).ready(function($) {
	//for loading wlm feeds on dashboard
	jQuery(function($) {
		data = {
			action: 'wlm_feeds'
		}
		$.ajax({
			type: 'POST',
			url: admin_main_js.wlm_feed_url,
			data: data,
			success: function(response) {
				if($.trim(response) != ""){
					$('.wlrss-widget').html(response);
					$('#wlrss-postbox').show();
				}
			}
		});
	});

});