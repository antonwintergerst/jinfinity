<?php
/**
 * @version     $Id: actions.php 013 2013-09-23 13:58:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiFormsModelActions extends JModelList
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
                'fid', 'fid',
                'event', 'event',
                'title', 'title',
                'state', 'state',
                'publish_up', 'publish_up',
                'publish_down', 'publish_down',
                'ordering', 'ordering'
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

        $event = $this->getUserStateFromRequest($this->context.'.filter.event', 'filter_event', '');
        $this->setState('filter.event', $event);

        $fid = $this->getUserStateFromRequest($this->context.'.filter.fid', 'filter_fid', '');
        $this->setState('filter.fid', $fid);

        // List state information.
        parent::populateState('title', 'asc');
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
        $query->select('a.*');
        $query->from('#__jiforms_actions AS a');

        // Join over the events
        $query->join('LEFT', $db->quoteName('#__jiforms_events').' AS e ON e.alias = a.event');
        $query->select('e.title AS event_title');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
            //$query->where('e.state = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(a.state = 0 OR a.state = 1)');
            //$query->where('(e.state = 0 OR e.state = 1)');
        }
        // Filter by form
        if($fid = $this->getState('filter.fid')) {
            $query->where('a.fid = ' .(int)$fid);
        }
        // Filter by event
        if($event = $this->getState('filter.event')) {
            $query->where('a.event = ' .$db->Quote($event));
        }
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.name LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'a.title');
        $orderDirn	= $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol.' '.$orderDirn));

        return $query;
    }
    /**
     * Method to get a list of jiform actions.
     *
     * @return	mixed	An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }
}