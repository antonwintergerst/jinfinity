/**
 * @version     $Id: jquery.jipagecontext.js 029 2014-03-04 16:40:00Z Anton Wintergerst $
 * @package     JiPageContext System Plugin for Joomla 3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

(function(jQuery){
    var JiPageContext = function(container, options)
    {
        var self = this;
        // Setup Options
        if(!jQuery.isPlainObject(options)) options = jQuery.parseJSON(options);
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
            this.initialize = function() {
                if(self.newclassnames!=null) {
                    jQuery(container).addClass(self.newclassnames);
                }
            };
            this.initialize();
        }
    };
    jQuery.fn.jipagecontext = function(options) {
        var element = jQuery(this);
        // Create new class
        var jipagecontext = new JiPageContext(this, options);
        // Set and return class data
        element.data('jipagecontext', jipagecontext);
        return jipagecontext;
    };
})(jQuery);