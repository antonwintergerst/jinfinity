<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: articles.php 037 2014-11-18 09:35:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
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
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of articles.
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.6
 */
 
require_once(JPATH_SITE.'/components/com_content/models/articles.php');
class JiCustomFieldsModelArticles extends ContentModelArticles
{
    public $fieldsearch = null;
    public $fieldfilter = null;
    public $mode = 'component';
    public $catsearch = null;

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   12.2
     */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
        $app = JFactory::getApplication();

        // #Jinfinity
        // get fieldsearch
        $fieldsearch = $app->input->get('fs', null, 'raw');
        $this->setState('filter.fieldsearch', $fieldsearch);
        // persist fieldsearch into userstate
        if(count($fieldsearch)>0) $app->setUserState('com_jicustomfields.fieldsearch', $fieldsearch);

        // get searchword
        $searchword = $app->input->get('sw', null, 'raw');
        $this->setState('filter.searchword', $searchword);
        // persist fieldsearch into userstate
        $app->setUserState('com_jicustomfields.searchword', $searchword);

        // List state information
        $value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $orderCol = $app->input->get('filter_order', 'a.ordering');

        if (!in_array($orderCol, $this->filter_fields))
        {
            $orderCol = 'a.ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
        {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        //#Jinfinity
        //$params = $app->getParams();
        $params = $app->getParams('com_content');
        $this->setState('params', $params);
        $user = JFactory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_content')) && (!$user->authorise('core.edit', 'com_content')))
        {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
        }

        $this->setState('filter.language', JLanguageMultilang::isEnabled());

        // Process show_noauth parameter
        if (!$params->get('show_noauth'))
        {
            $this->setState('filter.access', true);
        }
        else
        {
            $this->setState('filter.access', false);
        }

        $this->setState('layout', $app->input->getString('layout'));
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
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.introtext, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// use created if modified is 0
				'CASE WHEN a.modified = ' . $db->q($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
					'a.modified_by, uam.name as modified_by_name,' .
				// use created if publish_up is 0
				'CASE WHEN a.publish_up = ' . $db->q($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
					'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
					'a.hits, a.xreference, a.featured,'.' '.$query->length('a.fulltext').' AS readmore'
			)
		);

		// Process an Archived Article layout
		if ($this->getState('filter.published') == 2) {
			// If badcats is not null, this means that the article is inside an archived category
			// In this case, the state is set to 2 to indicate Archived (even if the article state is Published)
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END AS state'));
		}
		else {
			// Process non-archived layout
			// If badcats is not null, this means that the article is inside an unpublished category
			// In this case, the state is set to 0 to indicate Unpublished (even if the article state is Published)
			$query->select($this->getState('list.select', 'CASE WHEN badcats.id is not null THEN 0 ELSE a.state END AS state'));
		}

		$query->from('#__content AS a');

		// Join over the frontpage articles.
		//if ($this->context != 'com_content.featured') {
		//# Jinfinity
        if ($this->context != 'com_content.featured' && $this->context!='com_jicustomfields.featured') {
			$query->join('LEFT', '#__content_frontpage AS fp ON fp.content_id = a.id');
		}

		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Join on contact table
		$subQuery = $db->getQuery(true);
		$subQuery->select('contact.user_id, MAX(contact.id) AS id, contact.language');
		$subQuery->from('#__contact_details AS contact');
		$subQuery->where('contact.published = 1');
		$subQuery->group('contact.user_id, contact.language');
		$query->select('contact.id as contactid');
		$query->join('LEFT', '(' . $subQuery . ') AS contact ON contact.user_id = a.created_by');

		// Join over the categories to get parent category titles
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
		$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		// Join on voting table
		$query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count');
		$query->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');
		
		// #Jinfinity
        $app  = JFactory::getApplication();
        $jinput = $app->input;

        // check for field search/filter
        $fieldsearch = $this->getState('filter.fieldsearch', null);
        if($fieldsearch==null) {
            // state variable may not have been set
            $fieldsearch = $jinput->get('fs', null, 'raw');
            // persist fieldsearch into userstate
            if(count($fieldsearch)>0) $app->setUserState('com_jicustomfields.fieldsearch', $fieldsearch);
        }
        if(count($fieldsearch)>0) {
            // build field value conditions
            foreach($fieldsearch as $fid=>$value) {
                $fid = (int) $fid;
                // join field values for comparision
                $query->join('LEFT', '#__jifields_values AS fsv'.$fid.' ON (fsv'.$fid.'.fid='.$fid.' AND fsv'.$fid.'.cid=a.id)');

                // value may be an array from a form element
                if(is_array($value)) $value = implode($value, ',');
                if(strpos($value, ',')!==false) {
                    // filter by several field values
                    $query->where('fsv'.$fid.'.value IN ("'.str_replace(',', '","', $value).'")');
                } else {
                    // filter by a single field value
                    $query->where('fsv'.$fid.'.value='.$db->quote(urldecode($value)));
                }
                // field must exist
                $query->where('fsv'.$fid.'.fid IS NOT NULL');

                // TODO
                /*// value may be an array from a form element
                if(!is_array($value) && strpos($value, ',')!==false) $value = explode(',', $value);
                //if(is_array($value)) $value = implode($value, ',');
                if(is_array($value)) {
                    // filter by several field values
                    //$query->where('fsv'.$fid.'.value IN ("'.str_replace(',', '","', $value).'")');
                    $subquery = '';
                    foreach($value as $v) {
                        $subquery.= 'fsv'.$fid.'.value='.$db->quote($v).' OR '.$db->quote($v).' IN (fsv'.$fid.'.value)';
                    }
                    $query->where($subquery);
                } else {
                    // filter by a single field value
                    $query->where('fsv'.$fid.'.value='.$db->quote($value).' OR '.$db->quote($value).' IN (fsv'.$fid.'.value)');
                }
                // field must exist
                $query->where('fsv'.$fid.'.fid IS NOT NULL');*/
            }
        }

        // check for field searchword
        $searchword = $this->getState('filter.searchword', null);
        if($searchword==null) {
            // state variable may not have been set
            $searchword = $jinput->get('sw', null, 'raw');
            // persist fieldsearch into userstate
            $app->setUserState('com_jicustomfields.searchword', $searchword);
        }
        if($searchword!=null) {
            $searchwords = explode(',', $searchword);
            // split searchword into search terms
            foreach($searchwords as $word) {
                $word = trim($word);
                // search for word
                $wordsearch[] = 'LOWER(a.title) LIKE "%'.$word.'%"';
                $wordsearch[] = 'LOWER(a.introtext) LIKE "%'.$word.'%"';
                $wordsearch[] = 'LOWER(a.fulltext) LIKE "%'.$word.'%"';
                $query->where('('.implode(' OR ', $wordsearch).')');
            }
        }

		// Join to check for category published state in parent categories up the tree
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');

		if ($this->getState('filter.published') == 2) {
			// Find any up-path categories that are archived
			// If any up-path categories are archived, include all children in archived layout
			$subquery .= ' AND parent.published = 2 GROUP BY cat.id ';
			// Set effective state to archived if up-path category is archived
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 2 END';
		}
		else {
			// Find any up-path categories that are not published
			// If all categories are published, badcats.id will be null, and we just use the article state
			$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';
			// Select state to unpublished if up-path category is unpublished
			$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';
		}
		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$user	= JFactory::getUser();
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');
			$query->where('c.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			// Use article state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where($publishedWhere . ' = ' . (int) $published);
		}
		elseif (is_array($published)) {
			JArrayHelper::toInteger($published);
			$published = implode(',', $published);
			// Use article state if badcats.id is null, otherwise, force 0 for unpublished
			$query->where($publishedWhere . ' IN ('.$published.')');
		}

		// Filter by featured state
		$featured = $this->getState('filter.featured');
		switch ($featured)
		{
			case 'hide':
				$query->where('a.featured = 0');
				break;

			case 'only':
				$query->where('a.featured = 1');
				break;

			case 'show':
			default:
				// Normally we do not discriminate
				// between featured/unfeatured items.
				break;
		}

		// Filter by a single or group of articles.
		$articleId = $this->getState('filter.article_id');

		if (is_numeric($articleId)) {
			$type = $this->getState('filter.article_id.include', true) ? '= ' : '<> ';
			$query->where('a.id '.$type.(int) $articleId);
		}
		elseif (is_array($articleId)) {
			JArrayHelper::toInteger($articleId);
			$articleId = implode(',', $articleId);
			$type = $this->getState('filter.article_id.include', true) ? 'IN' : 'NOT IN';
			$query->where('a.id '.$type.' ('.$articleId.')');
		}

		// Filter by a single or group of categories
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId)) {
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid '.$type.(int) $categoryId;

			if ($includeSubcategories) {
				$levels = (int) $this->getState('filter.max_category_levels', '1');
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = '.(int) $categoryId);
				if ($levels >= 0) {
					$subQuery->where('sub.level <= this.level + '.$levels);
				}

				// Add the subquery to the main query
				$query->where('('.$categoryEquals.' OR a.catid IN ('.$subQuery->__toString().'))');
			}
			else {
				$query->where($categoryEquals);
			}
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			if (!empty($categoryId)) {
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid '.$type.' ('.$categoryId.')');
			}
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');
		$authorWhere = '';

		if (is_numeric($authorId)) {
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<> ';
			$authorWhere = 'a.created_by '.$type.(int) $authorId;
		}
		elseif (is_array($authorId)) {
			JArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);

			if ($authorId) {
				$type = $this->getState('filter.author_id.include', true) ? 'IN' : 'NOT IN';
				$authorWhere = 'a.created_by '.$type.' ('.$authorId.')';
			}
		}

		// Filter by author alias
		$authorAlias = $this->getState('filter.author_alias');
		$authorAliasWhere = '';

		if (is_string($authorAlias)) {
			$type = $this->getState('filter.author_alias.include', true) ? '= ' : '<> ';
			$authorAliasWhere = 'a.created_by_alias '.$type.$db->Quote($authorAlias);
		}
		elseif (is_array($authorAlias)) {
			$first = current($authorAlias);

			if (!empty($first)) {
				JArrayHelper::toString($authorAlias);

				foreach ($authorAlias as $key => $alias)
				{
					$authorAlias[$key] = $db->Quote($alias);
				}

				$authorAlias = implode(',', $authorAlias);

				if ($authorAlias) {
					$type = $this->getState('filter.author_alias.include', true) ? 'IN' : 'NOT IN';
					$authorAliasWhere = 'a.created_by_alias '.$type.' ('.$authorAlias .
						')';
				}
			}
		}

		if (!empty($authorWhere) && !empty($authorAliasWhere)) {
			$query->where('('.$authorWhere.' OR '.$authorAliasWhere.')');
		}
		elseif (empty($authorWhere) && empty($authorAliasWhere)) {
			// If both are empty we don't want to add to the query
		}
		else {
			// One of these is empty, the other is not so we just add both
			$query->where($authorWhere.$authorAliasWhere);
		}

		// Filter by start and end dates.
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->toSql());

		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');

		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState('filter.date_filtering', 'off');
		$dateField = $this->getState('filter.date_field', 'a.created');

		switch ($dateFiltering)
		{
			case 'range':
				$startDateRange = $db->Quote($this->getState('filter.start_date_range', $nullDate));
				$endDateRange = $db->Quote($this->getState('filter.end_date_range', $nullDate));
				$query->where('('.$dateField.' >= '.$startDateRange.' AND '.$dateField .
					' <= '.$endDateRange.')');
				break;

			case 'relative':
				$relativeDate = (int) $this->getState('filter.relative_date', 0);
				$query->where(
					$dateField.' >= DATE_SUB(' . $nowDate.', INTERVAL ' .
					$relativeDate.' DAY)'
				);
				break;

			case 'off':
			default:
				break;
		}

		// process the filter for list views with user-entered filters
		$params = $this->getState('params');

		if ((is_object($params)) && ($params->get('filter_field') != 'hide') && ($filter = $this->getState('list.filter'))) {
			// clean filter variable
			$filter = JString::strtolower($filter);
			$hitsFilter = (int) $filter;
			$filter = $db->Quote('%'.$db->escape($filter, true).'%', false);

			switch ($params->get('filter_field'))
			{
				case 'author':
					$query->where(
						'LOWER( CASE WHEN a.created_by_alias > '.$db->quote(' ').
						' THEN a.created_by_alias ELSE ua.name END ) LIKE '.$filter.' '
					);
					break;

				case 'hits':
					$query->where('a.hits >= '.$hitsFilter.' ');
					break;

				case 'title':
				default: // default to 'title' if parameter is not valid
					$query->where('LOWER( a.title ) LIKE '.$filter);
					break;
			}
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('a.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
			$query->where('(contact.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') OR contact.language IS NULL)');
		}

		// Add the list ordering clause.
        $orderby = trim($this->getState('filter_order', 'a.ordering'));
        if(substr($orderby, 0, 3)=='fv.') {
            $orderdir = $this->getState('list.direction', 'ASC');
            // order by field value
            $fid = (int) substr($orderby, 3, 1);
            $query->join('LEFT', '#__jifields_values AS fv'.$fid.' ON (fv'.$fid.'.cid=a.id AND fv'.$fid.'.fid='.$fid.')');

            // determine order by type
            $subQuery = $db->getQuery(true);
            $subQuery->select('type');
            $subQuery->from('#__jifields');
            $subQuery->where('id='.$fid);
            $db->setQuery($subQuery);
            $fieldtype = $db->loadResult();

            if($fieldtype=='currency' || $fieldtype=='area') {
                // numeric order by
                $query->order('fv'.$fid.'.value+0 '.$orderdir);
            } else {
                // string order by
                $query->order('fv'.$fid.'.value '.$orderdir);
            }
        } else {
            // , c.lft, a.ordering, a.created desc
		    $query->order($this->getState('list.ordering', 'a.ordering').' '.$this->getState('list.direction', 'ASC'));
        }
		$query->group('a.id, a.title, a.alias, a.introtext, a.checked_out, a.checked_out_time, a.catid, a.created, a.created_by, a.created_by_alias, a.created, a.modified, a.modified_by, uam.name, a.publish_up, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, a.hits, a.xreference, a.featured, a.fulltext, a.state, a.publish_down, badcats.id, c.title, c.path, c.access, c.alias, uam.id, ua.name, ua.email, contact.id, parent.title, parent.id, parent.path, parent.alias, v.rating_sum, v.rating_count, c.published, c.lft, a.ordering, parent.lft, fp.ordering, c.id, a.images, a.urls');
		return $query;
	}
}
