/**
Demo script to handle the theme demo
**/
var Demo = function() {

    // Handle Theme Settings
    var handleTheme = function() {

        var panel = jQuery('.theme-panel');

        if (jQuery('body').hasClass('page-boxed') === false) {
            jQuery('.layout-option', panel).val("fluid");
        }

        jQuery('.sidebar-option', panel).val("default");
        jQuery('.page-header-option', panel).val("fixed");
        jQuery('.page-footer-option', panel).val("default");
        if (jQuery('.sidebar-pos-option').attr("disabled") === false) {
            jQuery('.sidebar-pos-option', panel).val(Metronic.isRTL() ? 'right' : 'left');
        }

        //handle theme layout
        var resetLayout = function() {
            jQuery("body").
            removeClass("page-boxed").
            removeClass("page-footer-fixed").
            removeClass("page-sidebar-fixed").
            removeClass("page-header-fixed").
            removeClass("page-sidebar-reversed");

            jQuery('.page-header > .page-header-inner').removeClass("container");

            if (jQuery('.page-container').parent(".container").size() === 1) {
                jQuery('.page-container').insertAfter('body > .clearfix');
            }

            if (jQuery('.page-footer > .container').size() === 1) {
                jQuery('.page-footer').html(jQuery('.page-footer > .container').html());
            } else if (jQuery('.page-footer').parent(".container").size() === 1) {
                jQuery('.page-footer').insertAfter('.page-container');
                jQuery('.scroll-to-top').insertAfter('.page-footer');
            }

             jQuery(".top-menu > .navbar-nav > li.dropdown").removeClass("dropdown-dark");

            jQuery('body > .container').remove();
        };

        var lastSelectedLayout = '';

        var setLayout = function() {

            var layoutOption = jQuery('.layout-option', panel).val();
            var sidebarOption = jQuery('.sidebar-option', panel).val();
            var headerOption = jQuery('.page-header-option', panel).val();
            var footerOption = jQuery('.page-footer-option', panel).val();
            var sidebarPosOption = jQuery('.sidebar-pos-option', panel).val();
            var sidebarStyleOption = jQuery('.sidebar-style-option', panel).val();
            var sidebarMenuOption = jQuery('.sidebar-menu-option', panel).val();
            var headerTopDropdownStyle = jQuery('.page-header-top-dropdown-style-option', panel).val();

            if (sidebarOption == "fixed" && headerOption == "default") {
                alert('Default Header with Fixed Sidebar option is not supported. Proceed with Fixed Header with Fixed Sidebar.');
                jQuery('.page-header-option', panel).val("fixed");
                jQuery('.sidebar-option', panel).val("fixed");
                sidebarOption = 'fixed';
                headerOption = 'fixed';
            }

            resetLayout(); // reset layout to default state

            if (layoutOption === "boxed") {
                jQuery("body").addClass("page-boxed");

                // set header
                jQuery('.page-header > .page-header-inner').addClass("container");
                var cont = jQuery('body > .clearfix').after('<div class="container"></div>');

                // set content
                jQuery('.page-container').appendTo('body > .container');

                // set footer
                if (footerOption === 'fixed') {
                    jQuery('.page-footer').html('<div class="container">' + jQuery('.page-footer').html() + '</div>');
                } else {
                    jQuery('.page-footer').appendTo('body > .container');
                }
            }

            if (lastSelectedLayout != layoutOption) {
                //layout changed, run responsive handler: 
                Metronic.runResizeHandlers();
            }
            lastSelectedLayout = layoutOption;

            //header
            if (headerOption === 'fixed') {
                jQuery("body").addClass("page-header-fixed");
                jQuery(".page-header").removeClass("navbar-static-top").addClass("navbar-fixed-top");
            } else {
                jQuery("body").removeClass("page-header-fixed");
                jQuery(".page-header").removeClass("navbar-fixed-top").addClass("navbar-static-top");
            }

            //sidebar
            if (jQuery('body').hasClass('page-full-width') === false) {
                if (sidebarOption === 'fixed') {
                    jQuery("body").addClass("page-sidebar-fixed");
                    jQuery("page-sidebar-menu").addClass("page-sidebar-menu-fixed");
                    jQuery("page-sidebar-menu").removeClass("page-sidebar-menu-default");
                    Layout.initFixedSidebarHoverEffect();
                } else {
                    jQuery("body").removeClass("page-sidebar-fixed");
                    jQuery("page-sidebar-menu").addClass("page-sidebar-menu-default");
                    jQuery("page-sidebar-menu").removeClass("page-sidebar-menu-fixed");
                    jQuery('.page-sidebar-menu').unbind('mouseenter').unbind('mouseleave');
                }
            }

            // top dropdown style
            if (headerTopDropdownStyle === 'dark') {
                jQuery(".top-menu > .navbar-nav > li.dropdown").addClass("dropdown-dark");
            } else {
                jQuery(".top-menu > .navbar-nav > li.dropdown").removeClass("dropdown-dark");
            }

            //footer 
            if (footerOption === 'fixed') {
                jQuery("body").addClass("page-footer-fixed");
            } else {
                jQuery("body").removeClass("page-footer-fixed");
            }

            //sidebar style
            if (sidebarStyleOption === 'light') {
                jQuery(".page-sidebar-menu").addClass("page-sidebar-menu-light");
            } else {
                jQuery(".page-sidebar-menu").removeClass("page-sidebar-menu-light");
            }

            //sidebar menu 
            if (sidebarMenuOption === 'hover') {
                if (sidebarOption == 'fixed') {
                    jQuery('.sidebar-menu-option', panel).val("accordion");
                    alert("Hover Sidebar Menu is not compatible with Fixed Sidebar Mode. Select Default Sidebar Mode Instead.");
                } else {
                    jQuery(".page-sidebar-menu").addClass("page-sidebar-menu-hover-submenu");
                }
            } else {
                jQuery(".page-sidebar-menu").removeClass("page-sidebar-menu-hover-submenu");
            }

            //sidebar position
            if (Metronic.isRTL()) {
                if (sidebarPosOption === 'left') {
                    jQuery("body").addClass("page-sidebar-reversed");
                    jQuery('#frontend-link').tooltip('destroy').tooltip({
                        placement: 'right'
                    });
                } else {
                    jQuery("body").removeClass("page-sidebar-reversed");
                    jQuery('#frontend-link').tooltip('destroy').tooltip({
                        placement: 'left'
                    });
                }
            } else {
                if (sidebarPosOption === 'right') {
                    jQuery("body").addClass("page-sidebar-reversed");
                    jQuery('#frontend-link').tooltip('destroy').tooltip({
                        placement: 'left'
                    });
                } else {
                    jQuery("body").removeClass("page-sidebar-reversed");
                    jQuery('#frontend-link').tooltip('destroy').tooltip({
                        placement: 'right'
                    });
                }
            }

            Layout.fixContentHeight(); // fix content height            
            Layout.initFixedSidebar(); // reinitialize fixed sidebar
        };

        // handle theme colors
        var setColor = function(color) {
            var color_ = (Metronic.isRTL() ? color + '-rtl' : color);
            jQuery('#style_color').attr("href", Layout.getLayoutCssPath() + 'themes/' + color_ + ".css");
            if (color == 'light2') {
                jQuery('.page-logo img').attr('src', Layout.getLayoutImgPath() + 'logo-invert.png');
            } else {
                jQuery('.page-logo img').attr('src', Layout.getLayoutImgPath() + 'logo.png');
            }
        };

        jQuery('.toggler', panel).click(function() {
            jQuery('.toggler').hide();
            jQuery('.toggler-close').show();
            jQuery('.theme-panel > .theme-options').show();
        });

        jQuery('.toggler-close', panel).click(function() {
            jQuery('.toggler').show();
            jQuery('.toggler-close').hide();
            jQuery('.theme-panel > .theme-options').hide();
        });

        jQuery('.theme-colors > ul > li', panel).click(function() {
            var color = jQuery(this).attr("data-style");
            setColor(color);
            jQuery('ul > li', panel).removeClass("current");
            jQuery(this).addClass("current");
        });

        // set default theme options:

        if (jQuery("body").hasClass("page-boxed")) {
            jQuery('.layout-option', panel).val("boxed");
        }

        if (jQuery("body").hasClass("page-sidebar-fixed")) {
            jQuery('.sidebar-option', panel).val("fixed");
        }

        if (jQuery("body").hasClass("page-header-fixed")) {
            jQuery('.page-header-option', panel).val("fixed");
        }

        if (jQuery("body").hasClass("page-footer-fixed")) {
            jQuery('.page-footer-option', panel).val("fixed");
        }

        if (jQuery("body").hasClass("page-sidebar-reversed")) {
            jQuery('.sidebar-pos-option', panel).val("right");
        }

        if (jQuery(".page-sidebar-menu").hasClass("page-sidebar-menu-light")) {
            jQuery('.sidebar-style-option', panel).val("light");
        }

        if (jQuery(".page-sidebar-menu").hasClass("page-sidebar-menu-hover-submenu")) {
            jQuery('.sidebar-menu-option', panel).val("hover");
        }

        var sidebarOption = jQuery('.sidebar-option', panel).val();
        var headerOption = jQuery('.page-header-option', panel).val();
        var footerOption = jQuery('.page-footer-option', panel).val();
        var sidebarPosOption = jQuery('.sidebar-pos-option', panel).val();
        var sidebarStyleOption = jQuery('.sidebar-style-option', panel).val();
        var sidebarMenuOption = jQuery('.sidebar-menu-option', panel).val();

        jQuery('.layout-option, .page-header-option, .page-header-top-dropdown-style-option, .sidebar-option, .page-footer-option, .sidebar-pos-option, .sidebar-style-option, .sidebar-menu-option', panel).change(setLayout);
    };

    // handle theme style
    var setThemeStyle = function(style) {
        var file = (style === 'rounded' ? 'components-rounded' : 'components');
        file = (Metronic.isRTL() ? file + '-rtl' : file);

        jQuery('#style_components').attr("href", Metronic.getGlobalCssPath() + file + ".css");

        if ($.cookie) {
            $.cookie('layout-style-option', style);
        }
    };

    return {

        //main function to initiate the theme
        init: function() {
            // handles style customer tool
            handleTheme(); 
            
            // handle layout style change
            jQuery('.theme-panel .layout-style-option').change(function() {
                 setThemeStyle(jQuery(this).val());
            });

            // set layout style from cookie
            if ($.cookie && $.cookie('layout-style-option') === 'rounded') {
                setThemeStyle($.cookie('layout-style-option'));
                jQuery('.theme-panel .layout-style-option').val($.cookie('layout-style-option'));
            }            
        }
    };

}();