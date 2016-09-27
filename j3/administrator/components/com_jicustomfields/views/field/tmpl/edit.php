<?php
/**
 * @version     $Id: edit.php 005 2014-05-06 10:16:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    // Load the tooltip behavior.
    JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    JHtml::_('behavior.keepalive');
    JHtml::_('formbehavior.chosen', 'select');
} else {
}
// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$input = JFactory::getApplication()->input;
?>
<div class="jicustomfields customfields field">
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if (task == 'field.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                Joomla.submitform(task, document.getElementById('item-form'));
            } else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
            }
        }
    </script>

    <form action="<?php echo JRoute::_('index.php?option=com_jicustomfields&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
        <?php echo $this->form->getInput('fieldform'); ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>