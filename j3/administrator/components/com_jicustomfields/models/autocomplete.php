<?php 
/*
 * @version     $Id: autocomplete.php 030 2013-05-07 12:55:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access 
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.application.component.model');

class JiCustomFieldsModelAutoComplete extends JModelLegacy
{
    function fields() {
        $field = JRequest::getVar('field');
        $value = JRequest::getVar('value');
        if($field==null || $value==null) return "false";
        // Load Database Object
        $db = JFactory::getDBO();
        // Get the cids from the fields index (preserves scope of field)
        $query = 'SELECT `fid`, `value` FROM #__jifields_index';
		if(version_compare(JVERSION, '3.0.0', 'ge')) {
			$search = $db->escape($value, true).'%';
		} else {
			$search = $db->getEscaped($value, true).'%';
		}
        $query.= ' WHERE `value` LIKE '.$db->quote($search, false).' AND `fid`='.$db->quote($field);
        $query.= ' ORDER BY `value` ASC';
        // Set Query
        $db->setQuery($query);
        // Load Data
        $result = $db->loadObjectList();
		// Check for errors
		if($db->getErrorMsg()) {
            return $db->getErrorMsg();
        }
        
        if($result!=null) {
            return $result;
        } else {
            return "false";
        }
    }
}