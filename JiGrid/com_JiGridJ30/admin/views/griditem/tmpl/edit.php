<?php
/**
 * @version     $Id: edit.php 035 2013-07-18 10:41:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
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
    JHTML::stylesheet('administrator/components/com_jigrid/assets/css/jigrid.css');
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jigrid.css', 'administrator/components/com_jigrid/assets/css/');
}
// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$params = new JRegistry($params);
$input = JFactory::getApplication()->input;
?>
<div class="jinfinity jigrid<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' row-fluid'; ?>">
    <div class="griditem<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' span12'; ?>">
        <script type="text/javascript">
            Joomla.submitbutton = function(task) {
                if (task == 'griditem.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                    Joomla.submitform(task, document.getElementById('item-form'));
                } else {
                    alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
                }
            }
        </script>

        <form action="<?php echo JRoute::_('index.php?option=com_jigrid&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
            <div class="row-fluid">
                <!-- Begin Content -->
                <div class="span10 form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_JIGRID_GENERALTAB');?></a></li>
                        <?php $fieldsets = $this->form->getFieldsets('attribs'); ?>
                        <?php foreach ($fieldsets as $name => $fieldset) : ?>
                            <?php if($name!='layout'): ?>
                                <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <!-- Begin Tabs -->
                        <div class="tab-pane active" id="general">
                            <fieldset class="adminform">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <div class="control-group">
                                            <?php echo $this->form->getLabel('mode'); ?>
                                            <div class="controls">
                                                <?php echo $this->form->getInput('mode', null, $params->get('mode', 'easy')); ?>
                                            </div>
                                        </div>
                                        <?php if($this->item->alias!='root'): ?>
                                            <div class="control-group advanced-only">
                                                <?php echo $this->form->getLabel('parent_id'); ?><span class="advancedlabel"><?php echo JText::_('COM_JIGRID_ADVANCED'); ?></span>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('parent_id'); ?>
                                                </div>
                                            </div>
                                            <div class="control-group grid-only easy-only">
                                                <?php echo $this->form->getLabel('parent_grid'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('parent_grid'); ?>
                                                </div>
                                            </div>
                                            <div class="control-group row-only easy-only">
                                                <?php echo $this->form->getLabel('parent_row'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('parent_row'); ?>
                                                </div>
                                            </div>
                                            <div class="control-group cell-only easy-only">
                                                <?php echo $this->form->getLabel('parent_cell'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('parent_cell'); ?>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <?php echo $this->form->getLabel('type'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('type'); ?>
                                                </div>
                                            </div>
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
                                                <?php echo $this->form->getLabel('ordering'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput('ordering'); ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <input type="hidden" id="jform_title" name="jform[title]" value="<?php echo $this->form->getValue('title'); ?>" />
                                            <input type="hidden" id="jform_alias" name="jform[alias]" value="<?php echo $this->form->getValue('alias'); ?>" />
                                        <?php endif; ?>
                                    </div>
                                    <?php $fieldset = $fieldsets['layout']; ?>
                                    <div class="span6">
                                        <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
                                            <p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
                                        <?php endif;
                                        foreach ($this->form->getFieldset($name) as $field):
                                            $fieldname = $field->__get('fieldname');
                                            ?>
                                            <?php $class = (strtolower($field->__get('type'))=='typefield')? ' '.$field->getAttribute('controlclass'):''; ?>
                                            <div class="control-group<?php echo $class; ?>">
                                                <?php echo $this->form->getLabel($fieldname, 'attribs'); ?>
                                                <div class="controls">
                                                    <?php echo $this->form->getInput($fieldname, 'attribs'); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <?php foreach ($fieldsets as $name => $fieldset) : ?>
                            <?php if($name!='layout'): ?>
                                <div class="tab-pane" id="attrib-<?php echo $name;?>">
                                    <div class="row-fluid">
                                        <div class="span6">
                                            <?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
                                                <p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
                                            <?php endif;
                                            foreach ($this->form->getFieldset($name) as $field):
                                                $fieldname = $field->__get('fieldname');
                                                ?>
                                                <?php $class = (strtolower($field->__get('type'))=='typefield')? ' '.$field->getAttribute('controlclass'):''; ?>
                                                <div class="control-group<?php echo $class; ?>">
                                                    <?php echo $this->form->getLabel($fieldname, 'attribs'); ?>
                                                    <div class="controls">
                                                        <?php echo $this->form->getInput($fieldname, 'attribs'); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
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
                            <label class="control-label"><?php echo JText::_('COM_JIGRID_TITLE_LABEL'); ?></label>
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
                    </fieldset>
                </div>
                <!-- End Sidebar -->
            </div>
        </form>
        <?php echo $this->form->getInput('modechanger'); ?>
        <?php echo $this->form->getInput('typechanger'); ?>
    </div>
</div>