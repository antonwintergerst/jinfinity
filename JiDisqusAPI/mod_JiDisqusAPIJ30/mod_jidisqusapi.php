<?php
/*
 * @version     $Id: mod_jidisuqsapi.php 100 2013-05-24 16:00:00Z Anton Wintergerst $
 * @package     Jinfinity Disqus API Module
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       antonwintergerst@gmail.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

// Load Data
require_once dirname(__FILE__).'/helper.php';
$helper = new JiDisqusAPIHelper($params);
$data = $helper->getData($params);

if(version_compare(JVERSION, '3.0.0', 'ge')) {
	// Load Stylesheets
	JHTML::stylesheet('modules/mod_jidisuqsapi/assets/css/jidisqusapi.css');
} else {
	// Load Stylesheets
	JHTML::_('stylesheet', 'jidisqusapi.css', 'modules/mod_jidisuqsapi/assets/css/');
}

require(JModuleHelper::getLayoutPath('mod_jidisqusapi'));