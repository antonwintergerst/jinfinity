<?php
/**
 * @version     $Id: index.php 057 2014-11-20 19:15:00Z Anton Wintergerst $
 * @package     JiMediaBrowser for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// Initialize Joomla framework
if(!defined('_JEXEC')) {
    define( '_JEXEC', 1 );
    if(!defined('DS')) define( 'DS', DIRECTORY_SEPARATOR );

    if (!defined('_JDEFINES'))
    {
        define('JPATH_BASE', str_replace(DS.'media'.DS.'jimediabrowser', '', dirname(__FILE__)));
        require_once (JPATH_BASE.DS.'includes'.DS.'defines.php');
    }

    require_once JPATH_BASE . '/includes/framework.php';
}

$app = JFactory::getApplication('site');
$app->initialise();
JPluginHelper::importPlugin('system');

// no direct access
defined('_JEXEC') or die('Restricted access');

JPluginHelper::importPlugin('content');
/*$dispatcher = JEventDispatcher::getInstance();
$results = $dispatcher->trigger('onJiMediaBrowserRequest', array(null));*/
$app->triggerEvent('onJiMediaBrowserRequest', array(null));

// If JiMediaBrowser doesn't catch the event just die
header('HTTP/1.0 403 Forbidden');
header('Location: '.str_replace('/media/jimediabrowser', '', JURI::root().'index.php'));
die('Restricted access');