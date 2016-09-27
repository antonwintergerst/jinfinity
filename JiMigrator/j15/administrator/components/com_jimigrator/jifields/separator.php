<?php
/**
 * @version     $Id: separator.php 038 2014-12-16 13:06:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldSeparator extends JiMigratorField {
    function renderInput() {
        $html = '<span class="jiseparator">';
        if($this->get('label')!=null) $html.= JText::_($this->get('label'));
        $html.= '</span>';
        
        return $html;
    }
    function renderInputLabel() {
        return;
    }
}