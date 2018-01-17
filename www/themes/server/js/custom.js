(function($) {
	$(document).ready(function($) {
		var registerpage = $('body.login form#user-register-form');

		if (!$('form#add-loan-form').length) {
			$('div.form-item').each(function() {
				$(this).find('input').attr('placeholder', $(this).find('label').text());
			});
		}

		$('form div.form-actions button').addClass('btn btn-success uppercase');

		if (registerpage.length) {
			var div_account = $('div.create-account');
			div_account.find('a').attr('href', '/user/login').text('Sign In');
		}

		$('ul.menu li').each(function() {
			$(this).find('a.is-active').parent('li').addClass('active');
		});

		var custom_msg = $('.custom_message');

		if (custom_msg.length) {
			toastr.options = {
        "closeButton": false,
        "debug": false,
        "positionClass": "toast-top-full-width",
        "onclick": null,
        "showDuration": "1000",
        "hideDuration": "1000",
        "timeOut": "20000",
        "extendedTimeOut": "20000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      };

			toastr[custom_msg.attr('data-notice')](custom_msg.text(), "System message");
		}
		$('div.doctor-box div.portlet-body').each(function() {
			$(this).attr('style', 'display:none;');
			$(this).removeClass('hide');
		});
		$('div.doctor-box div.actions a').click(function() {
			var box = $('div#' + $(this).attr('data-name'));
			if (box.hasClass('hide2')) {
				box.slideDown('slow');
				box.removeClass('hide2');
			}
			else {
				box.slideUp('slow');
				box.addClass('hide2');
			}
		});
	});
})(jQuery);