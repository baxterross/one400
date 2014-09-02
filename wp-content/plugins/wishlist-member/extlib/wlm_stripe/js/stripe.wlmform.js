jQuery(function($) {
	$('.go-stripe-signup').leanModal({
		closeButton: ".stripe-close"
	});
	$('.stripe-form').wlmStripeForm();

	hash = $(location).attr('hash');
	if(hash.length > 0) {
		hash = hash.replace('#', '');
		if($('#stripe-signup-'+hash).length > 0) {
			$('#go-stripe-signup-' + hash).click();
		}
	}

	$('.stripe-open-login').live('click', function(ev) {
		ev.preventDefault();

		$('.stripe-login').show();
		$('.stripe-form-new').hide('slow')
	});

	$('.stripe-close-login').live('click', function(ev) {
		ev.preventDefault();
		$('.stripe-form-new').show('slow', function() {
			$('.stripe-login').hide();
		});
	});

	var dots = window.setInterval( function() {
		$('.stripe-waiting').each(function(i, e) {
			var el = $(this);
			if(el.html().length > 3) {
				el.html(".");
			} else {
				el.html(el.html() + ".");
			}
		});
	}, 300);
});