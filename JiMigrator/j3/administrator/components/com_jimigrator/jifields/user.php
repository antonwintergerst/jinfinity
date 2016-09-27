<?php
/**
 * @version     $Id: user.php 046 2013-10-25 13:00:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldUser extends JiMigratorField {
    function renderInput() {
        $html = '<select id="'.$this->get('inputid').'" name="'.$this->get('inputname').'">';
        $html.= $this->getOptions();
        $html.= '</select>';
        
        return $html;
    }
    function getOptions() {
        $html = '';
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query = 'SELECT `id`, `name` FROM #__users';
        $query.= ' WHERE `block`="0"';
        $query.= ' ORDER BY `name` ASC';
        // Set Query
        $db->setQuery($query, 0, 100);
        // Load Data
        $results = $db->loadObjectList();
        
        $html.= '<option value="0">- Select User -</option>';
        if($results!=null) {
            foreach($results as $item) {
                $selected = ($item->id==$this->getValue())? ' selected="selected"':'';
                $html.= '<option value="'.$item->id.'"'.$selected.'>'.$item->name.'</option>';
            }
        }
        return $html;
    }
    function getValue($decode=false) {
        $params = JRequest::getVar('params');
        if(isset($params[$this->id][$this->name])) {
            return $params[$this->id][$this->name];
        }
        if(isset($this->element->default)) {
            return $this->element->default;
        }
        return;
    }
}