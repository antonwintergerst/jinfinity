<?php 
/**
 * @version     $Id: jicustomfields.php 058 2014-12-19 10:10:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Load Stylesheets
if(version_compare(JVERSION, '3.0', 'ge')) {
    JHTML::stylesheet('media/jiframework/css/admin.css');
    JHTML::stylesheet('media/jicustomfields/css/admin.css');
} else {
    JHTML::_('stylesheet', 'admin.css', 'media/jiframework/css/');
    JHTML::_('stylesheet', 'admin.css', 'media/jicustomfields/css/');
}

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiCustomFieldsHelper', dirname(__FILE__).DS.'helpers'.DS.'jicustomfields.php');
}

// Build Controller
$controller = JControllerLegacy::getInstance('JiCustomFields');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();