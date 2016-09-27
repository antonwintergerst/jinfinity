/**
 * @version     $Id: jQuery modalpickerJ25.js 053 2013-08-22 14:39:00Z Anton Wintergerst $
 * @package     JiModalPicker Field for Joomla 2.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 * */

jQuery(document).ready(function() {
    jQuery('#jform_params_modaltype').jimodalpicker();
});
(function(jQuery){
    var JiModalPicker = function(container, options)
    {
        var self = this;
        // Setup Options
        this.attrinput = null;

        if(!jQuery.isPlainObject(options)) options = jQuery.parseJSON(options);
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.modalChanged = function(e) {
            var selected = this.findSelected(container);
            this.setAttributes(selected);
        }
        this.findSelected = function(e) {
            var selected = null;
            var options = e[0].getElementsByTagName('option');
            if(options!=null) {
                for(var o=0; o<options.length; o++) {
                    if(options[o].selected) selected = options[o].value;
                }
            }
            return selected;
        };
        this.setAttributes = function(selected) {
            var attrs = '';
            switch(selected) {
                case 'slimbox2':
                    attrs = 'rel="slimbox-images"';
                    break;
                case 'shadowbox':
                    attrs = 'rel="shadowbox[images]"';
                    break;
                case 'squeezebox':
                    attrs = 'rel="squeezebox"';
                    break;
                case 'fancybox':
                    attrs = 'class="fancybox" rel="images"';
                    break;
            }
            jQuery(this.attrinput).val(attrs);

            // Replace dynamic text
            attrs = attrs.replace('%f', 'folder');
            // Update preview
            var previewlink = jQuery('#modalpreviewlink');
            var href = jQuery(previewlink).attr('href');
            var thumbs = jQuery('#modalpreviewlink img');
            var src = '';
            if(thumbs[0]!=null) {
                src = thumbs[0].src;
            }
            var preview = jQuery('#modalpreview');
            jQuery('#modalpreview').html('<a id="modalpreviewlink" href="'+href+'" '+attrs+' target="_blank"><img src="'+src+'" alt="" /></a>');

            // Re-attach Modal
            var previewlink = jQuery('#modalpreviewlink');
            switch(selected) {
                case 'slimbox2':
                    jQuery("#modalpreviewlink").slimbox();
                    break;
                case 'shadowbox':
                    Shadowbox.init();
                    Shadowbox.setup("#modalpreviewlink");
                    break;
                case 'squeezebox':
                    SqueezeBox.assign($('modalpreviewlink'));
                    break;
                case 'fancybox':
                    jQuery("#modalpreviewlink").fancybox();
                    break;
            }
        };
        this.modalChangedHandler = function(e) {
            self.modalChanged(e);
        };
        this.init = function() {
            jQuery(container).on('change', this.modalChangedHandler);
            var selected = this.findSelected(container);
            this.setAttributes(selected);
        };
        this.init();
    };
    jQuery.fn.jimodalpicker = function(options) {
        var element = jQuery(this);
        // Create new class
        var jimodalpicker = new JiModalPicker(this, options);
        // Set and return class data
        element.data('jimodalpicker', jimodalpicker);
        return jimodalpicker;
    };
})(jQuery);