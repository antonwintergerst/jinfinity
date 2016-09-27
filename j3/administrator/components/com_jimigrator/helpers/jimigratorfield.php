<?php
/**
 * @version     $Id: jimigratorfield.php 047 2013-10-19 14:02:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiFieldJiMigratorField extends JiField {
    function getValue($decode=false) {
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