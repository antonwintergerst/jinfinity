<?php
/**
 * @version     $Id: mod_jidownloadtoken.php 020 2013-06-17 15:50:00Z Anton Wintergerst $
 * @package     JiDownloadToken Module for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

// Load Data
require_once dirname(__FILE__).'/helper.php';
$helper = new JiDownloadTokenHelper($params);
$token = $helper->getToken();

require(JModuleHelper::getLayoutPath('mod_jidownloadtoken'));