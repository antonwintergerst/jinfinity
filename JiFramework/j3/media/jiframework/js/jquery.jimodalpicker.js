/**
 * @version     $Id: jquery.jimodalpicker.js 056 2014-12-18 10:48:00Z Anton Wintergerst $
 * @package     JiModalPicker for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

(function(jQuery){
    var JiModalPicker = function(container, options)
    {
        var self = this;
        // Setup Options
        this.group = '%f';
        this.name = '';
        this.attrinput = null;

        if(!jQuery.isPlainObject(options)) options = jQuery.parseJSON(options);
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.iteration = 0;
        this.modalframework = 'custom';

        this.modalChanged = function(e) {
            this.modalframework = this.findSelected(container);
            this.setAttributes();
        }
        this.attrsChanged = function() {
            this.updateAttributes();
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
        this.setAttributes = function()
        {
            var attrs = '';
            switch(this.modalframework) {
                case 'slimbox2':
                    attrs = 'rel="slimbox-'+this.group+'"';
                    break;
                case 'shadowbox':
                    attrs = 'rel="shadowbox['+this.group+']"';
                    break;
                case 'fancybox':
                    attrs = 'class="fancybox" rel="'+this.group+'"';
                    break;
                default:
                    attrs = '';
                    break;
            }
            if(attrs!='') jQuery(this.attrinput).val(attrs);
            this.updateAttributes();
        }

        this.updateAttributes = function()
        {
            var attrs = jQuery(this.attrinput).val();

            // convert attributes to string
            var exclude = ['id', 'href', 'target'];
            var e = jQuery('<a '+attrs+'></a>');
            jQuery(e[0].attributes).each(function() {
                if(attrs!='') attrs+= ' ';
                if(exclude.indexOf(this.nodeName)==-1) attrs+= this.nodeName+'="'+this.value+'"';
            });

            // replace dynamic text
            attrs = attrs.replace(this.group, 'group');

            // Update preview
            var previewlink = (this.iteration==0)? jQuery('#'+this.name+'modalpreviewlink') : jQuery('#'+this.name+'modalpreviewlink'+this.iteration);
            var href = jQuery(previewlink).attr('href');
            var thumbs = jQuery(previewlink).find('img');
            var src = '';
            if(thumbs[0]!=null) {
                src = thumbs[0].src;
            }
            this.iteration++;
            jQuery('#'+this.name+'modalpreview').html('<a id="'+this.name+'modalpreviewlink'+this.iteration+'" href="'+href+'" '+attrs+' target="_blank"><img src="'+src+'" alt="" /></a>');

            // Re-attach Modal
            var previewlink = '#'+this.name+'modalpreviewlink'+this.iteration;
            if(this.modalframework=='slimbox2' || attrs.indexOf('slimbox')!=-1) {
                    jQuery(previewlink).slimbox();
            }
            if(this.modalframework=='shadowbox' || attrs.indexOf('shadowbox')!=-1) {
                    Shadowbox.init();
                    Shadowbox.setup(previewlink);
            }
            if(this.modalframework=='fancybox' || attrs.indexOf('fancybox')!=-1) {
                    jQuery(previewlink).fancybox();
            }
        };
        this.modalChangedHandler = function(e) {
            self.modalChanged(e);
        };
        this.attrsChangedHandler = function(e) {
            self.attrsChanged();
        };
        this.init = function() {
            this.modalChanged(container);
            jQuery(container).on('change', this.modalChangedHandler);
            var selected = this.findSelected(container);
            this.setAttributes(selected);

            jQuery(this.attrinput).on('blur', this.attrsChangedHandler);
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