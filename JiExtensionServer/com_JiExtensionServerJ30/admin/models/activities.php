<?php
/**
 * @version     $Id: activities.php 051 2014-01-04 11:36:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class JiExtensionServerModelActivities extends JModelList
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
                'id', 'a.id',
                'uid', 'a.uid',
                'eid', 'a.eid',
                'site', 'a.site',
                'activity', 'a.activity',
                'date', 'a.date',
                'username', 'u.name',
                'title', 'e.title',
                'jversion', 'e.jversion',
                'subversion', 'e.subversion'
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

        $uid = $this->getUserStateFromRequest($this->context.'.filter.uid', 'filter_uid', '');
        $this->setState('filter.uid', $uid);

        $site = $this->getUserStateFromRequest($this->context.'.filter.site', 'filter_site', '');
        $this->setState('filter.site', $site);

        $fid = $this->getUserStateFromRequest($this->context.'.filter.eid', 'filter_eid', '');
        $this->setState('filter.eid', $fid);

        // List state information.
        parent::populateState('a.date', 'desc');
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
        $query->from('#__jiextensions_activity AS a');

        $query->select('u.name as username');
        $query->join('LEFT', $db->quoteName('#__users').' AS u ON u.id = a.uid');

        $query->select('e.title, b.alias as jversion, s.subversion');
        $query->join('LEFT', $db->quoteName('#__jiextensions_subversions').' AS s ON s.id = a.sid');
        $query->join('LEFT', $db->quoteName('#__jiextensions_branches').' AS b ON b.id = s.bid');
        $query->join('LEFT', $db->quoteName('#__jiextensions').' AS e ON e.id = s.eid');

        // Filter by eid
        if($eid = $this->getState('filter.eid')) {
            $query->where('e.id = ' .$db->quote($eid));
        }
        // Filter by uid
        if($uid = $this->getState('filter.uid')) {
            $query->where('a.uid = ' .$db->quote($uid));
        }
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'uid:') === 0) {
                $query->where('a.uid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(username LIKE '.$search.' OR e.title LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'a.date');
        $orderDirn	= $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol.' '.$orderDirn));

        return $query;
    }
    /**
     * Method to get a list of jiextensions.
     *
     * @return	mixed	An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }
}