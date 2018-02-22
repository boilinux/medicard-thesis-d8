jQuery(function($) {
	$(document).ready(function() {
		var addmed = $('#add_medicine');
		var reset = $('#reset_medicine');
		var pharmatext = $('#edit-pharmacomment');

		pharmatext.attr('readonly','readonly');
		// pharmatext.hide();

		addmed.click(function(e) {
			e.preventDefault();

			var med = $('#edit-medicine option:selected').val();
			var quantity = $('#edit-medicine-quantity').val();

			if ($.isNumeric(quantity)) {
				pharmatext.append(med + " - " + quantity + "\n");
			}
			else {
				alert("Quantiy is invalid!");
			}

		});

		reset.click(function(e) {
			e.preventDefault();

			pharmatext.text("");
		});
	});
});
