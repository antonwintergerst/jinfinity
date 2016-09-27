<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: category.php 020 2014-07-18 13:04:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/components/com_content/models/category.php');
class JiCustomFieldsModelCategory extends ContentModelCategory
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'ordering', 'a.ordering',
                'featured', 'a.featured',
                'language', 'a.language',
                'hits', 'a.hits',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'author', 'a.author'
            );
            for($i = 1; $i<=50; $i++) {
                $config['filter_fields'][]= 'fv.'.$i;
            }
        }

        parent::__construct($config);
    }
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('site');
		$pk  = $app->input->getInt('id');

		$this->setState('category.id', $pk);
		// Load the parameters. Merge Global and Menu Item params into new object
		//$params = $app->getParams();
		// #Jinfinity
		$params = $app->getParams('com_content');
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);


		$this->setState('params', $mergedParams);
		$user		= JFactory::getUser();
				// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
			// Filter by start and end dates.
			$nullDate = $db->Quote($db->getNullDate());
			$nowDate = $db->Quote(JFactory::getDate()->toSQL());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}
		else {
			$this->setState('filter.published', array(0, 1, 2));
		}

		// process show_noauth parameter
		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}

		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));

		// filter.order
		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.ordering';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// set limit for query. If list, use parameter. If blog, add blog parameters for limit.
		if (($app->input->get('layout') == 'blog') || $params->get('layout_type') == 'blog')
		{
			$limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else {
			$limit = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		}

		$this->setState('list.limit', $limit);

		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');

		if ($showSubcategories) {
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		$this->setState('layout', $app->input->get('layout'));

	}
	/**
	 * Get the articles in the category
	 *
	 * @return	mixed	An array of articles or false if an error occurs.
	 * @since	1.5
	 */
	function getItems()
	{
		$params = $this->getState()->get('params');
		$limit = $this->getState('list.limit');

		if ($this->_articles === null && $category = $this->getCategory()) {
			//$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
            //$model->setState('params', JFactory::getApplication()->getParams());
			// #Jinfinity
            $model = JModelLegacy::getInstance('Articles', 'JiCustomFieldsModel', array('ignore_request' => true));
			$model->setState('params', $params);
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', $this->getState('filter.published'));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));
			$model->setState('list.ordering', $this->_buildContentOrderBy());
            // #Jinfinity
            $model->setState('filter_order', $this->getState('article.orderby'));
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include articles from subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
			$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));

			if ($limit >= 0) {
				$this->_articles = $model->getItems();

				if ($this->_articles === false) {
					$this->setError($model->getError());
				}
			}
			else {
				$this->_articles = array();
			}

			$this->_pagination = $model->getPagination();
		}

		return $this->_articles;
	}

    /**
     * Build the orderby for the query
     *
     * @return  string	$orderby portion of query
     * @since   1.5
     */
    protected function _buildContentOrderBy()
    {
        $app		= JFactory::getApplication('site');
        $db			= $this->getDbo();
        $params		= $this->state->params;
        $itemid		= $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
        $orderCol	= $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
        $orderDirn	= $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        $orderby	= ' ';

        if (!in_array($orderCol, $this->filter_fields))
        {
            $orderCol = null;
        }

        if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', '')))
        {
            $orderDirn = 'ASC';
        }

        if ($orderCol && $orderDirn)
        {
            $orderby .= $db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ';
        }

        $articleOrderby		= $params->get('orderby_sec', 'rdate');
        $articleOrderDate	= $params->get('order_date');
        $categoryOrderby	= $params->def('orderby_pri', '');
        $secondary			= ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
        $primary			= ContentHelperQuery::orderbyPrimary($categoryOrderby);

        $orderby .= $primary . ' ' . $secondary . ' a.created ';

        // #Jinfinity
        $this->setState('article.orderby', $orderCol);

        return $orderby;
    }

    // #Jinfinity
    public function getFields($fids, $published=1)
    {
        $fieldnames = array();
        if(is_array($fids) && count($fids)>0) {
            $db	= $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('`id`, `title`, `alias`');
            $query->from('#__jifields');
            // allow all fields to be selected
            if(!in_array('0', $fids)) $query->where('`id` IN ("'.implode('","',$fids).'")');
            if(is_numeric($published)) {
                $query->where('`state` = ' . (int) $published);
            } elseif($published === '') {
                $query->where('(`state` = 0 OR `state` = 1)');
            }

            $query->order('`ordering` ASC');

            $db->setQuery($query);
            $fieldnames = $db->loadAssocList('id');
            if(!is_array($fieldnames)) $fieldnames = array();
        }
        return $fieldnames;
    }
}
