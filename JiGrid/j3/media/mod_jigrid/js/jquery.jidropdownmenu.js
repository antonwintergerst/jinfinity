/**
 * @version     $Id: jquery.jidropdownmenu.js 015 2013-09-20 14:25:00Z Anton Wintergerst $
 * @package     JiDropDownMenu for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiDropDownMenu = function(container, options)
    {
        var self = this;

        this.setDefaultOptions = function() {
            this.menu = container;
            this.childelement = '.nav-childouter';
        }
        this.setDefaultOptions();
        // Set User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.openDropDown = function(e) {
            jQuery(e).addClass('open');
            jQuery(e).find(this.childelement).css({'display':'none', 'opacity':'1'}).stop()
                .slideDown('250', function() {
                    jQuery(e).find(this.childelement).css('display', 'block');
                }
            );
        };
        this.closeDropDown = function(e) {
            jQuery(e).removeClass('open');
            jQuery(e).find(this.childelement).css({'display':'block'})
                .slideUp('250')
                .animate({opacity: 0}, {queue: false, duration: 'slow', complete: function() {
                    jQuery(e).find(this.childelement).css('display', 'none');
                }}
            );
        };
        this.enterParent = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            var target = jQuery(sender).closest('li.parent');
            this.openDropDown(target);
        };
        this.leaveParent = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            var target = jQuery(sender).closest('li.parent');
            this.closeDropDown(target);
        };
        this.screenChanged = function(grid, e) {
        };
        this.updateLayout = function() {
        };
        this.windowResizeHandler = function(e) {self.updateLayout();};
        this.screenChangedHandler = function(grid, e) {self.screenChanged(grid, e);};
        this.enterParentHandler = function(e) {self.enterParent(e);};
        this.leaveParentHandler = function(e) {self.leaveParent(e);};
        this.init = function() {
            jQuery(window).on('resize', this.windowResizeHandler);
            jQuery('.jigrid.level1').on('screenchanged', this.screenChangedHandler);
            jQuery('.jidropdownmenu li.parent').on({
                'mouseenter':this.enterParentHandler,
                'mouseleave':this.leaveParentHandler
            });
        };
        this.init();
    };
    jQuery.fn.jidropdownmenu = function(options) {
        return this.each(function() {
            var element = jQuery(this);
            if(element.data('jidropdownmenu')) return;
            var jidropdownmenu = new JiDropDownMenu(this, options);
            element.data('jidropdownmenu', jidropdownmenu);
        });
    };
})(jQuery);
if(typeof jQuery!='undefined') {
    jQuery(document).ready(function() {
        jQuery('.jidropdownmenu').jidropdownmenu();
    });
}