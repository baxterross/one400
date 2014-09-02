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
		options: null,
		PopupRegForm: function(options) {
			var defaults = {
				validate_cvc: true,
				validate_email: true,
				validate_first_name: true,
				validate_last_name: true,
			};
			var options  =  $.extend(defaults, options);
			var elements = $(this);
			var buttons  = elements.find('button');
			var self     = this;
			this.forms   = elements;
			this.options = options;

			buttons.click(function(ev) {
				$(this).attr('disabled', 'disabled');
				i = buttons.index($(this));
				submit_frm = self.forms.eq(i);

				fields = {
					card_number: submit_frm.find('.regform-cardnumber'),
					cvc: submit_frm.find('.regform-cvc'),
					exp_month: submit_frm.find('.regform-expmonth'),
					exp_year: submit_frm.find('.regform-expyear'),
					email: submit_frm.find('.regform-email'),
					first_name: submit_frm.find('.regform-first_name'),
					last_name: submit_frm.find('.regform-last_name')
				};

				var status = self.validate_fields(fields);

				if(status === true) {
					//prevent multiple
					submit_frm.find('.regform-waiting').show();
					submit_frm.submit();
				} else {
					submit_frm.find('.regform-button').removeAttr('disabled');
				}
				return false;
			});
		},
		validate_fields: function(fields) {
			var all_status = true;
			if(this.options.validate_first_name) {
				var status = validate_required(fields.first_name.val());
				all_status = status && all_status;
				if(status === true) {
					fields.first_name.removeClass("error_input");
				} else {
					fields.first_name.addClass("error_input");
				}
			}

			if(this.options.validate_last_name) {
				var status = validate_required(fields.last_name.val());
				all_status = status && all_status;
				if(status === true) {
					fields.last_name.removeClass("error_input");
				} else {
					fields.last_name.addClass("error_input");
				}
			}

			if(this.options.validate_first_name) {
				status = validate_email(fields.email.val());
				all_status = status && all_status;
				if(status === true) {
					fields.email.removeClass("error_input");
				} else {
					fields.email.addClass("error_input");
				}
			}

			status = Stripe.card.validateCardNumber(fields.card_number.val());
			all_status = status && all_status;
			if(status === true) {
				fields.card_number.removeClass("error_input");
			} else {
				fields.card_number.addClass("error_input");
			}

			status = Stripe.card.validateExpiry(fields.exp_month.val(), "20" + fields.exp_year.val());
			all_status = status && all_status;
			if(status === true) {
				fields.exp_month.removeClass("error_input");
				fields.exp_year.removeClass("error_input");
			} else {
				fields.exp_month.addClass("error_input");
				fields.exp_year.addClass("error_input");
			}

			if(this.options.validate_cvc) {
				status = Stripe.card.validateCVC(fields.cvc.val());
				all_status = status && all_status;
				if(status === true) {
					fields.cvc.removeClass("error_input");
				} else {
					fields.cvc.addClass("error_input");
				}
			}



			return all_status;
		}

	});


})(jQuery);


//application
jQuery(function($) {
	$('.go-regform').leanModal({
		closeButton: ".regform-close"
	});

	hash = $(location).attr('hash');
	if(hash.length > 0) {
		if($(hash).length > 0) {
			hash = hash.replace('#', '');
			$('#go-'+hash).click();
		}
	}

	$('.regform-open-login').live('click', function(ev) {
		ev.preventDefault();

		$('.regform-login').show();
		$('.regform-new').hide('slow')
	});

	$('.regform-close-login').live('click', function(ev) {
		ev.preventDefault();
		$('.regform-new').show('slow', function() {
			$('.regform-login').hide();
		});
	});

	var dots = window.setInterval( function() {
		$('.regform-waiting').each(function(i, e) {
			var el = $(this);
			if(el.html().length > 3) {
				el.html(".");
			} else {
				el.html(el.html() + ".");
			}
		});
	}, 300);
});
