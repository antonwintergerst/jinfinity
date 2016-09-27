<?php
/**
 * @version     $Id: language.php 010 2014-06-18 19:41:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldLanguage extends JFormField
{

    protected $type = 'Language';

    protected function getLabel() {
        return;
    }

    protected function getInput()
    {
        $language = $this->getAttrValue('name');
        $lang = JFactory::getLanguage();
        $lang->load($language);
        return;
    }

    private function getAttrValue($var, $default = '')
    {
        $attrs = $this->element->attributes();
        return (isset($attrs[$var]) && (string) $attrs[$var] != '') ? (string) $attrs[$var] : $default;
    }
}