<?php 
/**
 * @version     $Id: uninstall.jinfinity.php 010 2014-12-12 13:40:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
$lang = &JFactory::getLanguage();
$lang->load('com_jimigrator');