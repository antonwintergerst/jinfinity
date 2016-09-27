<?php
/**
 * @version     $Id: helper.php 093 2014-11-13 10:12:00Z Anton Wintergerst $
 * @package     JiCustomFields Search Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modJiCustomFieldsSearchHelper {
    public function getSearch($params, $fieldlist)
    {
        $fields = $params->get('indices', 0);
        // force variable to be an array
        if(!is_array($fields)) $fields = array($fields);

        if($fields!=null && count($fields)>0) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('DISTINCT f.`id`, f.`title`, f.`type`, f.`attribs`, v.`value`');
            $query->from('#__jifields_values AS v');
            $query->join('left', '#__jifields AS f ON f.id = v.fid');
            $query->where('f.`state`=1');
            // only include selected fields
            if(!in_array(0, $fields)) $query->where('f.`id` IN ('.implode(',', $fields).')');
            // only include fields that can be filtered
            $query->where('f.type IN ("area", "currency", "date", "multiselect", "select", "tags", "radio")');

            // make sure content is available
            $query->join('left', '#__content AS c ON c.id = v.cid');
            $query->where('c.state = 1');
            $nullDate	= $db->Quote($db->getNullDate());
            $nowDate	= $db->Quote(JFactory::getDate()->toSql());
            $query->where('(c.publish_up = '.$nullDate.' OR c.publish_up <= '.$nowDate.')');
            $query->where('(c.publish_down = '.$nullDate.' OR c.publish_down >= '.$nowDate.')');

            $query->order('f.`title` ASC, v.`value` ASC');
            //$query->group('v.`value`');
            $db->setQuery($query);
            $results = $db->loadObjectList();


            // return null if no field values found
            if($results==null) return null;

            // group filters by fid
            $parents = array();
            $options = array();
            foreach($results as $result) {
                if(!empty($result->value)) {
                    if(!isset($options[$result->id])) {
                        $options[$result->id] = array($result->value);
                    } else {
                        $options[$result->id][] = $result->value;
                    }
                    $parents[$result->id] = $result;
                }
            }
            $filters = array();
            foreach($parents as $key=>$result) {
                $JiField = $fieldlist[$key];
                $JiField->prepareOutput();
                $JiField->set('options', $options[$key]);

                $filters[$key] = $JiField;
            }
            //echo '<pre>'; print_r($filters); echo '</pre>';
            return $filters;
        }
    }
    public function getFields($params)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        $jifields = array();

        // Load Database Object
        $db = JFactory::getDBO();
        // Get Fieldlist
        $query = $db->getQuery(true);
        $query->select('f.*');
        $query->from('#__jifields AS f');
        $query->join('left', '#__jifields_map AS map ON (map.`fid`=f.`id`)');
        $query->where('f.state = 1');

        // only load fields that are going to be rendered
        $fields = $params->get('indices', 0);
        if(is_array($fields) && !in_array(0, $fields)) $query->where('f.`id` IN ('.implode(',', $fields).')');

        $query->order('f.`ordering` ASC');
        $db->setQuery($query, 0, 100);
        $response = $db->loadObjectList('id');

        if($response!=null) {
            foreach($response as $field) {
                $JiField = $JiFieldHelper->loadType($field);
                if($JiField->get('group')!='system') {
                    if(isset($values[$field->id])) {
                        $JiField->setValue($values[$field->id]);
                    }
                    if(isset($field->attribs)) $JiField->setParams($field->attribs);
                    $jifields[$field->id] = $JiField;
                }
            }
        }
        return $jifields;
    }
}