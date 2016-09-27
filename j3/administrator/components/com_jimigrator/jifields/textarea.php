<?php
/**
 * @version     $Id: textarea.php 010 2014-02-27 19:17:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldTextArea extends JiMigratorField {
    function renderInput() {
        ob_start(); ?>
            <div class="textarea input <?php echo $this->type; ?>">
                <textarea id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>"><?php echo $this->get('value'); ?></textarea>
            </div>
        <?php
        $html = ob_get_clean();
        return $html;
    }
    public function getValue($decode=false) {
        $params = JRequest::getVar('jifields');
        if(isset($params[$this->get('id')])) {
            return $params[$this->get('id')];
        }
        if(isset($this->data->default)) {
            return $this->data->default;
        }
        return;
    }
}