<?php
/**
 * @version     $Id: catmaps.php 005 2014-10-28 10:36:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiCustomFieldsModelCatMaps extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'cat_title',
                'title',
                'state',
                'ordering'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('filter.catid', $pk);

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // List state information.
        parent::populateState('title', 'asc');
    }


    protected function getListQuery()
    {
        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $user	= JFactory::getUser();
        $app	= JFactory::getApplication();

        // Select the required fields from the table.
        $query->select('map.*');
        $query->from('#__jifields_map AS map');

        $query->select('f.title');
        $query->join('left', '#__jifields AS f ON f.id = map.fid');

        $query->select('c.title AS cat_title');
        $query->join('left', '#__categories AS c ON c.id = map.catid');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('f.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(f.`title` LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'title');
        $orderDirn	= $this->state->get('list.direction', 'asc');
        $query->order($db->escape('`'.$orderCol.'` '.$orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }
}