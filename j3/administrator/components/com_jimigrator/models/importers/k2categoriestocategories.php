<?php
/**
 * @version     $Id: categories.php 106 2014-01-25 17:51:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');
require_once(dirname(__FILE__).DS.'categories.php');

class K2CategoriesToCategoriesImporter extends CategoriesImporter {
    /* >>> PRO >>> */
    /**
     * Map k2 categories to categories table
     * @return string
     */
    public function getInputTable() {
        $dbtable = 'categories';
        return $dbtable;
    }
    /**
     * Map k2 categories to joomla categories
     * @param $item
     */
    public function willImportTableRow(&$item) {
        if(isset($item->name)) $item->title = $item->name;
        if(isset($item->parent)) $item->parent_id = $item->parent;

        parent::willImportTableRow($item);
    }
    /* <<< PRO <<< */
}