/**
Core script to handle the entire theme and core functions
**/
var Layout = function () {

    var layoutImgPath = 'admin/layout3/img/';

    var layoutCssPath = 'admin/layout3/css/';

    var resBreakpointMd = Metronic.getResponsiveBreakpoint('md');

    //* BEGIN:CORE HANDLERS *//
    // this function handles responsive layout on screen size resize or mobile device rotate.

    // Handles header
    var handleHeader = function () {        
        // handle search box expand/collapse        
        jQuery('.page-header').on('click', '.search-form', function (e) {
            jQuery(this).addClass("open");
            jQuery(this).find('.form-control').focus();

            jQuery('.page-header .search-form .form-control').on('blur', function (e) {
                jQuery(this).closest('.search-form').removeClass("open");
                jQuery(this).unbind("blur");
            });
        });

        // handle hor menu search form on enter press
        jQuery('.page-header').on('keypress', '.hor-menu .search-form .form-control', function (e) {
            if (e.which == 13) {
                jQuery(this).closest('.search-form').submit();
                return false;
            }
        });

        // handle header search button click
        jQuery('.page-header').on('mousedown', '.search-form.open .submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery(this).closest('.search-form').submit();
        });

        // handle scrolling to top on responsive menu toggler click when header is fixed for mobile view
        jQuery('body').on('click', '.page-header-top-fixed .page-header-top .menu-toggler', function(){
            Metronic.scrollTop();
        });     
    };

    // Handles main menu
    var handleMainMenu = function () {

        // handle menu toggler icon click
        jQuery(".page-header .menu-toggler").on("click", function(event) {
            if (Metronic.getViewPort().width < resBreakpointMd) {
                var menu = jQuery(".page-header .page-header-menu");
                if (menu.is(":visible")) {
                    menu.slideUp(300);
                } else {  
                    menu.slideDown(300);
                }

                if (jQuery('body').hasClass('page-header-top-fixed')) {
                    Metronic.scrollTop();
                }
            }
        });

        // handle sub dropdown menu click for mobile devices only
        jQuery(".hor-menu .dropdown-submenu > a").on("click", function(e) {
            if (Metronic.getViewPort().width < resBreakpointMd) {
                if (jQuery(this).next().hasClass('dropdown-menu')) {
                    e.stopPropagation();
                    if (jQuery(this).parent().hasClass("open")) {
                        jQuery(this).parent().removeClass("open");
                        jQuery(this).next().hide();
                    } else {
                        jQuery(this).parent().addClass("open");
                        jQuery(this).next().show();
                    }
                }
            }
        });

        // handle hover dropdown menu for desktop devices only
        if (Metronic.getViewPort().width >= resBreakpointMd) {
            jQuery('.hor-menu [data-hover="megamenu-dropdown"]').not('.hover-initialized').each(function() {   
                jQuery(this).dropdownHover(); 
                jQuery(this).addClass('hover-initialized'); 
            });
        } 

        // handle auto scroll to selected sub menu node on mobile devices
        jQuery(document).on('click', '.hor-menu .menu-dropdown > a[data-hover="megamenu-dropdown"]', function() {
            if (Metronic.getViewPort().width < resBreakpointMd) {
                Metronic.scrollTo(jQuery(this));
            }
        });

        // hold mega menu content open on click/tap. 
        jQuery(document).on('click', '.mega-menu-dropdown .dropdown-menu, .classic-menu-dropdown .dropdown-menu', function (e) {
            e.stopPropagation();
        });

        // handle fixed mega menu(minimized) 
        jQuery(window).scroll(function() {                
            var offset = 75;
            if (jQuery('body').hasClass('page-header-menu-fixed')) {
                if (jQuery(window).scrollTop() > offset){
                    jQuery(".page-header-menu").addClass("fixed");
                } else {
                    jQuery(".page-header-menu").removeClass("fixed");  
                }
            }

            if (jQuery('body').hasClass('page-header-top-fixed')) {
                if (jQuery(window).scrollTop() > offset){
                    jQuery(".page-header-top").addClass("fixed");
                } else {
                    jQuery(".page-header-top").removeClass("fixed");  
                }
            }
        });
    };

    // Handle sidebar menu links
    var handleMainMenuActiveLink = function(mode, el) {
        var url = location.hash.toLowerCase();    

        var menu = jQuery('.hor-menu');

        if (mode === 'click' || mode === 'set') {
            el = jQuery(el);
        } else if (mode === 'match') {
            menu.find("li > a").each(function() {
                var path = jQuery(this).attr("href").toLowerCase();       
                // url match condition         
                if (path.length > 1 && url.substr(1, path.length - 1) == path.substr(1)) {
                    el = jQuery(this);
                    return; 
                }
            });
        }

        if (!el || el.size() == 0) {
            return;
        }

        if (el.attr('href').toLowerCase() === 'javascript:;' || el.attr('href').toLowerCase() === '#') {
            return;
        }        

        // disable active states
        menu.find('li.active').removeClass('active');
        menu.find('li > a > .selected').remove();
        menu.find('li.open').removeClass('open');

        el.parents('li').each(function () {
            jQuery(this).addClass('active');

            if (jQuery(this).parent('ul.navbar-nav').size() === 1) {
                jQuery(this).find('> a').append('<span class="selected"></span>');
            }
        });
    };

    // Handles main menu on window resize
    var handleMainMenuOnResize = function() {
        // handle hover dropdown menu for desktop devices only
        var width = Metronic.getViewPort().width;
        var menu = jQuery(".page-header-menu");
            
        if (width >= resBreakpointMd && menu.data('breakpoint') !== 'desktop') { 
            // reset active states
            jQuery('.hor-menu [data-toggle="dropdown"].active').removeClass('open');

            menu.data('breakpoint', 'desktop');
            jQuery('.hor-menu [data-hover="megamenu-dropdown"]').not('.hover-initialized').each(function() {   
                jQuery(this).dropdownHover(); 
                jQuery(this).addClass('hover-initialized'); 
            });
            jQuery('.hor-menu .navbar-nav li.open').removeClass('open');
            jQuery(".page-header-menu").css("display", "block");
        } else if (width < resBreakpointMd && menu.data('breakpoint') !== 'mobile') {
            // set active states as open
            jQuery('.hor-menu [data-toggle="dropdown"].active').addClass('open');
            
            menu.data('breakpoint', 'mobile');
            // disable hover bootstrap dropdowns plugin
            jQuery('.hor-menu [data-hover="megamenu-dropdown"].hover-initialized').each(function() {   
                jQuery(this).unbind('hover');
                jQuery(this).parent().unbind('hover').find('.dropdown-submenu').each(function() {
                    jQuery(this).unbind('hover');
                });
                jQuery(this).removeClass('hover-initialized');    
            });
        } else if (width < resBreakpointMd) {
            //jQuery(".page-header-menu").css("display", "none");  
        }
    };

    var handleContentHeight = function() {
        var height;

        if (jQuery('body').height() < Metronic.getViewPort().height) {            
            height = Metronic.getViewPort().height -
                jQuery('.page-header').outerHeight() - 
                (jQuery('.page-container').outerHeight() - jQuery('.page-content').outerHeight()) -
                jQuery('.page-prefooter').outerHeight() - 
                jQuery('.page-footer').outerHeight();

            jQuery('.page-content').css('min-height', height);
        }
    };

    // Handles the go to top button at the footer
    var handleGoTop = function () {
        var offset = 100;
        var duration = 500;

        if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {  // ios supported
            jQuery(window).bind("touchend touchcancel touchleave", function(e){
               if (jQuery(this).scrollTop() > offset) {
                    jQuery('.scroll-to-top').fadeIn(duration);
                } else {
                    jQuery('.scroll-to-top').fadeOut(duration);
                }
            });
        } else {  // general 
            jQuery(window).scroll(function() {
                if (jQuery(this).scrollTop() > offset) {
                    jQuery('.scroll-to-top').fadeIn(duration);
                } else {
                    jQuery('.scroll-to-top').fadeOut(duration);
                }
            });
        }
        
        jQuery('.scroll-to-top').click(function(e) {
            e.preventDefault();
            jQuery('html, body').animate({scrollTop: 0}, duration);
            return false;
        });
    };

    //* END:CORE HANDLERS *//

    return {
        
        // Main init methods to initialize the layout
        // IMPORTANT!!!: Do not modify the core handlers call order.

        initHeader: function() {
            handleHeader(); // handles horizontal menu    
            handleMainMenu(); // handles menu toggle for mobile
            Metronic.addResizeHandler(handleMainMenuOnResize); // handle main menu on window resize

            if (Metronic.isAngularJsApp()) {      
                handleMainMenuActiveLink('match'); // init sidebar active links 
            }
        },

        initContent: function() {
            handleContentHeight(); // handles content height 
        },

        initFooter: function() {
            handleGoTop(); //handles scroll to top functionality in the footer
        },

        init: function () {            
            this.initHeader();
            this.initContent();
            this.initFooter();
        },

        setMainMenuActiveLink: function(mode, el) {
            handleMainMenuActiveLink(mode, el);
        },

        closeMainMenu: function() {
            jQuery('.hor-menu').find('li.open').removeClass('open');

            if (Metronic.getViewPort().width < resBreakpointMd && jQuery('.page-header-menu').is(":visible")) { // close the menu on mobile view while laoding a page 
                jQuery('.page-header .menu-toggler').click();
            }
        },

        getLayoutImgPath: function() {
            return Metronic.getAssetsPath() + layoutImgPath;
        },

        getLayoutCssPath: function() {
            return Metronic.getAssetsPath() + layoutCssPath;
        }
    };

}();