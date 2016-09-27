<?php
/**
 * @version     $Id: griditems.php 021 2013-07-18 11:35:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class JiGridModelGridItems extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	An optional associative array of configuration settings.
     * @see		JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'id',
                'title', 'title',
                'alias', 'alias',
                'parent_id', 'parent_id',
                'type', 'type',
                'state', 'state',
                'lft', 'lft',
                'rgt', 'rgt',
				'level', 'level',
				'path', 'path'
            );
        }

        parent::__construct($config);
    }
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $type = $this->getUserStateFromRequest($this->context.'.filter.type', 'filter_type', '');
        $this->setState('filter.type', $type);

        $hideroot = $this->getUserStateFromRequest($this->context.'.filter.hideroot', 'filter_hideroot', true);
        $this->setState('filter.hideroot', $hideroot);

        // List state information.
        parent::populateState('lft', 'asc');
    }
    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $user	= JFactory::getUser();
        $app	= JFactory::getApplication();

        // Select the required fields from the table.
        $query->select('*');
        $query->from('#__jigrid');

        // Exclude root
        if($this->getState('filter.hideroot')) $query->where('alias!="root"');

        // Filter by type
        if($type = $this->getState('filter.type')) {
            $query->where('type = ' .$db->quote($type));
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if(is_numeric($published)) {
            $query->where('state = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(state = 0 OR state = 1)');
        }
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(`title` LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'lft');
        $orderDirn	= $this->state->get('list.direction', 'asc');

        //$query->order($db->escape('lft '.$orderDirn));
        $query->order($db->escape($orderCol.' '.$orderDirn));

        return $query;
    }
    /**
     * Method to get a list of jigriditems.
     *
     * @return	mixed	An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }

}