var UIToastr = function () {

    return {
        //main function to initiate the module
        init: function () {

            var i = -1,
                toastCount = 0,
                jQuerytoastlast,
                getMessage = function () {
                    var msgs = ['Hello, some notification sample goes here',
                        '<div><input class="form-control input-small" value="textbox"/>&nbsp;<a href="http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank">Check this out</a></div><div><button type="button" id="okBtn" class="btn blue">Close me</button><button type="button" id="surpriseBtn" class="btn default" style="margin: 0 8px 0 8px">Surprise me</button></div>',
                        'Did you like this one ? :)',
                        'Totally Awesome!!!',
                        'Yeah, this is the Metronic!',
                        'Explore the power of Metronic. Purchase it now!'
                    ];
                    i++;
                    if (i === msgs.length) {
                        i = 0;
                    }

                    return msgs[i];
                };

            jQuery('#showtoast').click(function () {
                var shortCutFunction = jQuery("#toastTypeGroup input:checked").val();
                var msg = jQuery('#message').val();
                var title = jQuery('#title').val() || '';
                var jQueryshowDuration = jQuery('#showDuration');
                var jQueryhideDuration = jQuery('#hideDuration');
                var jQuerytimeOut = jQuery('#timeOut');
                var jQueryextendedTimeOut = jQuery('#extendedTimeOut');
                var jQueryshowEasing = jQuery('#showEasing');
                var jQueryhideEasing = jQuery('#hideEasing');
                var jQueryshowMethod = jQuery('#showMethod');
                var jQueryhideMethod = jQuery('#hideMethod');
                var toastIndex = toastCount++;

                toastr.options = {
                    closeButton: jQuery('#closeButton').prop('checked'),
                    debug: jQuery('#debugInfo').prop('checked'),
                    positionClass: jQuery('#positionGroup input:checked').val() || 'toast-top-right',
                    onclick: null
                };

                if (jQuery('#addBehaviorOnToastClick').prop('checked')) {
                    toastr.options.onclick = function () {
                        alert('You can perform some custom action after a toast goes away');
                    };
                }

                if (jQueryshowDuration.val().length) {
                    toastr.options.showDuration = jQueryshowDuration.val();
                }

                if (jQueryhideDuration.val().length) {
                    toastr.options.hideDuration = jQueryhideDuration.val();
                }

                if (jQuerytimeOut.val().length) {
                    toastr.options.timeOut = jQuerytimeOut.val();
                }

                if (jQueryextendedTimeOut.val().length) {
                    toastr.options.extendedTimeOut = jQueryextendedTimeOut.val();
                }

                if (jQueryshowEasing.val().length) {
                    toastr.options.showEasing = jQueryshowEasing.val();
                }

                if (jQueryhideEasing.val().length) {
                    toastr.options.hideEasing = jQueryhideEasing.val();
                }

                if (jQueryshowMethod.val().length) {
                    toastr.options.showMethod = jQueryshowMethod.val();
                }

                if (jQueryhideMethod.val().length) {
                    toastr.options.hideMethod = jQueryhideMethod.val();
                }

                if (!msg) {
                    msg = getMessage();
                }

                jQuery("#toastrOptions").text("Command: toastr[" + shortCutFunction + "](\"" + msg + (title ? "\", \"" + title : '') + "\")\n\ntoastr.options = " + JSON.stringify(toastr.options, null, 2));

                var jQuerytoast = toastr[shortCutFunction](msg, title); // Wire up an event handler to a button in the toast, if it exists
                jQuerytoastlast = jQuerytoast;
                if (jQuerytoast.find('#okBtn').length) {
                    jQuerytoast.delegate('#okBtn', 'click', function () {
                        alert('you clicked me. i was toast #' + toastIndex + '. goodbye!');
                        jQuerytoast.remove();
                    });
                }
                if (jQuerytoast.find('#surpriseBtn').length) {
                    jQuerytoast.delegate('#surpriseBtn', 'click', function () {
                        alert('Surprise! you clicked me. i was toast #' + toastIndex + '. You could perform an action here.');
                    });
                }

                jQuery('#clearlasttoast').click(function () {
                    toastr.clear(jQuerytoastlast);
                });
            });
            jQuery('#cleartoasts').click(function () {
                toastr.clear();
            });

        }

    };

}();