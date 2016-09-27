<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: search.php 053 2014-11-12 12:28:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of article categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.6
 */
require_once __DIR__ . '/articles.php';
class JiCustomFieldsModelSearch extends JiCustomFieldsModelArticles
{
    /**
     * Model context string.
     *
     * @var		string
     */
    public $_context = 'com_jicustomfields.search';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $input = JFactory::getApplication()->input;
        $user  = JFactory::getUser();

        // List state information
        $limitstart = $input->getUInt('limitstart', 0);
        $this->setState('list.start', $limitstart);

        $params = $this->state->params;
        $limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
        $this->setState('list.limit', $limit);
        $this->setState('list.links', $params->get('num_links'));

        $this->setState('filter.frontpage', true);

        if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
            // filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
        }
        else
        {
            $this->setState('filter.published', array(0, 1, 2));
        }

        // #Jinfinity$app = JFactory::getApplication();
        $app = JFactory::getApplication();

        // check for searchword
        if($params->get('filter_searchword', 0)) {
            $searchword = $params->get('searchword');

            // stack up request searchword
            $request_searchword = $app->input->get('sw', null, 'raw');
            if($request_searchword!=null) $searchword.= $request_searchword;

            $this->setState('filter.searchword', $searchword);

            // persist searchword into userstate
            $app->setUserState('com_jicustomfields.searchword', $searchword);
        }

        // check for field filters
        if($params->get('filter_fields', 0)) {
            $fieldsearch = array();
            for($i=0; $i<5; $i++) {
                $field = $params->get('field'.$i);
                $search = $params->get('field'.$i.'search');
                if($field!=null && $search!=null) $fieldsearch[$field] = $search;
            }

            // stack up request filters
            $request_fieldsearch = $app->input->get('fs', null, 'raw');
            if(is_array($request_fieldsearch)) $fieldsearch = array_replace($fieldsearch, $request_fieldsearch);
            if(count($fieldsearch)>0) $this->setState('filter.fieldsearch', $fieldsearch);

            // persist fieldsearch into userstate
            $app->setUserState('com_jicustomfields.fieldsearch', $fieldsearch);
        }

        // check for category selection
        if ($params->get('filter_category') && implode(',', $params->get('filter_category')) == true)
        {
            $filterCategories = $params->get('filter_category');
            if(is_array($filterCategories) && count($filterCategories)==1) $filterCategories = $filterCategories[0];
            $this->setState('filter.category_id', $filterCategories);
        }
        // set the depth of the category query based on parameter
        $showSubcategories = $params->get('show_subcategory_content', '0');

        if ($showSubcategories) {
            $this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
            $this->setState('filter.subcategories', true);
        }
    }

    /**
     * Method to get a list of articles.
     *
     * @return  mixed  An array of objects on success, false on failure.
     */
    public function getItems()
    {
        $params = clone $this->getState('params');
        $limit = $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links');
        if ($limit > 0)
        {
            $this->setState('list.limit', $limit);
            return parent::getItems();
        }
        return array();

    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id	A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= $this->getState('filter.frontpage');

        return parent::getStoreId($id);
    }

    /**
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Set the blog ordering
        $params = $this->state->params;
        $articleOrderby = $params->get('orderby_sec', 'rdate');
        $articleOrderDate = $params->get('order_date');
        $categoryOrderby = $params->def('orderby_pri', '');
        $secondary = ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
        $primary = ContentHelperQuery::orderbyPrimary($categoryOrderby);

        $orderby = $primary . ' ' . $secondary . ' a.created DESC ';
        $this->setState('list.ordering', $orderby);
        $this->setState('list.direction', '');

        // Create a new query object.
        $query = parent::getListQuery();

        // Filter by frontpage.
        /*if ($this->getState('filter.frontpage'))
        {
            $query->join('INNER', '#__content_frontpage AS fp ON fp.content_id = a.id');
        }*/

        // Filter by categories
        /*$filterCategories = $this->getState('filter.category_id');

        if (is_array($filterCategories) && !in_array('', $filterCategories))
        {
            $query->where('a.catid IN (' . implode(',', $filterCategories) . ')');
        }*/

        return $query;
    }
}