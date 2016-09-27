<?php
/**
 * @version     $Id: jiphpmyadmin.php 010 2013-07-01 22:30:00Z Anton Wintergerst $
 * @package     JiPhpMyAdmin for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiPhpMyAdminHelper', dirname(__FILE__).'/helpers/jiphpmyadmin.php');
}

// Build Controller
$controller = JControllerLegacy::getInstance('JiPhpMyAdmin');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();