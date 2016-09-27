<?php
/**
 * @version     $Id: usergroupmap.php 093 2013-08-14 14:10:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class UserGroupMapExporter extends JiExporter {
    /**
     * Override - force all data into user_usergroup_map as per Joomla 3.0 db table
     * @return string
     */
    public function getOutputFilename() {
        $filename = 'user_usergroup_map.csv';
        return $filename;
    }

    /**
     * Override - columns taken from Joomla 3.0
     * @return array
     */
    public function getColumns() {
        $columns = array('user_id', 'group_id');
        return $columns;
    }

    /**
     * @param object $item
     */
    public function willExportTableRow(&$item) {
        $shiftedGroups = array(
            18=>2, //1.5 Registered => 3.0 Registered
            19=>3, //1.5 Author => 3.0 Author
            20=>4, //1.5 Editor => 3.0 Editor
            21=>5, //1.5 Publisher => 3.0 Publisher
            23=>6, //1.5 Manager => 3.0 Manager
            24=>7, //1.5 Administrator => 3.0 Administrator
            25=>8 //1.5 Super Administrator => 3.0 Super Users
        );
        $newitem = new stdClass();
        $newitem->user_id = $item->aro_id;
        $newitem->group_id = (isset($shiftedGroups[$item->group_id]))? $shiftedGroups[$item->group_id] : $item->group_id;
        $item = $newitem;
    }
}