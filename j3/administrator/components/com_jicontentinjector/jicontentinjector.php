<?php 
/**
 * @version     $Id: jicontentinjector.php 011 2014-12-19 10:10:00Z Anton Wintergerst $
 * @package     JiContentInjector for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load Stylesheets
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('media/jiframework/css/admin.css');
	JHTML::stylesheet('media/jicontentinjector/css/admin.css');
} else {
    JHTML::_('stylesheet', 'admin.css', 'media/jiframework/css/');
	JHTML::_('stylesheet', 'admin.css', 'media/jicontentinjector/css/');
}

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiContentInjectorHelper', dirname(__FILE__).'/helpers/jicontentinjector.php');
}

// Build Controller
$controller = JControllerLegacy::getInstance('JiContentInjector');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();