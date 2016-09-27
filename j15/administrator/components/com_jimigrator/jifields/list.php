<?php
/**
 * @version     $Id: list.php 046 2013-10-25 12:59:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldList extends JiMigratorField {
    function renderInput() {
        $html = '<select id="'.$this->get('inputid').'" name="'.$this->get('inputname').'" data-placeholder="'.$this->get('name').'" class="chzn-select">';
        $html.= $this->getOptions();
        $html.= '</select>';
        
        return $html;
    }
    function getOptions() {
        $html = '';
		$params = $this->get('params');
		$options = $params->get('options');
		
        if(is_array($options)) {
            foreach($options as $value=>$label) {
                $selected = ($value==$this->getValue())? ' selected="selected"':'';
                $html.= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
            }
        }
        return $html;
    }
    function getValue($decode=false) {
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