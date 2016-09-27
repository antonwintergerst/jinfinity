<?php
/**
 * @version     $Id: helper.php 015 2014-11-17 19:35:00Z Anton Wintergerst $
 * @package     JiCustomFields Fields Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modJiCustomFieldsHelper {
    public function getFields($params) {
        $included = $params->get('fields', array(0));
        if(!is_array($included)) $included = array($included);

        if($params->get('mode', 'dynamic')=='dynamic') {
            $app = JFactory::getApplication();
            $jinput = $app->input;
            $option = $jinput->get('option');
            $article_id = (($option=='com_content' || $option=='com_jicustomfields') && $jinput->get('view')==='article')? (int)$jinput->get('id') : 0;
        } else {
            $article_id = (int) $params->get('item_id');
        }

        if($article_id==0) return array();

        // find catid from article
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('catid');
        $query->from('#__content');
        $query->where('id='.$article_id);
        $db->setQuery($query);
        $catid = (int) $db->loadResult();

        // get field data
        $query = $db->getQuery(true);
        $query->select('f.*, fv.value');
        $query->from('#__jifields AS f');
        $query->join('left', '#__jifields_map AS map ON map.fid=f.id');
        $query->join('left', '#__jifields_values AS fv ON fv.fid = f.id');
        $query->where('f.state=1');
        if(!in_array(0, $included)) $query->where('fv.`fid` IN('.implode(',', $included).')');
        if($catid!=0) {
            $query->join('left', '#__jifields_map AS map2 ON (map2.`catid`=0 AND map2.`fid`=0) OR (map2.`catid`='.(int) $catid.' AND map2.`fid`=0) OR (map.`fid`=map2.`fid` AND map2.`catid`=0)');
            $query->where('CASE WHEN map2.`fid`=0 THEN 1 ELSE (map.`catid`='.(int) $catid.' OR map2.`catid`=0) END');
        }
        $query->where('fv.cid='.$article_id);
        $db->setQuery($query);
        $fields = $db->loadObjectList();

        // Initialise JiFields
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        $jifields = array();
        if($fields!=null) {
            foreach($fields as $field) {
                $JiField = $JiFieldHelper->loadType($field);
                if($JiField->get('group')!='system') {
                    if(isset($field->attribs)) $JiField->setParams($field->attribs);
                    $jifields[$field->id] = $JiField;
                }
            }
        }
        return $jifields;
    }
}