<?php
/**
 * @version     $Id: meta.php 081 2014-03-23 09:34:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldMeta extends JiCustomField {
    public function renderInput() {
        $value = $this->get('value');
        ob_start(); ?>
        <div class="text input <?php echo $this->type; ?>tag">
            <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>" />
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
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_META').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd metaname-lbl">
                        <label for="fieldmetaname<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_METANAME'); ?></label>
                    </li>
                    <li class="jitd metaname">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmetaname<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][name]" value="<?php echo $params->get('name', ''); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd metascheme-lbl">
                        <label for="fieldmetascheme<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_METASCHEME'); ?></label>
                    </li>
                    <li class="jitd metascheme">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmetascheme<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][scheme]" value="<?php echo $params->get('scheme', ''); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd metahttpequiv-lbl">
                        <label for="fieldmetahttpequiv<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_METAHTTPEQUIV'); ?></label>
                    </li>
                    <li class="jitd metahttpequiv">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmetahttpequiv<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][httpequiv]" value="<?php echo $params->get('httpequiv', ''); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd metacharset-lbl">
                        <label for="fieldmetacharset<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_METACHARSET'); ?></label>
                    </li>
                    <li class="jitd metacharset">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmetacharset<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][charset]" value="<?php echo $params->get('charset', ''); ?>" />
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
        $value = $this->get('value');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Continue building HTML string
        $html.= '<meta name="'.$params->get('name').'"';
        $html.= ' content="'.$value.'"';
        if($params->get('scheme')!=null) $html.= ' scheme="'.$params->get('scheme').'"';
        if($params->get('httpequiv')!=null) $html.= ' http-equiv="'.$params->get('httpequiv').'"';
        if($params->get('charset')!=null) $html.= ' charset="'.$params->get('charset').'"';
        $html.= ' />';
        return $html;
    }
}