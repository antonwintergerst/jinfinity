<?php 
/**
 * @version     $Id: jicustomfields.php 055 2014-06-18 10:38:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$lang = JFactory::getLanguage();
$lang->load('com_content');

require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/dates.php');
require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/icon.php');
require_once(JPATH_SITE.'/components/com_jicustomfields/controller.php' );

// Require the com_content helper library
require_once(JPATH_SITE.'/components/com_content/helpers/query.php');
require_once(JPATH_SITE.'/components/com_content/helpers/route.php');

// Build Controller
if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
$controller = JControllerLegacy::getInstance('JiCustomFields');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();