<?php
/**
 * @version     $Id: helper.php 087 2014-10-31 10:53:00Z Anton Wintergerst $
 * @package     JiCustomFields Articles Module for Joomla 3.0+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modJiCustomFieldsArticlesHelper {
    public function getArticles($params, $state=1)
    {
        $user       = JFactory::getUser();
        $gid        = $user->get('aid', 0);
        $noauth     = !$params->get('show_noauth');
        $filter_category = $params->get('filter_category', array(0));
        $list_direction = $params->get('orderby_pri');
        $list_ordering = $params->get('orderby_sec');


        $orderby_field = (int) $params->get('orderby_field');
        $num_articles = $params->get('num_articles', 5);

        // join module params with com_content params
        $contentparams = JComponentHelper::getParams('com_content');
        $contentparams->merge($params);

        require_once(JPATH_SITE.'/components/com_jicustomfields/models/articles.php');
        $model = JModelLegacy::getInstance('Articles', 'JiCustomFieldsModel', array('ignore_request' => true));
        $model->setState('params', $contentparams);

        // category filter
        if(is_array($filter_category) && !in_array(0, $filter_category)) $model->setState('filter.category_id', $filter_category);

        // searchword
        if($params->get('filter_searchword', 0)) {
            $searchword = $params->get('searchword');
            if($searchword!=null) $model->setState('filter.searchword', $searchword);
        }

        // fieldsearch
        if($params->get('filter_fields', 0)) {
            $fieldsearch = array();
            for($i=0; $i<5; $i++) {
                $field = $params->get('field'.$i);
                $search = $params->get('field'.$i.'search');
                if($field!=null && $search!=null) $fieldsearch[$field] = $search;
            }
            if(count($fieldsearch)>0) $model->setState('filter.fieldsearch', $fieldsearch);
        }

        if($num_articles!=null && $num_articles!=0) $model->setState('list.limit', $num_articles);
        $model->setState('filter.category_id', $filter_category);
        $model->setState('list.direction', $list_direction);
        $model->setState('list.ordering', $list_ordering);

        // Merge Module Params with Global com_content params
        $app = JFactory::getApplication();
        $aparams = $app->getParams('com_content');
        $params->merge($aparams);

        $items = $model->getItems();

        // attach events
        foreach ($items as $item)
        {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

            $item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

            // No link for ROOT category
            if ($item->parent_alias == 'root')
            {
                $item->parent_slug = null;
            }

            $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
            $item->event   = new stdClass;

            $dispatcher = JEventDispatcher::getInstance();

            // Old plugins: Ensure that text property is available
            if (!isset($item->text))
            {
                $item->text = $item->introtext;
            }

            JPluginHelper::importPlugin('content');
            $dispatcher->trigger('onContentPrepare', array ('com_content.category', &$item, &$item->params, 0));

            // Old plugins: Use processed text as introtext
            $item->introtext = $item->text;

            $results = $dispatcher->trigger('onContentAfterTitle', array('com_content.category', &$item, &$item->params, 0));
            $item->event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.category', &$item, &$item->params, 0));
            $item->event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.category', &$item, &$item->params, 0));
            $item->event->afterDisplayContent = trim(implode("\n", $results));

            //#Jinfinity - Declare new onContentPrepare event as Joomla does not pass enough info using the standard event
            JPluginHelper::importPlugin('content');
            $results = $dispatcher->trigger('onJiContentPrepare', array('com_jicustomfields.category', &$item, &$this->params, 0));
        }

        return $items;
    }
    public function getFields()
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel');
        } else {
            $model = JModel::getInstance('Fields', 'JiCustomFieldsModel');
        }
        $fields = $model->getFields();
        return $fields;
    }
}