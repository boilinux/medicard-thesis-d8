jQuery(function($) {
	$(document).ready(function() {
		var addmed = $('#add_medicine');
		var reset = $('#reset_medicine');
		var pharmatext = $('#edit-pharmacomment');
		var desc = $('ul.desc-medicine li');

		// hide desc
		desc.hide();
		pharmatext.attr('readonly','readonly');

		$('ul.desc-medicine li.' . $('#edit-medicine option:selected').val()).show();

		addmed.click(function(e) {
			e.preventDefault();

			desc.hide();

			var med = $('#edit-medicine option:selected').val();
			var quantity = $('#edit-medicine-quantity').val();

			if ($.isNumeric(quantity)) {
				pharmatext.append(med + " - " + quantity + "\n");
			}
			else {
				alert("Quantiy is invalid!");
			}

			$('ul.desc-medicine li.' . med).show();

		});

		reset.click(function(e) {
			e.preventDefault();

			pharmatext.text("");
		});
	});
});
