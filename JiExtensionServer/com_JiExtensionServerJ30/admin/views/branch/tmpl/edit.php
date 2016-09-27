<?php
/**
 * @version     $Id: edit.php 018 2013-06-20 10:22:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');

// Load Scripts
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('administrator/components/com_jiextensionserver/assets/css/jiextensionserver.css');
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jiextensionserver.css', 'administrator/components/com_jiextensionserver/assets/css/');
}
// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$input = JFactory::getApplication()->input;
?>
<div class="jinfinity jiextensionserver<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' row-fluid'; ?>">
    <div class="branch<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' span12'; ?>">
        <script type="text/javascript">
            Joomla.submitbutton = function(task) {
                if (task == 'branch.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                    Joomla.submitform(task, document.getElementById('item-form'));
                } else {
                    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
                }
            }
        </script>

        <form action="<?php echo JRoute::_('index.php?option=com_jiextensionserver&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
            <div class="row-fluid">
                <!-- Begin Content -->
                <div class="span10 form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_JIEXTENSIONSERVER_GENERALTAB');?></a></li>
                        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                        <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                            <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <!-- Begin Tabs -->
                        <div class="tab-pane active" id="general">
                            <fieldset class="adminform">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('title'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('title'); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('alias'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('alias'); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('eid'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('eid'); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('latest'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('latest'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span6">
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
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                        <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                            <div class="tab-pane" id="attrib-<?php echo $name;?>">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
                                            <p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
                                        <?php endif;
                                        foreach ($this->form->getFieldset($name) as $field) : ?>
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
                            <label class="control-label"><?php echo JText::_('COM_JIEXTENSIONSERVER_TITLE_LABEL'); ?></label>
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
                            <?php echo $this->form->getLabel('state'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('state'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('access_usergroups'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('access_usergroups'); ?>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <!-- End Sidebar -->
            </div>
        </form>
    </div>
</div>