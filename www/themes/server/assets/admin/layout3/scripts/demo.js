/**
Demo script to handle the theme demo
**/
var Demo = function () {

    // Handle Theme Settings
    var handleTheme = function () {

        var panel = jQuery('.theme-panel');

        if (jQuery('.page-head > .container-fluid').size() === 1) {
            jQuery('.theme-setting-layout', panel).val("fluid");
        } else {
            jQuery('.theme-setting-layout', panel).val("boxed");
        }

        if (jQuery('.top-menu li.dropdown.dropdown-dark').size() > 0) {
            jQuery('.theme-setting-top-menu-style', panel).val("dark");
        } else {
            jQuery('.theme-setting-top-menu-style', panel).val("light");
        }

        if (jQuery('body').hasClass("page-header-top-fixed")) {
            jQuery('.theme-setting-top-menu-mode', panel).val("fixed");
        } else {
            jQuery('.theme-setting-top-menu-mode', panel).val("not-fixed");
        }

        if (jQuery('.hor-menu.hor-menu-light').size() > 0) {
            jQuery('.theme-setting-mega-menu-style', panel).val("light");
        } else {
            jQuery('.theme-setting-mega-menu-style', panel).val("dark");
        }

        if (jQuery('body').hasClass("page-header-menu-fixed")) {
            jQuery('.theme-setting-mega-menu-mode', panel).val("fixed");
        } else {
            jQuery('.theme-setting-mega-menu-mode', panel).val("not-fixed");
        }

        //handle theme layout
        var resetLayout = function () {
            jQuery("body").
            removeClass("page-header-top-fixed").
            removeClass("page-header-menu-fixed");

            jQuery('.page-header-top > .container-fluid').removeClass("container-fluid").addClass('container');
            jQuery('.page-header-menu > .container-fluid').removeClass("container-fluid").addClass('container');
            jQuery('.page-head > .container-fluid').removeClass("container-fluid").addClass('container');
            jQuery('.page-content > .container-fluid').removeClass("container-fluid").addClass('container');
            jQuery('.page-prefooter > .container-fluid').removeClass("container-fluid").addClass('container');
            jQuery('.page-footer > .container-fluid').removeClass("container-fluid").addClass('container');              
        };

        var setLayout = function () {

            var layoutMode = jQuery('.theme-setting-layout', panel).val();
            var headerTopMenuStyle = jQuery('.theme-setting-top-menu-style', panel).val();
            var headerTopMenuMode = jQuery('.theme-setting-top-menu-mode', panel).val();
            var headerMegaMenuStyle = jQuery('.theme-setting-mega-menu-style', panel).val();
            var headerMegaMenuMode = jQuery('.theme-setting-mega-menu-mode', panel).val();
            
            resetLayout(); // reset layout to default state

            if (layoutMode === "fluid") {
                jQuery('.page-header-top > .container').removeClass("container").addClass('container-fluid');
                jQuery('.page-header-menu > .container').removeClass("container").addClass('container-fluid');
                jQuery('.page-head > .container').removeClass("container").addClass('container-fluid');
                jQuery('.page-content > .container').removeClass("container").addClass('container-fluid');
                jQuery('.page-prefooter > .container').removeClass("container").addClass('container-fluid');
                jQuery('.page-footer > .container').removeClass("container").addClass('container-fluid');

                //Metronic.runResizeHandlers();
            }

            if (headerTopMenuStyle === 'dark') {
                jQuery(".top-menu > .navbar-nav > li.dropdown").addClass("dropdown-dark");
            } else {
                jQuery(".top-menu > .navbar-nav > li.dropdown").removeClass("dropdown-dark");
            }

            if (headerTopMenuMode === 'fixed') {
                jQuery("body").addClass("page-header-top-fixed");
            } else {
                jQuery("body").removeClass("page-header-top-fixed");
            }

            if (headerMegaMenuStyle === 'light') {
                jQuery(".hor-menu").addClass("hor-menu-light");
            } else {
                jQuery(".hor-menu").removeClass("hor-menu-light");
            }

            if (headerMegaMenuMode === 'fixed') {
                jQuery("body").addClass("page-header-menu-fixed");
            } else {
                jQuery("body").removeClass("page-header-menu-fixed");
            }          
        };

        // handle theme colors
        var setColor = function (color) {
            var color_ = (Metronic.isRTL() ? color + '-rtl' : color);
            jQuery('#style_color').attr("href", Layout.getLayoutCssPath() + 'themes/' + color_ + ".css");
            jQuery('.page-logo img').attr("src", Layout.getLayoutImgPath() + 'logo-' + color + '.png');
        };

        jQuery('.theme-colors > li', panel).click(function () {
            var color = jQuery(this).attr("data-theme");
            setColor(color);
            jQuery('.theme-colors > li', panel).removeClass("active");
            jQuery(this).addClass("active");
        });

        jQuery('.theme-setting-top-menu-mode', panel).change(function(){
            var headerTopMenuMode = jQuery('.theme-setting-top-menu-mode', panel).val();
            var headerMegaMenuMode = jQuery('.theme-setting-mega-menu-mode', panel).val();            

            if (headerMegaMenuMode === "fixed") {
                alert("The top menu and mega menu can not be fixed at the same time.");
                jQuery('.theme-setting-mega-menu-mode', panel).val("not-fixed");   
                headerTopMenuMode = 'not-fixed';
            }                
        });

        jQuery('.theme-setting-mega-menu-mode', panel).change(function(){
            var headerTopMenuMode = jQuery('.theme-setting-top-menu-mode', panel).val();
            var headerMegaMenuMode = jQuery('.theme-setting-mega-menu-mode', panel).val();            

            if (headerTopMenuMode === "fixed") {
                alert("The top menu and mega menu can not be fixed at the same time.");
                jQuery('.theme-setting-top-menu-mode', panel).val("not-fixed");   
                headerTopMenuMode = 'not-fixed';
            }                
        });

        jQuery('.theme-setting', panel).change(setLayout);

        jQuery('.theme-setting-layout', panel).change(function(){
            Index.redrawCharts();  // reload the chart on layout width change
        });
    };

    // handle theme style
    var setThemeStyle = function(style) {
        var file = (style === 'rounded' ? 'components-rounded' : 'components');
        file = (Metronic.isRTL() ? file + '-rtl' : file);

        jQuery('#style_components').attr("href", Metronic.getGlobalCssPath() + file + ".css");

        if (jQuery.cookie) {
            jQuery.cookie('layout-style-option', style);
        }


    };

    return {

        //main function to initiate the theme
        init: function() {
            // handles style customer tool
            handleTheme(); 

            // handle layout style change
            jQuery('.theme-panel .theme-setting-style').change(function() {
                 setThemeStyle(jQuery(this).val());
            });

            // set layout style from cookie
            if (jQuery.cookie && jQuery.cookie('layout-style-option') === 'rounded') {
                setThemeStyle(jQuery.cookie('layout-style-option'));  
                jQuery('.theme-panel .theme-setting-style').val(jQuery.cookie('layout-style-option'));
            }            
        }
    };

}();