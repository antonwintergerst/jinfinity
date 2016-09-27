<?php
/**
 * @version     $Id: loadlanguage.php 020 2014-12-05 13:52:00Z Anton Wintergerst $
 * @package     Jinfinity Header Field Type for Joomla! 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
defined('_JEXEC') or die;

class JFormFieldLoadLanguage extends JFormField {
    public $type = 'LoadLanguage';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        $this->params = $this->element->attributes();

        $lang = JFactory::getLanguage();
        $lang->load($this->get('name'), JPATH_ADMINISTRATOR, null, false, true);
        return '';
    }

    private function get($var, $default = '')
    {
        return (isset($this->params[$var]) && (string) $this->params[$var] != '') ? (string) $this->params[$var] : $default;
    }
}