<?php
/**
 * @version     $Id: separator.php 080 2014-03-08 14:28:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldSeparator extends JiCustomField {
    public function renderInput() {
        $html = $this->renderInputLabel();
        return $html;
    }
    public function renderInputParams() {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable optionstable">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop">
                    <li class="jitd span12 header"><?php echo JText::_('COM_JICUSTOMFIELDS_YOUTUBELABEL').' '.JText::_('COM_JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <div class="text input <?php echo $this->type; ?>">
                        <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $this->get('value'); ?>" />
                    </div>
                    <li class="jitd youtubewidth">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldyoutubewidth<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][width]" value="<?php echo $params->get('width', 560); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd youtubeheight-lbl">
                        <label for="fieldyoutubeheight<?php echo $this->get('id'); ?>"><?php echo JText::_('COM_JICUSTOMFIELDS_YOUTUBEHEIGHT'); ?></label>
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
    public function renderOutput() {
        // Start building HTML string
        $html = '';
        return $html;
    }
}