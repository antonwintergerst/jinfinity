/**
 * @version     $Id: jquery.jicachecleaner.js 010 2014-12-18 18:15:00Z Anton Wintergerst $
 * @package     JiCacheCleaner for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

(function(jQuery){
    var JiCacheCleaner = function(container, options)
    {
        var self = this;
        // Setup Options
        this.url = null;

        if(!jQuery.isPlainObject(options)) options = jQuery.parseJSON(options);
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.triggered = false;

        this.hijackClick = function(e) {
            if(!this.triggered) {
                e.preventDefault();
                e.stopImmediatePropagation();
                jQuery.ajax(this.url).complete(function() {
                    console.log('JiCache cleared!');
                    self.triggered = true;
                });
                return false;
            }
        }
        this.btnHandler = function(e) {
            e.preventDefault();
            window.location.href = self.url;
        }
        this.init = function() {
            var cachebtn = jQuery('<div class="btn-wrapper" id="toolbar-delete"><button class="btn btn-small"><span class="icon-delete"></span>Clear Cache</button></div>');
            jQuery(cachebtn).on('click', this.btnHandler);
            jQuery('#toolbar').append(cachebtn);
        };
        this.init();
    };
    jQuery.fn.jicachecleaner = function(options) {
        var element = jQuery(this);
        // Create new class
        var jicachecleaner = new JiCacheCleaner(this, options);
        // Set and return class data
        element.data('jicachecleaner', jicachecleaner);
        return jicachecleaner;
    };
})(jQuery);