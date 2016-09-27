<?php
/**
 * @version     $Id: default.php 025 2013-06-17 16:23:00Z Anton Wintergerst $
 * @package     JiDownloadToken Module for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Load Scripts
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    if($params->get('load_jquery')) JHtml::_('jquery.framework');
    if($params->get('load_mootools')) JHtml::_('behavior.framework', true);
    JHTML::script('modules/mod_jidownloadtoken/assets/js/mootools.zeroclipboard.js');
} else {
    if($params->get('load_jquery')) {
        JHTML::_('script', 'jquery.min.js', 'modules/mod_jidownloadtoken/assets/js/');
        JHTML::_('script', 'jquery.noconflict.js', 'modules/mod_jidownloadtoken/assets/js/');
    }
    if($params->get('load_mootools')) JHtml::_('behavior.framework', true);
    JHTML::_('script', 'mootools.zeroclipboard.js', 'modules/mod_jidownloadtoken/assets/js/');
}
?>
<?php if($token!=null): ?>
    <div class="jidownloadtoken">
        <script>
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    var clip = new ZeroClipboard(jQuery('.copydltoken').get(0), {
                        moviePath: '<?php echo JURI::root().'modules/mod_jidownloadtoken/assets/js/ZeroClipboard.swf'; ?>'
                    });
                    clip.on('mousedown', function(client) {
                        clip.setText(jQuery('.dltoken').val());
                        jQuery('.copydltoken').removeClass('btn-primary');
                        jQuery('.copydltoken').html('Copied!');
                    });
                });
            }
        </script>
        <div class="input-prepend input-append">
            <span class="add-on">Download Token</span>
            <input class="dltoken" type="text" value="<?php echo $token; ?>">
            <a class="btn btn-primary copydltoken" href="#">Copy</a>
        </div>
    </div>
<?php endif; ?>