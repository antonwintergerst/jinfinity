<?php
/**
 * @version     $Id: youtube.php 081 2014-03-23 09:34:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldYouTube extends JiCustomField {
    public function renderInput() {
        $value = $this->get('value');
        ob_start(); ?>
        <div class="jifieldgroup row-fluid <?php echo $this->type; ?>">
            <ul class="jitrow span12 nodrop">
                <li class="jitd span12 youtubeurl-lbl">
                    <label for="<?php echo $this->get('inputid'); ?>"><?php echo JText::_('JICUSTOMFIELDS_YOUTUBEURL'); ?></label>
                </li>
                <li class="jitd span12 youtubeurl">
                    <div class="text input">
                        <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>" />
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
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_YOUTUBE').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd youtubewidth-lbl">
                        <label for="fieldyoutubewidth<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_YOUTUBEWIDTH'); ?></label>
                    </li>
                    <li class="jitd youtubewidth">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldyoutubewidth<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][width]" value="<?php echo $params->get('width', 560); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd youtubeheight-lbl">
                        <label for="fieldyoutubeheight<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_YOUTUBEHEIGHT'); ?></label>
                    </li>
                    <li class="jitd youtubeheight">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldyoutubeheight<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][height]" value="<?php echo $params->get('height', 315); ?>" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function prepareStore() {
        $value = $this->get('value');
        // Find video ID
        parse_str(parse_url($value, PHP_URL_QUERY), $vars);
        if(isset($vars['v'])) {
            $value = $vars['v'];
        }
        $value = str_replace('http://www.youtube.com/embed/', '', $value);
        $this->setValue($value);
    }
    public function renderOutput() {
        $params = $this->get('params');
        $value = $this->get('value');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Prepare value parts
        $width = (int) $params->get('width', 560);
        $height = (int) $params->get('height', 315);

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        $html.= '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$value.'" frameborder="0" allowfullscreen></iframe>';
        $html.= $this->get('suffix', '');
        return $html;
    }
}