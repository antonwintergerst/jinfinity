<?php
/**
 * @version     $Id: k2itemstocontent.php 093 2014-01-25 17:51:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');
require_once(dirname(__FILE__).DS.'content.php');

class K2ItemsToContentImporter extends ContentImporter {
    /* >>> PRO >>> */
    /**
     * Map k2 items to content table
     * @return string
     */
    public function getInputTable() {
        $dbtable = 'content';
        return $dbtable;
    }
    /**
     * Map k2 items to joomla content
     * @param $item
     */
    public function willImportTableRow(&$item) {
        if(isset($item->published)) $item->state = $item->published;

        parent::willImportTableRow($item);
    }
    /* <<< PRO <<< */
}