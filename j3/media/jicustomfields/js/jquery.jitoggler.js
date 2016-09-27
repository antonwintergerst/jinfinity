/*
 * @version     $Id: jquery.jitoggler.js 130 2013-03-04 13:28:00Z Anton Wintergerst $
 * @package     JiToggler for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function($){
    var JiToggler = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.btn = null;
        this.tab = null;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        // Actions
        this.toggle = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            var btn = jQuery(sender).closest(this.btn);
            if(btn!=null) {
                // Find State
                var open;
                if(jQuery(btn).attr('class').indexOf('active')==-1) {
                    open = true;
                    jQuery(btn).addClass('active');
                } else {
                    open = false;
                    jQuery(btn).removeClass('active');
                }
                
                // Deactivate other tabs
                jQuery(this.tab).removeClass('active');
                var jid = this.findJID(btn);
                if(jid!=null) {
                    jQuery(btn).addClass('changing');
                    // Activate current tab
                    var tab = jQuery(this.tab+'.'+jid);
                    jQuery(tab).addClass('active');
                    if(open) {
                        jQuery(tab).slideDown(250, function() {
                            jQuery(btn).removeClass('changing');
                        });
                    } else {
                        jQuery(tab).slideUp(250, function() {
                            jQuery(btn).removeClass('changing');
                        });
                    }
                }
            }
        };
        this.findJID = function(e) {
            var jid = null;
            var classparts = jQuery(e).attr('class').split(' ');
            jQuery.each(classparts, function(index, classname) {
                if(classname.indexOf('jid')!=-1) {
                    jid = classname;
                }
            });
            return jid;
        };
        this.getElementSize = function(e) {
            var width = Math.max(e.scrollWidth, e.offsetWidth, e.clientWidth);
            var height = Math.max(e.scrollHeight, e.offsetHeight, e.clientHeight);
            return {x:width, y:height};
        };
        // Setup Handlers
        this.toggleHandler = function(e) {self.toggle(e);};
        
        // Init
        this.init = function() {
            this.btns = jQuery(this.btn);
            jQuery.each(this.btns, function(index, btn) {
                // Prevent duplicate handlers
                jQuery(btn).off('click', self.toggleHandler);
                // Set handler
                jQuery(btn).on('click', self.toggleHandler);
                var jid = self.findJID(btn);
                var tab = null;
                if(jid!=null) {
                    tab = jQuery(self.tab+'.'+jid);
                }
                if(jQuery(btn).attr('class').indexOf('active')!=-1 || jQuery(tab).attr('class').indexOf('active')!=-1) {
                    jQuery(btn).addClass('active');
                    jQuery(tab).addClass('active');
                    jQuery(tab).show();
                } else {
                    jQuery(btn).removeClass('active');
                    jQuery(tab).removeClass('active');
                    jQuery(tab).hide();
                }
            });
        };
        this.init();
    };
    $.fn.jitoggler = function(options) {
        var element = jQuery(this);
        if(element.data('jitoggler')) {
            // Load existing class
            var jitoggler = element.data('jitoggler').init();
            return element.data('jitoggler');
        } else {
            // Create new class
            var jitoggler = new JiToggler(this, options);
        }
        // Set and return class data
        element.data('jitoggler', jitoggler);
        return jitoggler;
    };
})(jQuery);