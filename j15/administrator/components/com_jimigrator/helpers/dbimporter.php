<?php
/**
 * @version     $Id: dbimporter.php 068 2013-10-25 12:33:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class DBImporter extends JiImporter {

    public $sourcedir;

    public $dbtable;

    function process() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->params = new JiMigratorObject(array(
            'truncate'=>1
        ));
        parent::process();
    }
}