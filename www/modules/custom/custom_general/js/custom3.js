jQuery(function($) {
	$(document).ready(function() {
		var pharma_form = $('form.pharmacist-comment-form');
		var data = drupalSettings.custom_general.custom_general_script2.data;
		var med = $('.medicine-textfield');
		var pharma_content = $('#edit-pharmacomment');
		var pharma_content2 = $('#edit-pharmacomment2');

		pharma_content.attr('readonly','readonly');
		pharma_content2.attr('readonly','readonly');

		pharma_content.hide();
		pharma_content2.hide();

		pharma_form.submit(function(e) {

			med.each(function() {
				var med_id = $(this).attr('data-nid');
				var acquire = parseInt($(this).val());
				var title = $(this).attr('data-title');

				for(var i in data) {

					data2 = data[i]['patient'];
					for(var i2 in data2) {
						if (data2[i2].med_nid == med_id) {
							if (Math.abs((acquire + parseInt(data2[i2].acquire))) <= parseInt(data2[i2].quantity)) {
								var new_acquire = parseInt(acquire) + parseInt(data2[i2].acquire);

								data[i]['patient'][i2].acquire = new_acquire;

								pharma_content2.val("[" + JSON.stringify(data[i]) + "]");

								var insert_text = title + " - " + acquire;

								if (data2[i2].quantity > new_acquire) {
									var out_of = parseInt(data2[i2].quantity) - parseInt(new_acquire);
									insert_text += " out of " + out_of;
								}

								pharma_content.append(insert_text);
							}
							else {
								alert(title + " Invalid input!");

								e.preventDefault();
							}
						}
					}
				}
			});
		});
	});
});
