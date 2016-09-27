<?php
/**
 * @version     $Id: hidden.php 021 2013-10-25 12:59:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldHidden extends JiMigratorField {
    public function getInput() {
        return;
    }
    public function getLabel() {
        return;
    }
    public function renderLabel() {
        return;
    }
    public function renderInputLabel() {
        return;
    }
    public function getValue($decode=false) {
        $params = JRequest::getVar('jifields');
        if(isset($params[$this->get('id')][$this->get('name')])) {
            return $params[$this->get('id')][$this->get('name')];
        }
        if(isset($this->data->default)) {
            return $this->data->default;
        }
        return;
    }
}