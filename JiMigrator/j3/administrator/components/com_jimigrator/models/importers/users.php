<?php 
/**
 * @version     $Id: users.php 086 2013-07-31 20:38:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class UsersImporter extends JiImporter {
    /**
     * Users Override
     * @param $item
     */
    public function willImportTableRow(&$item) {
        // 1.6+ Mappings
        if(json_decode($item->params)==null) $item->params = '';
    }
}