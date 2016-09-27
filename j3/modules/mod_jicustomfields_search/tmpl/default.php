<?php
/**
 * @version     $Id: default.php 060 2014-11-18 17:08:00Z Anton Wintergerst $
 * @package     JiCustomFields Search Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('formbehavior.chosen', 'select');
}
$document = JFactory::getDocument();
$document->addStyleSheet('media/mod_jicustomfields/css/jicustomfields.css');
$document->addScript('media/mod_jicustomfields/js/jquery.jicustomfields.js');

$app = JFactory::getApplication();
$jinput = $app->input;
// Preserve itemid
$Itemid = ($jinput->get('Itemid')!=null)? '&Itemid='.$jinput->get('Itemid'):'';
?>
<div class="modjicustomfields search<?php if($params->get('moduleclass_sfx')!=null) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <form class="jform" action="<?php echo JRoute::_(JiCustomFieldsHelperRoute::getSearchRoute($params->get('catid'))); ?>" method="post">
        <div class="fieldscontainer">
            <div class="fieldgroup">
                <div class="textfield">
                    <input class="inputbox searchword" type="text" id="searchword" name="sw" value="<?php echo $searchword; ?>" />
                    <label for="searchword" class="searchword-lbl">Search</label>
                </div>
            </div>
            <?php if($filters!=null): ?>
                <?php $current = 0; ?>
                <?php foreach($filters as $JiField): ?>
                    <?php if(isset($fieldsearch[(int)$JiField->get('id')])) {
                        $values = $fieldsearch[$JiField->get('id')];
                        if(!is_array($values)) {
                            if(strpos($values, ',')!==false) {
                                $values = explode(',', $values);
                            } else {
                                $values = array($values);
                            }
                        }
                    } else {
                        $values = array();
                    }
                    $fparams = $JiField->get('params'); ?>
                    <div class="fieldgroup">
                        <select id="field<?php echo $JiField->get('id'); ?>" name="fs[<?php echo $JiField->get('id'); ?>][]" data-placeholder="<?php echo $JiField->get('title'); ?>" class="inputbox" multiple="multiple" style="width:100%;">
                            <?php switch ($JiField->get('type')):
                                case 'area':
                                    $prefix = $fparams->get('areaprefix');
                                    $suffix = $fparams->get('areasuffix');
                                    foreach($JiField->get('options', array()) as $option):
                                        $selected = (in_array($option->value, $values))? 'selected="selected"' : ''; ?>
                                        <option value="<?php echo htmlspecialchars($option->value); ?>"<?php echo $selected; ?>><?php echo $prefix.$option->value.$suffix; ?></option>
                                    <?php endforeach;
                                break;
                                case 'currency':
                                    $prefix = $fparams->get('currencyprefix');
                                    $suffix = $fparams->get('currencysuffix');
                                    foreach($JiField->get('options', array()) as $option):
                                        $selected = (in_array($option->value, $values))? 'selected="selected"' : ''; ?>
                                        <option value="<?php echo htmlspecialchars($option->value); ?>"<?php echo $selected; ?>><?php echo $prefix.$option->value.$suffix; ?></option>
                                    <?php endforeach;
                                break;
                                case 'tags':
                                    foreach($JiField->get('options', array(), false) as $value):
                                            if(strlen($value)==0) continue;
                                            $selected = (in_array($value, $values))? 'selected="selected"' : ''; ?>
                                            <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo urldecode($value); ?></option>
                                        <?php
                                    endforeach;
                                break;
                                default:
                                    $itemvalues = isset($JiField->options)? $JiField->options : array();
                                    foreach($JiField->get('options', array(), true) as $value=>$label):
                                        if(strlen($value)==0) continue;
                                        if($params->get('hide_empty', 0)==0 || in_array($value, $itemvalues)):
                                            $selected = (in_array($value, $values))? 'selected="selected"' : ''; ?>
                                            <option value="<?php echo htmlspecialchars($value); ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                        <?php endif;
                                    endforeach;
                                break;
                            endswitch; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="actions">
            <input class="btn btn-primary search" type="submit" value="Search" />
            <a class="clear" href="<?php echo JRoute::_('index.php?option=com_jicustomfields&task=search.clear&catid='.$params->get('catid')); ?>" title="Clear Search">Clear Search</a>
        </div>
    </form>
</div>