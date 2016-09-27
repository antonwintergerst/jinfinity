/**
 * @version     $Id: jquery.jiselectmenu.js 032 2013-07-18 15:32:00Z Anton Wintergerst $
 * @package     JiSelectMenu for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiSelectMenu = function(container, options)
    {
        var self = this;

        this.setDefaultOptions = function() {
            this.maxwidth = 480;
            this.screentypes = ['phone'];

            this.menu = container;
            this.isOpen = false;
            this.urls = [];
        }
        this.setDefaultOptions();
        // Set User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }

        this.openMenu = function() {
            this.isOpen = true;
            jQuery(this.menu).removeClass('hideselect').slideDown(250, function() {
                var opened = false;
                var select = jQuery(self.menu).get(0);
                if(document.createEvent) { // all browsers
                    var e = document.createEvent("MouseEvents");
                    e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                    opened = select.dispatchEvent(e);
                } else if(select.fireEvent) { // ie
                    opened = select.fireEvent("onmousedown");
                }
            });
            jQuery('.jitogglemenubtn').addClass('active');
        };
        this.closeMenu = function() {
            this.isOpen = false;
            jQuery(this.menu).slideUp(function() {
                jQuery(self.menu).addClass('hideselect');
            });
            jQuery('.jitogglemenubtn').removeClass('active');
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
        this.openURL = function() {
            var key = jQuery(this.menu).val();
            if(this.urls[key]!=null) {
                window.open(this.urls[key],'_self');
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
        this.closeHandler = function(e) {
            if(self.isOpen) {
                if(self.delayedClose!=null) clearTimeout(self.delayedClose);
                self.delayedClose = setTimeout(function() {
                    self.closeMenu();
                    clearTimeout(self.delayedClose);
                }, 100);
            }
        };
        this.openHandler = function(e) {self.openURL();};
        this.windowResizeHandler = function(e) {self.updateLayout();};
        this.screenChangedHandler = function(grid, e) {self.screenChanged(grid, e);};
        this.toggleMenuHandler = function(e) {self.toggleMenu(e);};
        this.init = function() {
            if(typeof jigrid!=undefined) {
                this.screenChanged(jigrid, {'screentype':jigrid.screentype});
            }
            jQuery(window).on({
                'resize':this.windowResizeHandler/*,
                'click':this.closeHandler*/
            });
            jQuery(this.menu).on({
                'change':this.openHandler,
                'blur':this.closeHandler
            });
            jQuery('.jigrid.level1').on('screenchanged', this.screenChangedHandler);
            jQuery('.jitogglemenubtn').on('click', this.toggleMenuHandler);
        };
        this.init();
    };
    jQuery.fn.jiselectmenu = function(options) {
        return this.each(function() {
            var element = jQuery(this);
            if(element.data('jiselectmenu')) return;
            var jiselectmenu = new JiSelectMenu(this, options);
            element.data('jiselectmenu', jiselectmenu);
        });
    };
})(jQuery);