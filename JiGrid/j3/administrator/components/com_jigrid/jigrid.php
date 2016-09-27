<?php
/**
 * @version     $Id: jigrid.php 021 2014-12-19 10:10:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 1.5+
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
    JHTML::stylesheet('media/jigrid/css/admin.css');
} else {
    JHTML::_('stylesheet', 'admin.css', 'media/jiframework/css/');
    JHTML::_('stylesheet', 'admin.css', 'media/jigrid/css/');
}

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiGridHelper', dirname(__FILE__).'/helpers/jigrid.php');
}

// Build Controller
if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
$controller = JControllerLegacy::getInstance('JiGrid');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();