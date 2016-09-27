<?php
/**
 * @version     $Id: form.php 085 2014-12-19 11:37:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$language = JFactory::getLanguage();
$language->load('com_jicustomfields');

// Get JiCustomFields Parameters
$jiparams = JComponentHelper::getParams('com_jicustomfields');

if(!version_compare(JVERSION, '3.0.0', 'ge')) JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');

// Get the Application
$app = JFactory::getApplication();
$jinput = $app->input;
$appname = $app->getName();

// set editmode
$option = $jinput->get('option');
if($appname=='administrator' && $option=='com_jicustomfields') {
    // com_jicustomfields management
    $editmode = 'admin';
    $candelete = true;
} else {
    if($appname=='administrator' && $jiparams->get('fields_admin_manager', 1)==1) {
        // admin management
        $editmode = 'admin';
    } elseif($jiparams->get('fields_site_manager', 1)==1) {
        // front-end management
        $editmode = 'admin';
    } else {
        $editmode = 'site';
    }
    $candelete = false;
}

// Get Fieldtypes
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
$JiFieldHelper = new JiCustomFieldHelper();
$fieldtypes = $JiFieldHelper->getFieldTypes();

$document = JFactory::getDocument();
$document->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');

// every field type script and stylesheet should be loaded here
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('media/jicustomfields/css/jicustomfields.css');
    JHTML::stylesheet('media/jicustomfields/css/jiautocomplete.css');
    JHTML::stylesheet('media/jicustomfields/css/jimediamanager.css');
    JHTML::stylesheet('media/jicustomfields/css/jiuploader.css');
    JHtml::_('formbehavior.chosen', 'select');
    JHTML::script('media/jicustomfields/js/jquery.jiautocomplete.js');
    JHTML::script('media/jicustomfields/js/jquery.jitoggler.js');
    JHTML::script('media/jicustomfields/js/jquery.jisortable.js');
    JHTML::script('media/jicustomfields/js/jquery.jifields.js');
    JHTML::script('media/jicustomfields/js/jquery.jiuploader.js');
    JHTML::script('media/jicustomfields/js/jquery.jimediamanager.js');
} else {
    JHTML::_('stylesheet', 'jicustomfields.css', 'media/jicustomfields/css/');
    JHTML::_('stylesheet', 'jicustomfields.j25.css', 'media/jicustomfields/css/');
    JHTML::_('stylesheet', 'jiautocomplete.css', 'media/jicustomfields/css/');
    JHTML::_('stylesheet', 'jimediamanager.css', 'media/jicustomfields/css/');
    JHTML::_('stylesheet', 'jiuploader.css', 'media/jicustomfields/css/');
    JHtml::_('jquery.framework');
    JHTML::_('script', 'jquery.jiautocomplete.js', 'media/jicustomfields/js/');
    JHTML::_('script', 'jquery.jitoggler.js', 'media/jicustomfields/js/');
    JHTML::_('script', 'jquery.jisortable.js', 'media/jicustomfields/js/');
    JHTML::_('script', 'jquery.jifields.js', 'media/jicustomfields/js/');
    JHTML::_('script', 'jquery.jiuploader.js', 'media/jicustomfields/js/');
    JHTML::_('script', 'jquery.jimediamanager.js', 'media/jicustomfields/js/');
}

$data = new stdClass();
if(isset($this->form)) {
    $data = null;
    foreach ((Array)$this->form as $key => $val) {
        if($val instanceof JRegistry){
            $data = &$val;
            break;
        }
    }
    $data = $data->toObject();
}
if($jiparams->get('debug',0)==1){
    echo '<pre>';
    print_r($this->jifields);
    echo '</pre>';
}
?>
<fieldset class="jicustomfields customfields">
    <script type="text/javascript">
        var jicustomfields = null;
        if(typeof jQuery!='undefined') {
            jQuery(document).ready(function() {
                jicustomfields = jQuery('.jicustomfields').jicustomfields({contentid:'<?php echo $this->item->id; ?>', contenttype:'type'});
                jQuery('.fieldscontainer').jitoggler({btn:'.jitogglerbtn', tab:'.jitogglertab'});
                jQuery('.fieldscontainer').jisortable({btn:'.jisortbtn', tab:'.jifield'});
            });
        }
    </script>
    <div id="fieldscontainer" class="adminform fieldscontainer jitable container-fluid">
        <?php if($this->jifields != null):
            foreach($this->jifields as $JiField): ?>
                <?php $value = (isset($this->item->fields[$JiField->get('id')]))? $this->item->fields[$JiField->get('id')] : ''; ?>
                <?php $JiField->prepareInput();
                // shortcut to fieldparams
                $fieldparams = $JiField->get('params'); ?>
                <ul class="jitrow row-fluid jifield jid<?php echo $JiField->get('id'); ?> type-<?php echo $JiField->get('type'); ?> alias-<?php echo $JiField->get('alias'); ?>">
                    <script type="text/javascript">
                        if(typeof jQuery!='undefined') {
                            jQuery(document).ready(function() {
                                var field = {
                                    'e':'.jifield.jid<?php echo $JiField->get('id'); ?>',
                                    'id':'<?php echo $JiField->get('id'); ?>',
                                    'title':'<?php echo $JiField->get('title'); ?>',
                                    'type':'<?php echo $JiField->get('type'); ?>'
                                };
                                if(jicustomfields!=null) jicustomfields.fields.push(field);
                                var JiField = jQuery(field.e).jifield(field, field.type);
                                if(JiField!=null) JiField.prepareInput();
                            });
                        }
                    </script>
                    <li class="fieldtitle span2">
                        <?php echo $JiField->renderInputLabel(); ?>
                    </li>
                    <li class="fieldvalue span10">
                        <?php if($fieldparams->get('showhint') && strlen($fieldparams->get('hint'))>0): ?>
                            <div class="fieldhint">
                                <?php echo $fieldparams->get('hint'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="row-fluid">
                            <div class="fieldvalue <?php echo ($editmode=='admin')? 'span10':'span12'; ?>">
                                <?php echo $JiField->renderInputScript(); ?>
                                <?php echo $JiField->renderInput(); ?>
                            </div>
                            <?php if($editmode=='admin'): ?>
                                <div class="fieldactions span2">
                                    <div class="jitable">
                                        <ul class="jitrow row-fluid nodrop">
                                            <li class="jitd span3">
                                                <a class="jibtn icon26 jitogglerbtn jid<?php echo $JiField->get('id'); ?>" href="#" rel="<?php echo $JiField->get('inputid'); ?>" title="<?php echo JText::_('JICUSTOMFIELDS_TOGGLEPARAMS'); ?>">
                                                    <span class="jiicon params"><?php echo JText::_('JICUSTOMFIELDS_TOGGLEPARAMS'); ?></span>
                                                </a>
                                            </li>
                                            <li class="jitd span3">
                                                <a class="jibtn icon26 jiremovebtn" href="#" rel="<?php echo $JiField->get('inputid'); ?>" title="<?php echo JText::_('JICUSTOMFIELDS_REMOVEFIELD'); ?>">
                                                    <span class="jiicon subtract"><?php echo JText::_('JICUSTOMFIELDS_REMOVE'); ?></span>
                                                </a>
                                            </li>
                                            <li class="jitd span3">
                                                <a class="jibtn icon26 jideletebtn" href="#" rel="<?php echo $JiField->get('inputid'); ?>" title="<?php echo JText::_('JICUSTOMFIELDS_DELETEFIELD'); ?>">
                                                    <span class="jiicon trash"><?php echo JText::_('JICUSTOMFIELDS_DELETE'); ?></span>
                                                </a>
                                            </li>
                                            <li class="jitd span3">
                                                <a class="jibtn icon26 jisortbtn jid<?php echo $JiField->get('id'); ?>" href="#" rel="<?php echo $JiField->get('inputid'); ?>" title="<?php echo JText::_('JICUSTOMFIELDS_CHANGEORDER'); ?>">
                                                    <span class="jiicon sort"><?php echo JText::_('JICUSTOMFIELDS_SORT'); ?></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if($editmode=='admin'): ?>
                            <div class="row-fluid">
                                <?php
                                // Push the current field onto the common params field
                                $CommonParamsField = $JiFieldHelper->loadType($JiField, 'commonparams');
                                // Pass common arrays to avoid unnecessary reloads
                                $CommonParamsField->fieldtypes = $fieldtypes;

                                $CommonParamsField->prepareInput();
                                $JiField->prepareInput(); ?>
                                <div class="paramsbox span12 jitogglertab jid<?php echo $JiField->get('id'); ?>">
                                    <?php echo $CommonParamsField->renderInput(); ?>
                                    <?php echo $JiField->renderInputParams(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" id="savefield<?php echo $JiField->get('id'); ?>" name="jifields[<?php echo $JiField->get('id'); ?>][save]" value="1" />
                    </li>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if(isset($data->catid)): ?>
            <input type="hidden" class="jicatid" name="jicatid" value="<?php echo $data->catid; ?>" />
        <?php endif; ?>
    </div>
    <?php if($editmode=='admin'): ?>
        <div class="jitable fieldcreator">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop common">
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_CREATEPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span12 nodrop common">
                    <li class="jitd">
                        <p>Create a new field and assign it to the current category.</p>
                    </li>
                </ul>
                <ul class="jitrow span12 nodrop common">
                    <li class="jitd">
                        <div class="input-append fieldactions">
                            <select id="fieldtype" name="fieldtype" data-placeholder="Field Type" class="form-control inputbox">
                                <option value=""><?php echo JText::_('JICUSTOMFIELDS_SELECT_TYPE'); ?></option>
                                <?php foreach($fieldtypes as $JiFieldType): ?>
                                    <?php JText::script('JICUSTOMFIELDS_'.$JiFieldType->get('type').'_DESC'); ?>
                                    <?php if($JiFieldType->get('group')!='system'): ?>
                                        <?php $selected = ($JiFieldType->get('name')=='textfield')? ' selected="selected"': ''; ?>
                                        <option value="<?php echo $JiFieldType->get('name'); ?>"<?php echo $selected; ?>><?php echo JText::_($JiFieldType->get('label')); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" class="form-control" id="fieldtitle" name="fieldtitle" placeholder="<?php echo JText::_('JICUSTOMFIELDS_FIELDTITLE'); ?>">
                            <a class="btn addfieldbtn" title="<?php echo JText::_('JICUSTOMFIELDS_CREATEFIELD'); ?>" href="#"><span class="icon-save-new"></span> <?php echo JText::_('JICUSTOMFIELDS_CREATEFIELD'); ?></a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
</fieldset>