(function($) {
	function validate_email(email) {
		// contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
		return  /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(email);
	}
	function validate_required(value) {
		return $.trim(value).length > 0
	}

	$.fn.extend({
		forms: null,
		wlmStripeForm: function(options) {
			var defaults = {};
			var options =  $.extend(defaults, options);
			var elements = $(this);
			var buttons = elements.find('button');
			var self = this;
			this.forms = elements;

			buttons.click(function(ev) {
				$(this).prop('disabled', true);
				i = buttons.index($(this));
				stripe_frm = self.forms.eq(i);

				fields = {
					card_number: stripe_frm.find('.stripe-field-cardnumber'),
					cvc: stripe_frm.find('.stripe-field-cvc'),
					exp_month: stripe_frm.find('.stripe-field-expmonth'),
					exp_year: stripe_frm.find('.stripe-field-expyear'),
					email: stripe_frm.find('.stripe-field-email'),
					first_name: stripe_frm.find('.stripe-field-first_name'),
					last_name: stripe_frm.find('.stripe-field-last_name')
				};

				var status = validate_fields(fields);

				if(status === true) {
					//prevent multiple
					stripe_frm.find('.stripe-waiting').show();
					
					Stripe.card.createToken({
						number: fields.card_number.val(),
						cvc: fields.cvc.val(),
						exp_month: fields.exp_month.val(),
						exp_year: fields.exp_year.val(),
						name: fields.first_name.val() + " " + fields.last_name.val()
					}, function(status, response) {
						stripe_frm = $(this.stripe_frm);
						if (response.error) {
							// show the errors on the form
							stripe_frm.find('.stripe-error').html(response.error.message);
							stripe_frm.find('.stripe-button').prop("disabled", false);
							stripe_frm.find('.stripe-waiting').hide();
						} else {
							var token = response['id'];
							// insert the token into the form so it gets submitted to the server
							stripe_frm.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
							stripe_frm.submit();
						}
					});
				} else {
					stripe_frm.find('.stripe-button').prop("disabled", false);
				}
				return false;
			});
		}
	});

	function validate_fields(fields) {
		var all_status = true;

		var status = validate_required(fields.first_name.val());
		all_status = status && all_status;
		if(status === true) {
			fields.first_name.removeClass("error_input");
		} else {
			fields.first_name.addClass("error_input");
		}

		var status = validate_required(fields.last_name.val());
		all_status = status && all_status;
		if(status === true) {
			fields.last_name.removeClass("error_input");
		} else {
			fields.last_name.addClass("error_input");
		}

		status = validate_email(fields.email.val());
		all_status = status && all_status;
		if(status === true) {
			fields.email.removeClass("error_input");
		} else {
			fields.email.addClass("error_input");
		}


		status = Stripe.validateCardNumber(fields.card_number.val());
		all_status = status && all_status;
		if(status === true) {
			fields.card_number.removeClass("error_input");
		} else {
			fields.card_number.addClass("error_input");
		}

		status = Stripe.validateExpiry(fields.exp_month.val(), "20" + fields.exp_year.val());
		all_status = status && all_status;
		if(status === true) {
			fields.exp_month.removeClass("error_input");
			fields.exp_year.removeClass("error_input");
		} else {
			fields.exp_month.addClass("error_input");
			fields.exp_year.addClass("error_input");
		}

		status = Stripe.validateCVC(fields.cvc.val());
		all_status = status && all_status;
		if(status === true) {
			fields.cvc.removeClass("error_input");
		} else {
			fields.cvc.addClass("error_input");
		}
		return all_status;
	}
})(jQuery);