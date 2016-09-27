<?php
/**
 * @version     $Id: jiforms.php 010 2013-08-26 14:21:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load Stylesheets
if(version_compare(JVERSION, '3.0.0', 'ge')) {
	JHTML::stylesheet('administrator/components/com_jiforms/assets/css/jiforms.css');
} else {
	JHTML::_('stylesheet', 'jiforms.css', 'administrator/components/com_jiforms/assets/css/');
}

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiFormsHelper', dirname(__FILE__).'/helpers/jiforms.php');
}

// Build Controller
$controller = JControllerLegacy::getInstance('JiForms');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();