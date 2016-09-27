<?php
/**
 * @version     $Id: hidden.php 080 2014-03-08 14:28:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldHidden extends JiCustomField {
    public function renderInput() {
        $value = $this->get('value');
        ob_start(); ?>
        <div class="text input <?php echo $this->type; ?>tag">
            <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>" />
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