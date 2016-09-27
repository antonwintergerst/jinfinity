<?php
/**
 * @version     $Id: jifieldhelper.php 030 2014-12-03 09:19:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 System Plugin
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Returns JiField value for a given field alias and item id, alias, object or array
 * @param string $field_alias
 * @param int|string|array|object $item
 * @return string
 */
function get_jifieldvalue($field_alias, $item)
{
    // item may already have the field value attached
    if(is_array($item) || is_object($item)) {
        // object/array
        $item = (array) $item;
        if(isset($item[$field_alias]) && is_a($item[$field_alias], 'JiCustomField')) {
            $JiField = $item[$field_alias];
            return $JiField->get('value');
        }
    }
    // id or alias
    return get_jifield($field_alias, $item, true);
}

/**
 * Returns JiField or JiField value for a given field alias and item id or alias
 * @param string $field_alias
 * @param int|string $item
 * @param bool $plain_value
 * @return bool|JiCustomField|string
 */
function get_jifield($field_alias, $item, $plain_value=false)
{
    if(is_array($item) || is_object($item)) {
        // find the id or alias from the object/array
        $item = (array) $item;
        if(isset($item['id'])) {
            $item = $item['id'];
        } elseif(isset($item['alias'])) {
            $item = $item['alias'];
        } else {
            return false;
        }
    }
    if((int)$item>0) $item_id = (int) $item;

    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
    $JiFieldHelper = new JiCustomFieldHelper();

    // find catid from article
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('`id`, `catid`');
    $query->from('#__content');
    if(isset($item_id)) {
        $query->where('`id`='.$item_id);
    } else {
        $query->where('`alias`='.$db->quote($item));
    }
    $db->setQuery($query);
    $item = $db->loadObject();
    if(!$item) return false;

    // get field data
    $query = $db->getQuery(true);
    $query->select('f.*, fv.`value`');
    $query->from('#__jifields AS f');
    $query->join('left', '#__jifields_map AS map ON map.`fid` = f.`id`');
    $query->join('left', '#__jifields_values AS fv ON fv.`fid` = f.`id`');
    if((int)$item->catid!=0) $query->where('map.`catid` = '.(int)$item->catid);
    $query->where('f.`alias` = '.$db->quote($field_alias));
    $query->where('fv.`cid` = '.(int)$item->id);
    $db->setQuery($query, 0, 1);
    $field = $db->loadObject();

    if($field==null) return false;

    // construct JiField object from field data
    $JiField = $JiFieldHelper->loadType($field);
    $JiField->set('item', $item);
    if($JiField->get('group')!='system') {
        if(isset($field->value)) $JiField->setValue($field->value);
        if(isset($field->attribs)) $JiField->setParams($field->attribs);

        if($plain_value) {
            return $JiField->get('value');
        } else {
            return $JiField;
        }
    }

    return false;
}

function get_jifields($item, $plain_value=false)
{
    if((int)$item>0) $item_id = (int) $item;

    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
    $JiFieldHelper = new JiCustomFieldHelper();

    // find catid from article
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('`id`, `catid`');
    $query->from('#__content');
    if(isset($item_id)) {
        $query->where('`id`='.$item_id);
    } else {
        $query->where('`alias`='.$db->quote($item));
    }
    $db->setQuery($query);
    $item = $db->loadObject();
    if(!$item) return array();

    // get field data
    $query = $db->getQuery(true);
    $query->select('DISTINCT f.*, fv.`value`');
    $query->from('#__jifields AS f');
    $query->join('left', '#__jifields_map AS map ON map.`fid` = f.`id`');
    $query->join('left', '#__jifields_values AS fv ON fv.`fid` = f.`id`');
    if((int)$item->catid!=0) $query->where('map.`catid` = '.(int)$item->catid);
    $query->where('fv.`cid` = '.(int)$item->id);
    $db->setQuery($query, 0, 1);
    $results = $db->loadObjectList();

    if($results==null) return array();

    // construct JiField object from field data
    $fields = array();
    foreach($results as $result) {
        $JiField = $JiFieldHelper->loadType($result);
        $JiField->set('item', $item);
        if($JiField->get('group')!='system') {
            if(isset($result->value)) $JiField->setValue($result->value);
            if(isset($result->attribs)) $JiField->setParams($result->attribs);

            if($plain_value) {
                $fields[$result->alias] = $JiField->get('value');
            } else {
                $fields[$result->alias] = $JiField;
            }
        }
    }

    return $fields;
}

function get_jifieldid($field_alias)
{
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('f.id');
    $query->from('#__jifields');
    $query->where('f.`alias` = '.$db->quote($field_alias));
    $query->setQuery($query);
    return (int) $db->loadResult();
}

function set_jifields($item, $context='com_content.article')
{
    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
    if(version_compare(JVERSION, '3', 'ge')) {
        $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
    } else {
        $model = JModel::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
    }
    $model->store($item, $context);
}

function render_jifieldinputs($id=null, $catid=null)
{
    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
    if(version_compare(JVERSION, '3', 'ge')) {
        $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
    } else {
        $model = JModel::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
    }
    $model->setState('filter.published', 1);

    if(isset($id) && is_object($id)) {
        $item = $id;
        if(!isset($item->id)) $item->id = 0;
        if(isset($catid)) $item->catid = $catid;
    } else {
        $item = new stdClass();
        $item->id = (int)$id;
        $item->catid = (int)$catid;
    }

    // load field values
    $values = ((int)$item->id>0)? $model->getValues($item, 'item') : null;

    // load fieldlist
    $jifields = $model->getJiFields($item, $values);

    ob_start();
    $model->renderInputLayout($jifields, $item);
    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'fields'.DS.'tmpl'.DS.'form.php');
    $html = ob_get_clean();

    return $html;
}