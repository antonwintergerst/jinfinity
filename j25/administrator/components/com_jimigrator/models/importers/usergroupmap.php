<?php 
/**
 * @version     $Id: usergroupmap.php 086 2013-07-31 20:39:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 2.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class UserGroupMapImporter extends JiImporter {
    /**
     * User Group Map Override
     * @param $item
     * @return null|string
     */
    public function doesTableRowExist($item) {
        $db = JFactory::getDBO();

        // Check if exists
        $query = 'SELECT `user_id` FROM `#__'.$this->dbtable.'` WHERE `user_id`='.$db->quote($item->user_id).' AND `group_id`='.$db->quote($item->group_id);
        $db->setQuery($query);
        $exists = $db->loadResult();
        // DEBUG LV0 Check there are no errors
        if($db->getErrorMsg()) $this->setStatus(array('msg'=>'ERROR: '.$db->getErrorMsg()));

        return $exists;
    }
}