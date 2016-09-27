/**
 * @version     $Id: jquery.jitoggler.js 121 2014-12-15 10:47:00Z Anton Wintergerst $
 * @package     JiToggler for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function(jQuery){
    var JiToggler = function(btn, options)
    {
        var self = this;

        // Set Default Options
        this.btn = btn;
        this.tab = null;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        // Actions
        this.toggle = function(e) {
            var btn = e.target != null ? e.target : e.srcElement;
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
                    // Activate current tab
                    var tab = jQuery(this.tab+'.'+jid);
                    jQuery(tab).addClass('active');
                    if(open) {
                        jQuery(tab).slideDown(250);
                    } else {
                        jQuery(tab).slideUp(250);
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
        this.btns = jQuery(this.btn);
        jQuery.each(this.btns, function(index, btn) {
            jQuery(btn).on('change', self.toggleHandler);
            var jid = self.findJID(btn);
            var tab = null;
            if(jid!=null) {
                tab = jQuery(self.tab+'.'+jid);
            }
            var tabclass = jQuery(tab).attr('class');
            if(tabclass!=null) {
                if(jQuery(btn).is(':checked') || jQuery(btn).attr('class').indexOf('active')!=-1 || tabclass.indexOf('active')!=-1) {
                    jQuery(btn).addClass('active');
                    jQuery(tab).addClass('active');
                    jQuery(tab).show();
                } else {
                    jQuery(btn).removeClass('active');
                    jQuery(tab).removeClass('active');
                    jQuery(tab).hide();
                }
            }
        });
    };
    jQuery.fn.jitoggler = function(options) {
        var element = jQuery(this);
        if(element.data('jitoggler')) return element.data('jitoggler');
        var jitoggler = new JiToggler(this, options);
        element.data('jitoggler', jitoggler);
        return jitoggler;
    };
})(jQuery);