<?php
/**
 * @version     $Id: subversion.php 020 2013-06-20 10:40:00Z Anton Wintergerst $
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

class JFormFieldSubversion extends JFormFieldList
{
    protected $type = 'Subversion';
    
    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query	= $db->getQuery(true);

        $query->select('s.id, s.subversion');
        $query->from('#__jiextensions_subversions AS s');

        $query->select('e.title');
        $query->join('LEFT', '#__jiextensions AS e ON (e.id = s.eid)');

        $query->where('s.state = 1');

        $query->order($db->escape('e.title ASC'));
        $query->order($db->escape('s.subversion DESC'));

        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();
        
        $options = array();
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->id, $result->title.' #'.$result->subversion);
            }
        }
        return $options;
    }
}