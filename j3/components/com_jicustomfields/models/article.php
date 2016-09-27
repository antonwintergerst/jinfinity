<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: article.php 015 2013-03-19 11:13:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Article Model
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
// #Jinfinity - Run this model instead of the standard ContentModelArticle
require_once(JPATH_SITE.'/components/com_content/models/article.php');
class JiCustomFieldsModelArticle extends ContentModelArticle
{
    /**
     * Model context string.
     *
     * @var     string
     */
    protected $_context = 'com_content.article';
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('article.id', $pk);

        $offset = $app->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        //$params = $app->getParams();
        // #Jinfinity
        $params = $app->getParams('com_content');
        $this->setState('params', $params);

        // TODO: Tune these values based on other permissions.
        $user       = JFactory::getUser();
        if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
    
    /**
     * Method to get article data.
     *
     * @param   integer The id of the article.
     *
     * @return  mixed   Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null)
    {
        // #Jinfinity - Force model to populate state
        $this->populateState();
        
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {

            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select(
                    $this->getState(
                        'item.select', 'a.id, a.asset_id, a.title, a.alias, a.introtext, a.fulltext, ' .
                        // If badcats is not null, this means that the article is inside an unpublished category
                        // In this case, the state is set to 0 to indicate Unpublished (even if the article state is Published)
                        'CASE WHEN badcats.id is null THEN a.state ELSE 0 END AS state, ' .
                        'a.catid, a.created, a.created_by, a.created_by_alias, ' .
                        // use created if modified is 0
                        'CASE WHEN a.modified = ' . $db->q($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
                        'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, ' .
                        'a.images, a.urls, a.attribs, a.version, a.ordering, ' .
                        'a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language, a.xreference'
                    )
                );
                $query->from('#__content AS a');

                // Join on category table.
                $query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
                $query->join('LEFT', '#__categories AS c on c.id = a.catid');

                // Join on user table.
                $query->select('u.name AS author');
                $query->join('LEFT', '#__users AS u on u.id = a.created_by');

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
                
                // #Jinfinity - Join Fields
                $query->select('fv.value AS fields');
                $query->join('LEFT', '#__jifields_values AS fv ON a.id = fv.cid');

                $query->where('a.id = ' . (int) $pk);

                // Filter by start and end dates.
                $nullDate = $db->Quote($db->getNullDate());
                $date = JFactory::getDate();

                $nowDate = $db->Quote($date->toSql());

                $query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
                $query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

                // Join to check for category published state in parent categories up the tree
                // If all categories are published, badcats.id will be null, and we just use the article state
                $subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
                $subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
                $subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');
                $subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
                $query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
                }

                $db->setQuery($query);

                $data = $db->loadObject();

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->attribs);

                $data->params = clone $this->getState('params');
                $data->params->merge($registry);

                $registry = new JRegistry;
                $registry->loadString($data->metadata);
                $data->metadata = $registry;

                // Compute selected asset permissions.
                $user   = JFactory::getUser();

                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!$user->get('guest')) {
                    $userId = $user->get('id');
                    $asset  = 'com_content.article.'.$data->id;

                    // Check general edit permission first.
                    if ($user->authorise('core.edit', $asset)) {
                        $data->params->set('access-edit', true);
                    }
                    // Now check if edit.own is available.
                    elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                        // Check for a valid user and that they are the owner.
                        if ($userId == $data->created_by) {
                            $data->params->set('access-edit', true);
                        }
                    }
                }

                // Compute view access permissions.
                if ($access = $this->getState('filter.access')) {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                }
                else {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user = JFactory::getUser();
                    $groups = $user->getAuthorisedViewLevels();

                    if ($data->catid == 0 || $data->category_access === null) {
                        $data->params->set('access-view', in_array($data->access, $groups));
                    }
                    else {
                        $data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
                    }
                }

                $this->_item[$pk] = $data;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                }
                else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }
}
