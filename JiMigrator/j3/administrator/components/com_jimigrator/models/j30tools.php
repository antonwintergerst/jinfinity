<?php 
/**
 * @version     $Id: j30tools.php 063 2013-08-14 16:02:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 3.0+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
jimport( 'joomla.application.component.view' );
class JiMigratorModelJ30Tools extends JiModel
{
    function __construct() {
        parent::__construct();
        // Set Processor Vars
        $this->start = 0;
        $this->limit = 100;
        $this->processed = 0;
        $this->total = null;
    }
    protected function __distruct()
    {
        $this->setup = null;
        $this->rootdir = null;
        $this->sectioncount = null;
        $this->catcount = null;
        $this->categories = null;
        $this->createnew = null;
        $this->start = null;
        $this->limit = null;
        $this->processed = null;
        $this->total = null;
    }
    function clearAll() {
        // Clear categories
        $db = JFactory::getDBO();
        $query = 'TRUNCATE TABLE #__categories';
        $db->setQuery($query);
        $db->query();
        // Add default categories
        $query = 'INSERT INTO `#__categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`, `version`) VALUES
(1, 0, 0, 0, 13, 0, \'\', \'system\', \'ROOT\', \'root\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{}\', \'\', \'\', \'\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(2, 27, 1, 1, 2, 1, \'uncategorised\', \'com_content\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(3, 28, 1, 3, 4, 1, \'uncategorised\', \'com_banners\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":"","foobar":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(4, 29, 1, 5, 6, 1, \'uncategorised\', \'com_contact\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(5, 30, 1, 7, 8, 1, \'uncategorised\', \'com_newsfeeds\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(6, 31, 1, 9, 10, 1, \'uncategorised\', \'com_weblinks\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1),
(7, 32, 1, 11, 12, 1, \'uncategorised\', \'com_users\', \'Uncategorised\', \'uncategorised\', \'\', \'\', 1, 0, \'0000-00-00 00:00:00\', 1, \'{"target":"","image":""}\', \'\', \'\', \'{"page_title":"","author":"","robots":""}\', 42, \'2011-01-01 00:00:01\', 0, \'0000-00-00 00:00:00\', 0, \'*\', 1);';
        $db->setQuery($query);
        $db->query();
        // Clear articles
        $query = 'TRUNCATE TABLE #__content';
        $db->setQuery($query);
        $db->query();
        // Clear Menu Types
        $query = 'TRUNCATE TABLE #__menu_types';
        $db->setQuery($query);
        $db->query();
        // Remove Site Menu Items
        $query = 'DELETE FROM #__menu WHERE client_id=0 AND alias!="root"';
        $db->setQuery($query);
        $db->query();
        // Remove Site Modules
        $query = 'DELETE FROM #__modules WHERE client_id=0';
        $db->setQuery($query);
        $db->query();
    }
}