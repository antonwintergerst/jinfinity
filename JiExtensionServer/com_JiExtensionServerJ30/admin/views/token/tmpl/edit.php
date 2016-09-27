<?php
/**
 * @version     $Id: edit.php 025 2013-06-18 10:13:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load Scripts
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHtml::_('formbehavior.chosen', 'select');
    JHtml::_('behavior.framework', true);
    JHTML::script('administrator/components/com_jiextensionserver/assets/js/mootools.zeroclipboard.js');
} else {
    JHTML::_('script', 'jquery.min.js', 'administrator/components/com_jiextensionserver/assets/js/');
    JHTML::_('script', 'jquery.noconflict.js', 'administrator/components/com_jiextensionserver/assets/js/');
    JHtml::_('behavior.framework', true);
    JHTML::_('script', 'mootools.zeroclipboard.js', 'administrator/components/com_jiextensionserver/assets/js/');
}
$input = JFactory::getApplication()->input;
$model = JModelLegacy::getInstance('Token', 'JiExtensionServerModel');
$token = $model->getToken();
?>
<div class="jiextensionserver">
    <div class="token">
        <script type="text/javascript">
            Joomla.submitbutton = function(task) {
                if (task == 'token.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                    Joomla.submitform(task, document.getElementById('item-form'));
                } else {
                    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
                }
            }
        </script>

        <form action="<?php echo JRoute::_('index.php?option=com_jiextensionserver&view=token&layout=edit'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
            <div class="row-fluid">
                <!-- Begin Content -->
                <div class="span10 form-horizontal">
                    <div>
                        <script>
                            if(typeof jQuery!='undefined') {
                                jQuery(document).ready(function() {
                                    if(typeof ZeroClipboard!='undefined') {
                                        // ZeroClipboard Legacy Compatibility
                                        ZeroClipboard.setMoviePath( '<?php echo JURI::root().'administrator/components/com_jiextensionserver/assets/js/ZeroClipboardOld.swf'; ?>' );
                                        var clip = new ZeroClipboard.Client();
                                        clip.setText(jQuery('.dltoken').val());
                                        clip.addEventListener('onComplete', function() {
                                            jQuery('.copydltoken').removeClass('btn-primary');
                                            jQuery('.copydltoken').html('Copied!');
                                        });
                                        clip.glue(jQuery('.copydltoken').get(0));
                                    } else {
                                        var clip = new ZeroClipboard(jQuery('.copydltoken').get(0), {
                                            moviePath: '<?php echo JURI::root().'administrator/components/com_jiextensionserver/assets/js/ZeroClipboard.swf'; ?>'
                                        });

                                        clip.on('mousedown', function(client) {
                                            clip.setText(jQuery('.dltoken').val());
                                            jQuery('.copydltoken').removeClass('btn-primary');
                                            jQuery('.copydltoken').html('Copied!');
                                        });
                                    }
                                });
                            }
                        </script>
                        <div class="input-prepend input-append">
                            <span class="add-on">Download Token</span>
                            <input class="dltoken" type="text" value="<?php echo $token; ?>">
                            <a class="btn btn-primary copydltoken" href="#">Copy</a>
                        </div>
                    </div>
                    <div class="tab-pane active" id="general">
                        <fieldset class="adminform">
                            <div class="row-fluid">
                                <div class="span6">
                                    <div class="control-group">
                                        <?php echo $this->form->getLabel('dlkey'); ?>
                                        <div class="controls">
                                            <?php echo $this->form->getInput('dlkey'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="span6">
                                    <div class="control-group">
                                        <?php echo $this->form->getLabel('valid'); ?>
                                        <div class="controls">
                                            <span id="jform_valid" class="readonly"><?php echo ($this->form->getValue('valid')==1)? JText::_('COM_JIEXTENSIONSERVER_YES') : JText::_('COM_JIEXTENSIONSERVER_NO'); ?></span>
                                        </div>
                                    </div>
                                    <div class="control-group">
                                        <?php echo $this->form->getLabel('id'); ?>
                                        <div class="controls">
                                            <?php echo $this->form->getInput('id'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="clr"></div>
                        </fieldset>
                    </div>
                    <input type="hidden" name="task" value="" />
                    <input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
                    <?php echo JHtml::_('form.token'); ?>
                </div>
                <!-- End Content -->
            </div>
        </form>
    </div>
</div>