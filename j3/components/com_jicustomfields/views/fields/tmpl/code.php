<?php
/**
 * @version     $Id: code.php 081 2014-03-23 09:34:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldCode extends JiCustomField {
	public function renderInput() {
        $value = $this->get('value');
		ob_start(); ?>
		<div class="textarea input <?php echo $this->type; ?>">
            <textarea id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]"><?php echo $value; ?></textarea>
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
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        ob_start();
        eval('?>'.$value.'<?php ');
        $html.= ob_get_clean();
        $html.= $this->get('suffix', '');
        return $html;
    }
}