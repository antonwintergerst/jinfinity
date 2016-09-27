/**
 * @version     $Id: jQuery modalpickerJ15.js 052 2013-08-22 14:39:00Z Anton Wintergerst $
 * @package     JiModalPicker Field for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 * */

jQuery(document).ready(function() {
    var modalpicker = jQuery('#paramsmodaltype');
    var modalPickerChangedHandler = function(e) {modalPickerChanged(e);};
    jQuery(modalpicker).change(modalPickerChangedHandler);
    
    var selected = findSelected(modalpicker);    
    setAttributes(selected);
});
function modalPickerChanged(e) {
    var modalpicker = jQuery('#paramsmodaltype');
    var selected = findSelected(modalpicker);
    setAttributes(selected);
}
function setAttributes(selected) {
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
    var attrsinput = jQuery('#paramsgal_thumbs_linkattr');
    attrsinput.value = attrs;
    
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
}
function findSelected(e) {
    var selected = null;
    var options = e[0].getElementsByTagName('option');
    if(options!=null) {
        for(var o=0; o<options.length; o++) {
            if(options[o].selected) selected = options[o].value;
        }
    }
    return selected;
}
