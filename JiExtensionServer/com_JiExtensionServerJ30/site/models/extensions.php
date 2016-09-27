<?php
/**
 * @version     $Id: extensions.php 049 2014-12-03 18:56:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class JiExtensionServerModelExtensions extends JModelList
{
    public $uid = 0;
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'e.id',
                'title', 'e.title',
                'alias', 'e.alias',
                'publisher', 'e.publisher',
                'jversion', 'e.jversion',
                'subversion', 'e.subversion',
                'downloadhits', 'e.downloadhits',
                'updatehits', 'e.updatehits',
                'state', 'e.state',
                'publish_up', 'e.publish_up',
                'publish_down', 'e.publish_down',
                'ordering', 'e.ordering'
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
	 * @since	1.6
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '1');
        $this->setState('filter.published', $published);

        $jversion = $this->getUserStateFromRequest($this->context.'.filter.alias', 'alias');
        $this->setState('filter.alias', $jversion);

        $jversion = $this->getUserStateFromRequest($this->context.'.filter.jversion', 'jversion', '*');
        $this->setState('filter.jversion', $jversion);

        $this->setState('filter.access', true);

        // List state information.
        parent::populateState('s.subversion', 'desc');
	}

	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{

        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $app	= JFactory::getApplication();
        $user = JFactory::getUser($this->uid);

        // Select the required fields from the table.
        $query->select('e.*, b.alias AS jversion, b.attribs AS battribs, s.changelog, s.premium, s.subversion, s.downloadurl, s.updateurl');
        $query->from('#__jiextensions AS e');

        $query->join('LEFT', '#__jiextensions_branches AS b ON (b.eid = e.id)');

        $query->join('LEFT', '#__jiextensions_subversions AS s ON (s.eid = e.id AND s.id = b.latest)');

        // Filter by access level.
        if($user!=null && $access = $this->getState('filter.access')) {
            $groups	= implode(',', $user->getAuthorisedViewLevels());
            $query->where('e.access IN ('.$groups.')');
            //$query->where('s.access IN ('.$groups.')');
        }

        // Filter by published state
        $query->where('(e.state = 1)');
        $query->where('(b.state = 1)');
        $query->where('(s.state = 1)');

        // Filter by alias
        if($alias = $this->getState('filter.alias')) {
            $query->where('e.alias = ' .$db->quote($alias));
        }

        // Filter by jversion
        if($app->getName()=='site') {
            $query->where('(b.alias="free" OR b.alias="pro")');
        }
        /*if($jversion = $this->getState('filter.jversion')) {
            if($jversion!='*') {
                if(version_compare($jversion, '3.0', 'ge')) {
                    $lowerlimit = '3.0';
                } elseif(version_compare($jversion, '1.7', 'ge')) {
                    $lowerlimit = '2.5';
                    $upperlimit = '3.0';
                } elseif(version_compare($jversion, '1.5', 'ge')) {
                    $lowerlimit = '1.5';
                    $upperlimit = '2.5';
                }

                $query->where('(b.alias = ' .$db->quote($lowerlimit).' OR b.alias > ' .$db->quote($lowerlimit).')');
                if(isset($upperlimit)) $query->where('b.alias < ' .$db->quote($upperlimit));
            }
        }*/
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'e.id:') === 0) {
                $query->where('e.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(e.title LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 's.subversion');
        $orderDirn	= $this->state->get('list.direction', 'desc');

        if($orderCol=='s.subversion') {
            $query->order($db->escape('e.alias asc'));
            $query->order($db->escape('b.alias asc'));
            $query->order($db->escape($orderCol.' '.$orderDirn));
        } else {
            $query->order($db->escape($orderCol.' '.$orderDirn));
        }
        return $query;
	}

	public function getItems()
	{
        $params =JComponentHelper::getParams('com_jiextensionserver');
        $jinput = JFactory::getApplication()->input;

        // Check if token is valid to view premium download urls
        $user = JFactory::getUser();
        if($user->id==0) {
            // Check token is valid
            $token = $jinput->get('dlkey', null, 'raw');
            $token = htmlspecialchars_decode($token);
            $model = JModelLegacy::getInstance('Token', 'JiExtensionServerModel');
            $isValidToken = $model->checkToken($token);
            $uid = $model->uid;
            $user = JFactory::getUser($uid);
        } else {
            $uid = $user->id;
        }
        $this->uid = $uid;

        $items	= parent::getItems();

        // check if user has a bundle subscription
        $allowedGroups = $params->get('premium_usergroups', array(0));
        $bundleLicence = false;
        if(in_array(0, $allowedGroups)!=false) {
            $bundleLicence = true;
        } elseif(isset($user->groups)) {
            foreach($user->groups as $group) {
                if(in_array($group, $allowedGroups)) {
                    $bundleLicence = true;
                    break;
                }
            }
        }

        $proitems = array();
        $freeitems = array();
        if(is_array($items)) {
            // map pro and free versions
            foreach($items as $item) {
                if($item->jversion=='pro') {
                    $item->hasfree = 0;
                    $item->haspro = 1;
                    $item->pro = 0;

                    // check for pro access
                    $singleLicence = false;
                    if(!$bundleLicence) {
                        $proparams = new JRegistry($item->attribs);
                        $proparams->merge(new JRegistry($item->battribs));

                        // check if user has a single extension subscription
                        $allowedGroups = $proparams->get('access_usergroups', null);
                        if(is_array($allowedGroups) && isset($user->groups)) {
                            foreach($user->groups as $group) {
                                if(in_array($group, $allowedGroups)) {
                                    $singleLicence = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($bundleLicence || $singleLicence) {
                        // grant access to pro item
                        $item->pro = 1;
                    }

                    $proitems[$item->alias] = $item;
                }
                if($item->jversion=='free') {
                    $item->hasfree = 1;
                    $item->haspro = 0;
                    $item->pro = 0;
                    $freeitems[$item->alias] = $item;
                }
            }
            foreach($freeitems as &$item) {
                if(isset($proitems[$item->alias])) {
                    $proitem = $proitems[$item->alias];
                    $item->changelog = $proitem->changelog;
                    $item->haspro = 1;

                    if($proitem->pro==1) {
                        // replace download with pro item
                        $item = $proitem;
                    } else {
                        continue;
                    }
                }
            }
            // there may be pro items without free and vice versa
            $items = array_merge($proitems, $freeitems);
        }
		return $items;
	}
    public function getUser() {
        $user = JFactory::getUser($this->uid);
        return $user;
    }
    public function getUserSubscriptions() {
        $params =JComponentHelper::getParams('com_jiextensionserver');
        $subpackage = $params->get('subpackage', 'akeebasubs');

        if($this->uid==0) return array();
        switch($subpackage) {
            case 'akeebasubs':
                $subscriptions = $this->getAkeebaSubscriptions();
                break;
            case 'rsmembership':
                $subscriptions = $this->getRSMemberships();
                break;
            default:
                $subscriptions = null;
                break;
        }
        return $subscriptions;
    }
    public function getAkeebaSubscriptions() {
        $db = JFactory::getDbo();
        $query = "SELECT s.publish_down AS end_date, l.title FROM #__akeebasubs_subscriptions AS s
        LEFT JOIN #__akeebasubs_levels l ON (s.akeebasubs_level_id = l.akeebasubs_level_id)
        WHERE s.`user_id`='".$this->uid."' AND s.`enabled`='1'";
        $db->setQuery($query);
        $subscriptions = $db->loadObjectList();
        return $subscriptions;
    }
    public function getRSMemberships() {
        $db = JFactory::getDbo();
        $query = "SELECT u.membership_end AS end_date, m.name AS title FROM #__rsmembership_membership_users u
        LEFT JOIN #__rsmembership_memberships m ON (u.membership_id = m.id)
        WHERE `user_id`='".$this->uid."' AND `u`.`published`='1'";
        $db->setQuery($query);
        $subscriptions = $db->loadObjectList();
        return $subscriptions;
    }
	public function getStart()
	{
		return $this->getState('list.start');
	}
}
