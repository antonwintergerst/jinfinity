<?php
/**
 * @version     $Id: mod_jicustomfeilds_search.php 066 2014-10-31 17:49:00Z Anton Wintergerst $
 * @package     JiCustomFields Search Module for Joomla 3.0+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// #Jinfinity - Check if com_jicustomfields exists
$modelPath = JPATH_SITE.DS.'components'.DS.'com_jicustomfields'.DS.'models';
$jiexists = JFolder::exists($modelPath);
if(!$jiexists) {
    echo '<div><h3>Uhoh! It looks like the JiCustomFields Component is not installed!</h3><br />Get the latest Jinfinity Component from the <a href="http://www.jinfinity.com">Jinfinity Website</a>.</div>';
    return;
}
require_once(JPATH_SITE.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
require_once dirname(__FILE__).DS.'helper.php';
require_once(JPATH_SITE.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'route.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

$helper = new modJiCustomFieldsSearchHelper();
$fieldlist = $helper->getFields($params);
$filters = $helper->getSearch($params, $fieldlist);
$fieldsearch = $app->getUserState('com_jicustomfields.fieldsearch', array());
$searchword = $app->getUserState('com_jicustomfields.searchword');

require(JModuleHelper::getLayoutPath('mod_jicustomfields_search'));