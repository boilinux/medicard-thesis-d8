jQuery(function($) {
	$(document).ready(function() {
		var username = drupalSettings.custom_general.custom_general_script.username;
		var patient_id = drupalSettings.custom_general.custom_general_script.patient_id;
		var addmed = $('#add_medicine');
		var reset = $('#reset_medicine');
		var pharmatext = $('#edit-pharmacomment');
		var prescription = $('#edit-prescription');
		var desc = $('ul.desc-medicine li');
		var temp_med = $('div.med-list ul');
		var comment = $('#edit-medicine-comment');

		// hide desc
		desc.hide();
		pharmatext.attr('readonly','readonly');
		prescription.attr('readonly','readonly');
		prescription.hide();

		$('ul.desc-medicine li.' + $('#edit-medicine').val()).show();

		// Docotr's prescription
		addmed.click(function(e) {
			e.preventDefault();

			var med = $('#edit-medicine option:selected').text();
			var quantity = $('#edit-medicine-quantity').val();

			if ($.isNumeric(quantity) && quantity > 0) {
				// display med temp
				var nid = $('#edit-medicine').val().split("-");

				var existing_li = $('div.med-list ul li.med-' + nid[1]);
				if (existing_li.length) {
					existing_li.attr({
						'data-quantity': quantity,
						'data-comment': comment.val(),
					});
					existing_li.text(med + " - " + quantity + " " + comment.val());
				}
				else {
					temp_med.append("<li class='med-" + nid[1] + "' data-nid='" + nid[1] + "' data-quantity='" + quantity + "' data-comment='" + comment.val() + "'>" + med + " - " + quantity + " " + comment.val() + "</li>");
				}

				// add to prescription textarea
				var data_list = $('div.med-list ul li');
				if (data_list.length) {
					var val = [];

					data_list.each(function() {
						var nid2 = $(this).attr('data-nid');
						var quantity2 = $(this).attr('data-quantity');
						var comment2 = $(this).attr('data-comment');

						val.push({
							'patient_nid': patient_id,
							'med_nid': nid2,
							'quantity': quantity2,
							'comment': comment2,
							'user': username,
							'status': 0,
							'acquire': 0,
						});
					});

					prescription.val(JSON.stringify(val));
				}
			}
			else {
				alert("Quantiy is invalid!");
			}

		});

		$('#edit-medicine').change(function() {
			desc.hide();

			$('ul.desc-medicine li.' + $(this).val()).show();
		});

		reset.click(function(e) {
			e.preventDefault();

			prescription.text("");
			temp_med.text("");
		});
	});
});
