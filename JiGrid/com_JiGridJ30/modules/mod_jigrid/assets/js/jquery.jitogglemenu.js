/**
 * @version     $Id: jquery.jitogglemenu.js 030 2013-07-18 14:11:00Z Anton Wintergerst $
 * @package     JiToggleMenu for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiToggleMenu = function(container, options)
    {
        var self = this;

        this.maxwidth = 480;
        this.screentypes = ['phone'];

        this.menu = container;
        this.isOpen = false;

        this.openMenu = function() {
            this.isOpen = true;
            jQuery('.jitogglemenubtn').addClass('active');
            jQuery(this.menu).slideDown();
        };
        this.closeMenu = function() {
            this.isOpen = false;
            jQuery('.jitogglemenubtn').removeClass('active');
            jQuery(this.menu).slideUp();
        };
        this.toggleMenu = function(e) {
            e.preventDefault();
            e.stopPropagation();
            if(this.isOpen) {
                this.closeMenu();
            } else {
                this.openMenu();
            }
        };
        this.screenChanged = function(grid, e) {
            var showtogglemenu = false;
            jQuery.each(this.screentypes, function(index, screentype) {
                if(screentype==e.screentype) showtogglemenu = true;
            });
            if(showtogglemenu) {
                jQuery(document.body).addClass('hasjitogglemenu');
            } else {
                jQuery(document.body).removeClass('hasjitogglemenu');
            }
        };
        this.updateLayout = function() {
            var maxheight = jQuery(window).height()-jQuery('.jitogglemenubar').height();
            jQuery(container).css('max-height',maxheight+'px');
        };
        this.windowResizeHandler = function(e) {self.updateLayout();};
        this.screenChangedHandler = function(grid, e) {self.screenChanged(grid, e);};
        this.toggleMenuHandler = function(e) {self.toggleMenu(e);};
        this.init = function() {
            jQuery(window).on('resize', this.windowResizeHandler);
            jQuery('.jigrid.level1').on('screenchanged', this.screenChangedHandler);
            jQuery('.jitogglemenubtn').on('click', this.toggleMenuHandler);
        };
        this.init();
    };
    jQuery.fn.jitogglemenu = function(options) {
        return this.each(function() {
            var element = jQuery(this);
            if(element.data('jitogglemenu')) return;
            var jitogglemenu = new JiToggleMenu(this, options);
            element.data('jitogglemenu', jitogglemenu);
        });
    };
})(jQuery);
if(typeof jQuery!='undefined') {
    jQuery(document).ready(function() {
        jQuery('.jitogglemenu').jitogglemenu();
    });
}