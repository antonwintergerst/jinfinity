<?php
/**
 * @version     $Id: mod_jigrid.php 010 2013-07-10 18:16:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$moduletype = $params->get('moduletype', 'togglemenu');

JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');

$class_sfx = htmlspecialchars($params->get('class_sfx'));

// Load Data
require_once dirname(__FILE__).'/helper.php';
$helper = new JiGridModHelper();
$data = $helper->getData($params);

require(JModuleHelper::getLayoutPath('mod_jigrid', $moduletype));