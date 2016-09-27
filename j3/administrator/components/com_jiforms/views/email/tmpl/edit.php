<?php
/**
 * @version     $Id: edit.php 018 2013-11-14 10:44:00Z Anton Wintergerst $
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
<div class="jiforms form">
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if (task == 'email.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                <?php echo $this->form->getField('message')->save(); ?>
                Joomla.submitform(task, document.getElementById('item-form'));
            } else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
            }
        }
    </script>

    <form action="<?php echo JRoute::_('index.php?option=com_jiforms&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
        <div class="row-fluid">
            <!-- Begin Content -->
            <div class="span10 form-horizontal">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_JIFORMS_GENERALTAB');?></a></li>
                    <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                    <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                        <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <!-- Begin Tabs -->
                    <div class="tab-pane active" id="general">
                        <fieldset class="adminform">
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('title'); ?> <?php echo $this->form->getInput('title'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('subject'); ?> <?php echo $this->form->getInput('subject'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('to'); ?> <?php echo $this->form->getInput('to'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('cc'); ?> <?php echo $this->form->getInput('cc'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('bcc'); ?> <?php echo $this->form->getInput('bcc'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('from'); ?> <?php echo $this->form->getInput('from'); ?>
                            </div>
                            <div class="control-group form-inline">
                                <?php echo $this->form->getLabel('replyto'); ?> <?php echo $this->form->getInput('replyto'); ?>
                            </div>
                            <?php echo $this->form->getInput('message'); ?>
                        </fieldset>
                    </div>
                    <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                    <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                        <div class="tab-pane" id="attrib-<?php echo $name;?>">
                            <div class="row-fluid">
                                <div class="span6">
                                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                        <div class="control-group">
                                            <?php echo $field->label; ?>
                                            <div class="controls">
                                                <?php echo $field->input; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- End Tabs -->
                </div>
                <input type="hidden" name="task" value="" />
                <input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
                <?php echo JHtml::_('form.token'); ?>
            </div>
            <!-- End Content -->
            <!-- Begin Sidebar -->
            <div class="span2">
                <h4><?php echo JText::_('JDETAILS');?></h4>
                <hr />
                <fieldset class="form-vertical">
                    <div class="control-group">
                        <label class="control-label"><?php echo JText::_('COM_JIFORMS_TITLE_LABEL'); ?></label>
                        <div class="controls">
                            <input id="jform_titlelabel" class="readonly" type="text" readonly="readonly" size="10" value="<?php echo $this->form->getValue('title'); ?>" name="" aria-invalid="false">

                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->form->getLabel('id'); ?>
                        <div class="controls">
                            <?php echo $this->form->getInput('id'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->form->getLabel('alias'); ?>
                        <div class="controls">
                            <?php echo $this->form->getInput('alias'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->form->getLabel('state'); ?>
                        <div class="controls">
                            <?php echo $this->form->getInput('state'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->form->getLabel('publish_up'); ?>
                        <div class="controls">
                            <?php echo $this->form->getInput('publish_up'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->form->getLabel('publish_down'); ?>
                        <div class="controls">
                            <?php echo $this->form->getInput('publish_down'); ?>
                        </div>
                    </div>
                </fieldset>
            </div>
            <!-- End Sidebar -->
        </div>
    </form>
</div>