<?php
/**
 * @version     $Id: extension.php 016 2013-06-18 10:30:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldExtension extends JFormFieldList
{
    protected $type = 'Extension';
    
    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query = 'SELECT `id`, `title` FROM #__jiextensions';
        $query.= ' WHERE `state`=1';
        $query.= ' ORDER BY `title` ASC';
        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();
        
        $options = array();
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->id, $result->title);
            }
        }
        return $options;
    }
}