<?php
/**
 * @version     $Id: mod_jiforms.php 011 2014-02-18 22:53:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).'/helper.php';
$modHelper = new modJiFormsHelper();

$form = $modHelper->getForm($params);

require(JModuleHelper::getLayoutPath('mod_jiforms', 'form'));