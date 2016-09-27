<?php
/**
 * @version     $Id: jiextensionserver.php 015 2013-06-20 09:59:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load 1.7+ SubMenu Helpers
if(version_compare(JVERSION, '1.6.0', 'ge')) {
    JLoader::register('JiExtensionServerHelper', dirname(__FILE__).'/helpers/jiextensionserver.php');
}

// Build Controller
if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
$controller = JControllerLegacy::getInstance('JiExtensionServer');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();