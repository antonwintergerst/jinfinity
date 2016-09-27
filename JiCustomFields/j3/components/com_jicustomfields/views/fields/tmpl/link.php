<?php
/**
 * @version     $Id: link.php 082 2014-03-23 09:34:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldLink extends JiCustomField {
    public function renderInput() {
        $value = $this->get('value', 'jiobject');
        ob_start(); ?>
        <div class="jifieldgroup row-fluid <?php echo $this->get('type'); ?>">
            <ul class="jitrow span12 nodrop">
                <li class="jitd linkurl-lbl">
                    <label for="fieldlinkurl<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_LINKURL'); ?></label>
                </li>
                <li class="jitd linkurl">
                    <div class="text input">
                        <input class="inputbox" type="text" id="fieldlinkurl<?php echo $this->get('id'); ?>" name="<?php echo $this->get('inputname'); ?>[value][url]" value="<?php echo $value->get('url'); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd linktitle-lbl">
                    <label for="fieldlinktitle<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_LINKTITLE'); ?></label>
                </li>
                <li class="jitd linktitle">
                    <div class="text input">
                        <input class="inputbox" type="text" id="fieldlinktitle<?php echo $this->get('id'); ?>" name="<?php echo $this->get('inputname'); ?>[value][title]" value="<?php echo $value->get('title'); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd linktarget-lbl">
                    <label for="fieldlinktarget<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_LINKTARGET'); ?></label>
                </li>
                <li class="jitd linktarget">
                    <div class="select input">
                        <?php $choices = array(
                            '_parent'=>JText::_('JICUSTOMFIELDS_LINKTARGET_PARENT'),
                            '_blank'=>JText::_('JICUSTOMFIELDS_LINKTARGET_BLANK'),
                            '_self'=>JText::_('JICUSTOMFIELDS_LINKTARGET_SELF'),
                            '_top'=>JText::_('JICUSTOMFIELDS_LINKTARGET_TOP')
                        ); ?>
                        <select id="fieldlinktarget<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][value][target]" data-placeholder="<?php echo JText::_('JICUSTOMFIELDS_LINKTARGET_PARENT'); ?>" class="chzn-select">
                            <option value=""><?php echo JText::_('JICUSTOMFIELDS_USEGLOBAL'); ?></option>
                            <?php foreach($choices as $val=>$label): ?>
                                <?php $selected = ($val==$value->get('target', '_parent'))? ' selected="selected"':''; ?>
                                <option value="<?php echo $val; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </li>
            </ul>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderInputParams() {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable optionstable">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop">
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_LINK').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd linkglobaltarget-lbl">
                        <label for="fieldlinkglobaltarget<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_LINKGLOBALTARGET'); ?></label>
                    </li>
                    <li class="jitd linkglobaltarget">
                        <div class="select input">
                            <?php $choices = array(
                                '_parent'=>JText::_('JICUSTOMFIELDS_LINKTARGET_PARENT'),
                                '_blank'=>JText::_('JICUSTOMFIELDS_LINKTARGET_BLANK'),
                                '_self'=>JText::_('JICUSTOMFIELDS_LINKTARGET_SELF'),
                                '_top'=>JText::_('JICUSTOMFIELDS_LINKTARGET_TOP')
                            ); ?>
                            <select id="fieldlinkglobaltarget<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][target]" data-placeholder="<?php echo $params->get('target', JText::_('JICUSTOMFIELDS_LINKTARGET_SELF')); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('target', '_self'))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderOutput() {
        $params = $this->get('params');
        $value = $this->get('value', 'jiobject');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if($value->get('url')==null && $params->get('hideempty', '0')==1) return $html;

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        $html.= '<a href="'.$value->get('url', '#').'" target="'.$value->get('target', '_self').'" title="'.$value->get('title').'" class="jilink"><span>'.$value->get('title').'</span></a>';
        $html.= $this->get('suffix', '');
        return $html;
    }
}