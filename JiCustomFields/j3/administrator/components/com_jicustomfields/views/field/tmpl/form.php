<?php
/**
 * @version     $Id: form.php 019 2014-11-20 11:54:00Z Anton Wintergerst $
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
// Get JiResources Parameters
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
}
// Get Fieldtypes
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
$JiFieldHelper = new JiCustomFieldHelper();
$JiFieldHelper->setPaths(array(
    JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_jicustomfields'.DS.'fields',
    JPATH_SITE.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'fields'.DS.'tmpl'
));
$fieldtypes = $JiFieldHelper->getFieldTypes();

$document = JFactory::getDocument();
$document->addStyleSheet('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
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
?>

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

<fieldset class="jicustomfields">
    <div id="fieldscontainer" class="adminform fieldscontainer jitable container-fluid">
        <?php if($this->jifield!=null):
            $JiField = $this->jifield; ?>
            <?php $value = (isset($this->item->fields[$JiField->get('id')]))? $this->item->fields[$JiField->get('id')] : ''; ?>
            <?php $JiField->prepareInput(); ?>
            <?php echo $JiField->renderInputScript(); ?>
            <ul class="jitrow row-fluid jifield jid<?php echo $JiField->get('id'); ?>">
                <script type="text/javascript">
                    if(typeof jQuery!='undefined') {
                        jQuery(document).ready(function() {
                            var JiField = jQuery('.jid<?php echo $JiField->get('id'); ?>').jifield({'id':'<?php echo $JiField->get('id'); ?>', 'name':'<?php echo $JiField->get('name'); ?>', 'type':'<?php echo $JiField->get('type'); ?>'}, '<?php echo $JiField->get('type'); ?>');
                            if(JiField!=null) JiField.prepareInput();
                        });
                    }
                </script>
                <li class="fieldvalue span12">
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
                    <input type="hidden" id="savefield<?php echo $JiField->get('id'); ?>" name="jifields[<?php echo $JiField->get('id'); ?>][save]" value="1" />
                </li>
            </ul>
        <?php endif; ?>
    </div>
    <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
</fieldset>