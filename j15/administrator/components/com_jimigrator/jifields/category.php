<?php
/**
 * @version     $Id: category.php 046 2013-10-25 12:58:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldCategory extends JiMigratorField {
    function renderInput() {
        $html = '<select id="'.$this->get('inputid').'" name="'.$this->get('inputname').'" data-placeholder="'.$this->get('name').'" class="chzn-select">';
        $html.= $this->getOptions();
        $html.= '</select>';
        
        return $html;
    }
    function getOptions() {
        $html = '';
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query = 'SELECT `id`, `title`, `level` FROM #__categories';
        $query.= ' WHERE `extension`="com_content"';
        $query.= ' ORDER BY `lft` ASC';
        // Set Query
        $db->setQuery($query, 0, 100);
        // Load Data
        $results = $db->loadObjectList();
        
        $html.= '<option value="0">- Select Category -</option>';
        if($results!=null) {
            foreach($results as $item) {
                $selected = ($item->id==$this->getValue())? ' selected="selected"':'';
                $prefix = ($item->level>1)? '| '.str_repeat('- ', $item->level-1) : '';
                $html.= '<option value="'.$item->id.'"'.$selected.'>'.$prefix.$item->title.'</option>';
            }
        }
        return $html;
    }
    function getValue($decode=false) {
        $params = JRequest::getVar('jifields');
        if(isset($params[$this->get('id')][$this->get('name')])) {
            return $params[$this->get('id')][$this->get('name')];
        }
        if(isset($this->element->default)) {
            return $this->element->default;
        }
        return;
    }
}