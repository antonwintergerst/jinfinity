<?php 
/**
 * @version     $Id: jimigrator.php 040 2014-12-15 12:16:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Load JiStandards
require_once( JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jistandards.php' );

// Load Stylesheets
if(version_compare(JVERSION, '3.0', 'ge')) {
    JHTML::stylesheet('media/jiframework/css/admin.css');
	JHTML::stylesheet('media/jimigrator/css/jimigrator.css');
} else {
    JHTML::_('stylesheet', 'admin.css', 'media/jiframework/css/');
	JHTML::_('stylesheet', 'jimigrator.css', 'media/jimigrator/css/');
}

// Load 1.7+ SubMenu Helpers & variables
if(version_compare( JVERSION, '1.6', 'ge' )) {
    JLoader::register('JiMigratorHelper', dirname(__FILE__).'/helpers/jimigrator.php');
}
if(version_compare( JVERSION, '2.5', 'ge' )) {
    $jinput = JFactory::getApplication()->input;
    $controller = $jinput->get('controller');
    $view = $jinput->get('view');
    $api = $jinput->get('api');
} else {
    $controller = JRequest::getWord('controller');
    $view = JRequest::getWord('view');
    $api = JRequest::getWord('api');
}

// Build Controller
require_once( JPATH_SITE.'/administrator/components/com_jimigrator/controller.php' );
$controller = ($controller!=null)? $controller : $view;

if($controller) {
    if($api) {
        $path = JPATH_SITE.'/administrator/components/com_jimigrator/controllers/'.$api.'/'.$controller.'.php';
    } else {
        $path = JPATH_SITE.'/administrator/components/com_jimigrator/controllers/'.$controller.'.php';
    }
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}
$classname    = 'JiMigratorController'.$controller;
$controller   = new $classname( );
$controller->execute( JRequest::getWord( 'task' ) );
$controller->redirect();