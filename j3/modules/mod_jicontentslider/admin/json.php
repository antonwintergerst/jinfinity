<?php
/**
 * @version     $Id: json.php 119 2014-05-08 14:36:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// Initialize Joomla framework
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );

define('JPATH_BASE', str_replace(DS.'modules'.DS.'mod_jicontentslider'.DS.'admin', '', dirname(__FILE__)));
define('JPATH_SITE', JPATH_BASE);

require_once (JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once (JPATH_BASE.DS.'includes'.DS.'framework.php');
require_once (JPATH_BASE.DS.'libraries'.DS.'joomla'.DS.'factory.php');
$app = JFactory::getApplication('site');
$app->initialise();
JPluginHelper::importPlugin('system');

// no direct access
defined('_JEXEC') or die('Restricted access');

// Set Page Header
header('Content-Type: application/json;charset=UTF-8');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");

// Get Params
$jinput = $app->input;
$params = new JRegistry();
$params->set('links', $jinput->get('links'));
$params->set('captions', $jinput->get('captions'));
$params->set('discs', $jinput->get('discs'));
$params->set('uniqueclass', $jinput->get('uniqueclass'));
$params->set('sourcetype', $jinput->get('sourcetype'));
$params->set('source', $jinput->get('source', '', 'raw'));

$params->set('width',$jinput->get('width', '100%', 'raw'));
$params->set('height', $jinput->get('height', '300px', 'raw'));
$params->set('padding', $jinput->get('padding', '0', 'raw'));
$params->set('autosizing', $jinput->get('autosizing'));
$params->set('verticalAlign', $jinput->get('verticalAlign', 'top'));
$params->set('horizontalAlign', $jinput->get('horizontalAlign'));
$params->set('numberslides', $jinput->get('numberslides'));
$params->set('speed', $jinput->get('speed'));
$params->set('delay', $jinput->get('delay'));
$params->set('autoplay', $jinput->get('autoplay'));
$params->set('responsive', $jinput->get('responsive'));
$params->set('sli_thumbs_resize', $jinput->get('sli_thumbs_resize'));

// Get Data
require_once(JPATH_SITE.'/modules/mod_jicontentslider/helper.php');
$helper = new JiContentSliderHelper();
$data = $helper->getData($params);

// Echo Data
echo json_encode($data);
// Close the Application.
$app->close();
exit;