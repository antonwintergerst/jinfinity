<?php 
/**
 * @version     $Id: logs.php 022 2013-05-09 11:33:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerLogs extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
    }
	function showlog() {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: text/plain;charset=UTF-8');     
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
        	$model = JModelLegacy::getInstance('Logs', 'JiMigratorModel');
		} else {
			$model = JModel::getInstance('Logs', 'JiMigratorModel');
		}
        echo $model->getLog();
        // Close the Application.
        $app->close(); 
    }
}