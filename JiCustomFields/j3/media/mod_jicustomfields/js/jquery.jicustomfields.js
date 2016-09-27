/**
 * @version     $Id: jquery.jicustomfields.js 010 2014-10-27 13:30:00Z Anton Wintergerst $
 * @package     JiCustomFields Search Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
jQuery(document).ready(function() {
    jQuery('.modjicustomfields.search .searchword').on({
        click:function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            jQuery(target).closest('.fieldgroup').addClass('hidelabel');
        },
        focus:function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            jQuery(target).closest('.fieldgroup').addClass('hidelabel');
        },
        blur:function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            if(jQuery(target).val()=='') jQuery(target).closest('.fieldgroup').removeClass('hidelabel');
        }
    });
    if(jQuery('.modjicustomfields.search .searchword').val().length>0) {
        jQuery('.modjicustomfields.search .searchword').closest('.fieldgroup').addClass('hidelabel');
    }
});