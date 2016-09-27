/*
 * @version     $Id: jquery.jicheckbox.js 166 2014-12-15 10:47:00Z Anton Wintergerst $
 * @package     JiCheckbox for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function(jQuery){
    var JiCheckbox = function(element, options)
    {
        var self = this;
        // Set Default Options
        this.element = element;
        this.image = null;
        this.width = 13;
        this.height = 13;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        // Actions
        this.toggleCheckbox = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var cbox = document.getElementById(target.parentNode.rel);
            if(jQuery(cbox).is(':checked')) {
                jQuery(cbox).removeAttr('checked');
                if(cbox!=null) cbox.checked = false;
                jQuery(target).css('background-position', '0 0');
            } else {
                jQuery(cbox).attr('checked','checked');
                if(cbox!=null) cbox.checked = true;
                jQuery(target).css('background-position', -this.width+'px 0');
            }
            // Fire Event
            e.target = cbox;
            jQuery(cbox).trigger('change', e);
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
        },
        this.checkboxChanged = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var img = jQuery('#'+target.id+'-img');
            if(jQuery(target).is(':checked')) {
                jQuery(target).attr('checked','checked');
                jQuery(img).css('background-position', -this.width+'px 0');
            } else {
                jQuery(target).removeAttr('checked');
                jQuery(img).css('background-position', '0 0');
            }
        };
        // Setup Handlers
        this.clickHandler = function(e) {self.toggleCheckbox(e);};
        this.toggleListener = function(e) {self.checkboxChanged(e);};
        
        // Init
        // Find Checkboxes
        var cbs = jQuery(this.element);
        jQuery.each(cbs, function(index, cbox) {
            if(cbox.id.length==0) cbox.id = cbox.name;
            jQuery(cbox).addClass('realcbox');
            jQuery(cbox).css('display', 'none');
            jQuery(cbox).on('change', self.toggleListener);
            
            var customcbox = jQuery(document.createElement('div')).attr({'class': jQuery(cbox).attr('id')+'-container customcbox'});
            var link = jQuery(document.createElement('a')).attr({'class': 'cboxlink', 'href': '#', 'rel': cbox.id});
            jQuery(customcbox).append(link);
            var xOffset = 0;
            if(jQuery(cbox).is(':checked')) {
                jQuery(cbox).attr('checked','checked');
                xOffset = -self.width;
            } else {
                jQuery(cbox).removeAttr('checked');
            }
            var img = jQuery(document.createElement('span')).attr({
                'id': cbox.id+'-img',
                'class': 'cboximg'
            }).css({
                'display': 'block',
                'width': self.width+'px',
                'height': self.height+'px',
                'background-image': 'url('+self.image+')',
                'background-repeat': 'no-repeat',
                'background-position': xOffset+'px 0'
            });
            jQuery(link).append(img);
            jQuery(customcbox).insertAfter(cbox);
            
            jQuery(link).on('click', self.clickHandler);
        });
    };
        
    jQuery.fn.jicheckbox = function(options) {
        var element = jQuery(this);
        if(element.data('jicheckbox')) return element.data('jicheckbox');
        var jicheckbox = new JiCheckbox(this, options);
        element.data('jicheckbox', jicheckbox);
        return jicheckbox;
    };
})(jQuery);