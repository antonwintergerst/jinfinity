<?php
/*
 * @version     $Id: cache.php 102 2013-06-04 20:37:00Z Anton Wintergerst $
 * @package     Jinfinity Disqus API Module
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       antonwintergerst@gmail.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiDisqusAPICacheHelper
{
    public function get($request) {
        $module = JModuleHelper::getModule('mod_jidisqusapi');
        $params = new JRegistry();
        $params->loadString($module->params);

        $db = JFactory::getDBO();
        $tablelist = $db->getTableList();
        $cachetable = $db->getPrefix().'jidisqusapi_cache';
        if(!in_array($cachetable, $tablelist)) return false;

        $query = 'SELECT * FROM `#__jidisqusapi_cache` WHERE `request`='.$db->quote($request);
        $db->setQuery($query);
        $result = $db->loadObject();
        if($result==null) return false;
        if(date(strtotime('now'))-strtotime($result->updated)>$params->get('cachetime', 3600)) return false;
        return $result->data;
    }
    public function set($request, $data) {
        $db = JFactory::getDBO();
        $tablelist = $db->getTableList();
        $cachetable = $db->getPrefix().'jidisqusapi_cache';
        if(in_array($cachetable, $tablelist)) {
            // Find existing
            $query = 'SELECT `id` FROM `#__jidisqusapi_cache` WHERE `request`='.$db->quote($request);
            $db->setQuery($query);
            $id = $db->loadResult();
            if($id==null) {
                // Save
                $query = 'INSERT INTO `#__jidisqusapi_cache` (`request`, `data`, `updated`)';
                $query.= ' VALUES ('.$db->quote($request).', '.$db->quote($data).','.$db->quote(date('Y-m-d H:i:s')).')';
            } else {
                // Update
                $query = 'UPDATE `#__jidisqusapi_cache` SET `request`='.$db->quote($request).', `data`='.$db->quote($data).', `updated`='.$db->quote(date('Y-m-d H:i:s'));
                $query.= ' WHERE `id`='.(int)$id;
            }
            $db->setQuery($query);
            $db->query();
        }
    }
    public function installCache() {
        $return = new stdClass();
        $db = JFactory::getDBO();
        $query = 'CREATE TABLE IF NOT EXISTS `#__jidisqusapi_cache` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `request` text NOT NULL,
          `data` text NOT NULL,
          `updated` datetime NOT NULL,
          PRIMARY KEY (`id`)
        );';
        $db->setQuery($query);
        $db->query();
        $return->valid = true;
        $return->msg = 'Cache Installed!';
        return $return;
    }
    public function clearCache() {
        $return = new stdClass();
        $db = JFactory::getDBO();
        $query = 'TRUNCATE TABLE `#__jidisqusapi_cache';
        $db->setQuery($query);
        $db->query();
        $return->valid = true;
        $return->msg = 'Cache Cleared!';
        return $return;
    }
    public function uninstallCache() {
        $return = new stdClass();
        $db = JFactory::getDBO();
        $query = 'DROP TABLE `#__jidisqusapi_cache';
        $db->setQuery($query);
        $db->query();
        $return->valid = true;
        $return->msg = 'Cache Uninstalled!';
        return $return;
    }
}