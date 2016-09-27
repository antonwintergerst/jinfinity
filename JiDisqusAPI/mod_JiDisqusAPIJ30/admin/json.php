<?php
/*
 * @version     $Id: json.php 101 2013-06-04 20:18:00Z Anton Wintergerst $
 * @package     Jinfinity Disqus API Module
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// Initialize Joomla framework
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );

define('JPATH_BASE', str_replace(DS.'modules'.DS.'mod_jidisqusapi'.DS.'admin', '', dirname(__FILE__)));
require_once (JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once (JPATH_BASE.DS.'includes'.DS.'framework.php');
require_once (JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'factory.php');
$mainframe = JFactory::getApplication('site');
$mainframe->initialise();
JPluginHelper::importPlugin('system');

// no direct access
defined('_JEXEC') or die('Restricted access');

// Get the Application Object.
$app = JFactory::getApplication();      
// Set Page Header
header('Content-Type: application/json;charset=UTF-8');

$task = JRequest::getVar('modtask');
require_once(dirname(__FILE__).'/cache.php');
$cacheHelper = new JiDisqusAPICacheHelper();
if($task=='install') {
    $data = $cacheHelper->installCache();
} elseif($task=='clear') {
    $data = $cacheHelper->clearCache();
} elseif($task=='uninstall') {
    $data = $cacheHelper->uninstallCache();
}
// Echo Data
echo json_encode($data);
// Close the Application.
$app->close();
exit;