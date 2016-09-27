<?php 
/*
 * @version     $Id: useraccess.php 017 2012-10-16 13:37:00Z Anton Wintergerst $
 * @package     JiFileGallery Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2012 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare( JVERSION, '1.6.0', 'ge' )) {
    // Joomla 1.7+ Compatibility
    jimport('joomla.form.helper');
    JFormHelper::loadFieldClass('list');
    
    class JFormFieldUserAccess extends JFormFieldList 
    {
    
        protected $type = 'UserAccess';
     
        public function getOptions()
        {
            // Load Database Object
            $db = JFactory::getDBO();
            // Query Statement
            $query = 'SELECT `id`, `title` FROM #__usergroups';
            // Set Query
            $db->setQuery( $query );
            // Load Data
            $usergroups = $db->loadObjectList();
            $options[] = JHTML::_('select.option',  0, 'All Users');
            if($usergroups!=null) {
                foreach($usergroups as $option) {
                    $options[] = JHTML::_('select.option',  $option->id, $option->title );
                }
            }
            return $options;
        }
    }
} else {
    // Joomla 1.5 Compatibility
    class JElementUserAccess extends JElement
    {
        var $_name = 'UserAccess';
    
        function fetchElement($name, $value, &$node, $control_name)
        {
            $fieldName = $control_name.'['.$name.']';
            // Load Database Object
            $db =& JFactory::getDBO();
            // Query Statement
            $query = 'SELECT `id`, `name` FROM #__core_acl_aro_groups';
            // Set Query
            $db->setQuery( $query );
            // Load Data
            $results = $db->loadObjectList();
            $options[] = JHTML::_('select.option',  0, 'All Users');
            if($results!=null) {
                foreach ($results as $result) {
                    if($result->id!=null) $options[] = JHTML::_('select.option', $result->id, $result->name);
                }
            }
            $attribs    = ' ';
            if ($v = $node->attributes( 'size' )) {
                $attribs    .= 'size="'.$v.'"';
            }
            if ($v = $node->attributes( 'class' )) {
                $attribs    .= 'class="'.$v.'"';
            } else {
                $attribs    .= 'class="inputbox"';
            }
            if ($m = $node->attributes( 'multiple' ))
            {
                $attribs    .= ' multiple="multiple"';
                $fieldName       .= '[]';
            }
            
            $output= JHTML::_('select.genericlist', $options, $fieldName, $attribs, 'value', 'text', $value );
    
            return $output;
        }
    }
}
