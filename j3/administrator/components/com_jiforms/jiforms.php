<?php
/**
 * @version     $Id: jiforms.php 012 2014-12-19 10:10:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 1.7+
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
	JHTML::stylesheet('media/jiforms/css/admin.css');
} else {
    JHTML::_('stylesheet', 'admin.css', 'media/jiframework/css/');
	JHTML::_('stylesheet', 'admin.css', 'media/jiforms/css/');
}

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiFormsHelper', dirname(__FILE__).'/helpers/jiforms.php');
}

// Build Controller
$controller = JControllerLegacy::getInstance('JiForms');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();