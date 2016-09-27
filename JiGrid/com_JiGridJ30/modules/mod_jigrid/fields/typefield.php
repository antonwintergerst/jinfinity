<?php
/**
 * @version     $Id: typefield.php 030 2013-07-17 11:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('text');

class JFormFieldTypeField extends JFormField
{
    protected $type = 'TypeField';

    public function getAttribute($key, $default=null) {
        $attribs = $this->element->attributes();
        $value = isset($attribs[$key])? (string) $attribs[$key] : $default;
        return $value;
    }
    public function setAttribute($key, $value) {
        $attribs = $this->element->attributes();
        $attribs[$key] = $value;
    }
    public function getLabel() {
        return;
    }
    public function getInput() {
        $type = $this->getAttribute('rtype');
        $this->setAttribute('type', $type);
        $field = JFormHelper::loadFieldType($type, true);
        $field->setForm($this->form);
        $field->setup($this->element, $this->__get('value'), 'params');

        $html = '<div class="control-group fieldtype '.$this->getAttribute('controlclass','').'">';
        $html.= '<div class="control-label">'.$field->getLabel().'</div>';
        $html.= '<div class="controls">'.$field->getInput().'</div>';
        $html.= '</div>';
        return $html;
    }
}