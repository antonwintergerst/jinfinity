<?php
/**
 * @version     $Id: branch.php 016 2013-06-18 10:30:00Z Anton Wintergerst $
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

class JFormFieldBranch extends JFormFieldList
{
    protected $type = 'Branch';
    
    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('b.id, b.title AS branch');
        $query->from('#__jiextensions_branches AS b');

        $query->select('e.title');
        $query->join('LEFT', '#__jiextensions AS e ON (e.id = b.eid)');

        $query->where('b.state = 1');

        $query->order($db->escape('e.title ASC'));
        $query->order($db->escape('b.alias DESC'));
        
        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();
        
        $options = array();
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->id, $result->title.'_'.$result->branch);
            }
        }
        return $options;
    }
}