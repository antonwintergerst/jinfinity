<?php 
/**
 * @version     $Id: form.field.php 072 2014-10-28 10:23:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Get Jinfinity Parameters
$jiparams = JComponentHelper::getParams('com_jicustomfields');
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

$JiField->prepareInput(); ?>
<ul class="jitrow row-fluid jifield jid<?php echo $JiField->get('id'); ?>">
    <li class="fieldtitle span2">
        <?php echo $JiField->renderInputLabel(); ?>
    </li>
    <li class="fieldvalue span10">
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