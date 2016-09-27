<?php
/**
 * @version     $Id: edit.php 005 2014-10-27 11:55:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
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
if(version_compare(JVERSION, '3.0', 'ge')) {
    JHTML::stylesheet('media/jicustomfields/css/jicustomfields.css');
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jicustomfields.css', 'media/jicustomfields/css/');
}
// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$input = JFactory::getApplication()->input;
?>
<div class="jinfinity jicustomfields<?php if(version_compare(JVERSION, '3.0', 'l')) echo ' row-fluid'; ?>">
    <div class="catmap<?php if(version_compare(JVERSION, '3.0', 'l')) echo ' span12'; ?>">
        <script type="text/javascript">
            Joomla.submitbutton = function(task) {
                if (task == 'catmap.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                    Joomla.submitform(task, document.getElementById('item-form'));
                } else {
                    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
                }
            }
        </script>

        <form action="<?php echo JRoute::_('index.php?option=com_jicustomfields&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
            <div class="row-fluid">
                <!-- Begin Content -->
                <div class="span10 form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JICUSTOMFIELDS_GENERAL_TAB');?></a></li>
                        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                        <?php if($fieldSets):
                            foreach($fieldSets as $name=>$fieldSet) : ?>
                                <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
                            <?php endforeach;
                        endif; ?>
                    </ul>

                    <div class="tab-content">
                        <!-- Begin Tabs -->
                        <div class="tab-pane active" id="general">
                            <fieldset class="adminform">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('allcats'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('allcats'); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('catid'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('catid'); ?>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('fid'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('fid'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clr"></div>
                            </fieldset>
                        </div>
                        <?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
                        <?php if($fieldSets):
                            foreach($fieldSets as $name=>$fieldSet) : ?>
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
                            <?php endforeach;
                        endif; ?>
                        <!-- End Tabs -->
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