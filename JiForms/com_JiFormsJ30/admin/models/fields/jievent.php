<?php
/**
 * @version     $Id: jievent.php 010 2013-09-05 09:43:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldJiEvent extends JFormFieldList
{
    protected $type = 'JiEvent';

    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query	= $db->getQuery(true);

        $query->select('e.alias, e.title');
        $query->from('#__jiforms_events AS e');

        $query->where('e.state = 1');

        $query->order($db->escape('e.title ASC'));

        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();

        $options = array();
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->alias, $result->title);
            }
        }
        return $options;
    }
}