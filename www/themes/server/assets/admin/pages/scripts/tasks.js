var Tasks = function () {


    return {

        //main function to initiate the module
        initDashboardWidget: function () {
			jQuery('.task-list input[type="checkbox"]').change(function() {
				if (jQuery(this).is(':checked')) { 
					jQuery(this).parents('li').addClass("task-done"); 
				} else { 
					jQuery(this).parents('li').removeClass("task-done"); 
				}
			}); 
        }

    };

}();