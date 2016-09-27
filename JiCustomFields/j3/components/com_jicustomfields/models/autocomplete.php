<?php 
/*
 * @version     $Id: autocomplete.php 010 2013-01-29 23:21:00Z Anton Wintergerst $
 * @package     Jinfinity Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
class JinfinityModelAutoComplete extends JModelLegacy
{
    function fields() {
        $field = JRequest::getVar('field');
        $value = JRequest::getVar('value');
        if($field==null || $value==null) return "false";
        // Load Database Object
        $db = JFactory::getDBO();
        // Get the cids from the fields index (preserves scope of field)
        $query = 'SELECT fid, value FROM #__jinfinity_fields_index';
        $query.= ' WHERE value LIKE "'.$value.'%" AND fid='.$field;
        $query.= ' ORDER BY value ASC';
        // Set Query
        $db->setQuery($query);
        // Load Data
        $result = $db->loadObjectList();
        
        if($result != null) {
            return $result;
        } else {
            return "false";
        }
    }
}