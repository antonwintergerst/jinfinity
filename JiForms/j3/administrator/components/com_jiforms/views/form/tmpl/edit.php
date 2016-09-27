<?php
/**
 * @version     $Id: edit.php 018 2013-09-23 13:35:00Z Anton Wintergerst $
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
    JHtml::stylesheet('http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
    JHtml::script('http://code.jquery.com/ui/1.10.3/jquery-ui.js');
    // Load the tooltip behavior.
    JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    JHtml::_('behavior.keepalive');
    JHtml::_('formbehavior.chosen', 'select');
    JHtml::script('media/jiforms/libs/edit_area/edit_area_full.js');
} else {
}
// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$input = JFactory::getApplication()->input;

$form = $this->form;
?>
<div class="jiforms form">
    <script type="text/javascript">
        jQuery(document).ready(function() {
            function updatePreview() {
                var rawdata = editAreaLoader.getValue("jform_content");
                jQuery.ajax({
                    url:'index.php?option=com_jiforms&view=form&task=form.preview', type:'post', data:{'content':rawdata}
                }).done(function(response) {
                        console.log('test');
                    jQuery('.jform_content_preview').html(response);
                        jQuery('.isresizable').resizable();
                });
            }
            editAreaLoader.init({
                id: "jform_content"	// id of the textarea to transform
                ,start_highlight: true	// if start with highlight
                ,allow_resize: "no"
                ,allow_toggle: true
                ,word_wrap: true
                ,language: "en"
                ,syntax: "php"
            });
            updatePreview();
            jQuery('#jform_content_updatebtn').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                updatePreview();
            });

        });
        Joomla.submitbutton = function(task) {
            if (task == 'form.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
                var rawdata = editAreaLoader.getValue("jform_content");
                jQuery('#jform_content').val(rawdata);
                Joomla.submitform(task, document.getElementById('item-form'));
            } else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
            }
        }
    </script>

    <form action="<?php echo JRoute::_('index.php?option=com_jiforms&layout=edit&id='.(int) $this->form->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
        <div class="row-fluid">
            <!-- Begin Content -->
            <div class="span10 form-horizontal">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_JIFORMS_GENERALTAB');?></a></li>
                    <?php $fieldSets = $this->jform->getFieldsets('attribs'); ?>
                    <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                        <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <!-- Begin Tabs -->
                    <div class="tab-pane active" id="general">
                        <fieldset class="adminform">
                            <div class="control-group form-inline">
                                <?php echo $this->jform->getLabel('title'); ?> <?php echo $this->jform->getInput('title'); ?>
                            </div>
                            <?php echo $this->jform->getInput('content'); ?>
                        </fieldset>
                    </div>
                    <?php $fieldSets = $this->jform->getFieldsets('attribs'); ?>
                    <?php foreach ($fieldSets as $name => $fieldSet) : ?>
                        <div class="tab-pane" id="attrib-<?php echo $name;?>">
                            <div class="row-fluid">
                                <div class="span6">
                                    <?php foreach ($this->jform->getFieldset($name) as $field) : ?>
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
                            <input id="jform_titlelabel" class="readonly" type="text" readonly="readonly" size="10" value="<?php echo $this->jform->getValue('title'); ?>" name="" aria-invalid="false">

                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->jform->getLabel('id'); ?>
                        <div class="controls">
                            <?php echo $this->jform->getInput('id'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->jform->getLabel('alias'); ?>
                        <div class="controls">
                            <?php echo $this->jform->getInput('alias'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->jform->getLabel('state'); ?>
                        <div class="controls">
                            <?php echo $this->jform->getInput('state'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->jform->getLabel('publish_up'); ?>
                        <div class="controls">
                            <?php echo $this->jform->getInput('publish_up'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo $this->jform->getLabel('publish_down'); ?>
                        <div class="controls">
                            <?php echo $this->jform->getInput('publish_down'); ?>
                        </div>
                    </div>
                </fieldset>
            </div>
            <!-- End Sidebar -->
        </div>
    </form>
    <button id="jform_content_updatebtn" class="btn btn-small btn-info"><span class="icon-refresh"></span>Update Preview</button>
    <div class="previewouter isresizable">
        <div class="jform_content_preview"></div>
    </div>
</div>