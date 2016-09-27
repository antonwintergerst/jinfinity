<?php
/**
 * @version     $Id: checkbox.php 016 2014-12-15 11:50:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldCheckbox extends JiMigratorField {
    public function renderInputLabel() {
        return;
    }
    public function renderInput() {
        $checked = ($this->get('value')==1)? ' checked="checked"':'';
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    jQuery('.checkboxfield').jicheckbox({image:'<?php echo JURI::root(); ?>media/jimigrator/images/ui16/checkbox.png', width:'16', height:'16'});
                    jQuery('.jitogglerbtn').jitoggler({tab:'.jitogglertab'});
                });
            }
        </script>
        <div class="checkbox">
            <input id="<?php echo $this->get('inputid'); ?>" class="checkboxfield jitogglerbtn jid<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>" type="checkbox"<?php echo $checked; ?> />
            <label id="<?php echo $this->inputid.'-lbl'; ?>" for="<?php echo $this->get('inputid'); ?>"<?php if($this->get('description')!=null) echo ' class="hasTip" title="'.$this->get('description').'"'; ?>><?php echo $this->get('label'); ?></label>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function getValue($decode=false) {
        $params = JRequest::getVar('jifields');
        if(isset($params[$this->get('id')])) {
            $value = ($params[$this->get('id')]=='on')? 1 : 0;
            return $value;
        }
        if(isset($this->data->default)) {
            return $this->data->default;
        }
        return;
    }
}