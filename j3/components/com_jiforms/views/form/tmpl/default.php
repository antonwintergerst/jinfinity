<?php
/**
 * @version     $Id: default.php 036 2013-10-28 12:31:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHtml::stylesheet('media/jiforms/css/jiforms.css');
    JHtml::script('media/jiforms/js/jquery.jivalidator.js');
}
$jinput = JFactory::getApplication()->input;
if(!isset($form)) $form = $this->form;
?>
<div class="jiforms form">
    <script type="text/javascript">
        var jiformvalidator = null;
        if(typeof jQuery!=undefined) {
            jQuery(document).ready(function() {
                jiformvalidator = jQuery('.jiform').jivalidator({'url':'<?php echo JURI::root(); ?>index.php?option=com_jiforms&amp;view=validate'});
                <?php if($jinput->get('event')=='fail'): ?>
                setTimeout(function() {jiformvalidator.validate(true);}, 200);
                <?php endif; ?>
            });
        }
    </script>
    <?php eval('?>'.$this->form->content.'<?php '); ?>
</div>