<?php
/**
 * @version     $Id: mod_jicustomfields.php 014 2014-10-27 13:32:00Z Anton Wintergerst $
 * @package     JiCustomFields Fields Module for Joomla
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
    echo '<div><h3>Uhoh! It looks like the JiCustomFields Component is not installed!</h3><br />Get the latest Jinfinity Component from the <a href="http://www.jinfinity.com/downloads?alias=jicustomfields">Jinfinity Website</a>.</div>';
    return;
}
require_once dirname(__FILE__).DS.'helper.php';
$helper = new modJiCustomFieldsHelper();
$fields = $helper->getFields($params);

require(JModuleHelper::getLayoutPath('mod_jicustomfields'));