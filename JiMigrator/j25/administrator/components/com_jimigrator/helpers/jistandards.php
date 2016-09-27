<?php 
/*
 * @version     $Id: export.php 015 2013-04-10 10:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport( 'joomla.application.component.view');
// Standardize Joomla classes
if(version_compare(JVERSION, '3.0.0', 'ge')) {
	if(!class_exists('JiModel')) {
		class JiModel extends JModelLegacy {}
	}
	if(!class_exists('JiView')) {
		class JiView extends JViewLegacy {}
	}
} else {
	if(!class_exists('JiModel')) {
		class JiModel extends JModel {}
	}
	if(!class_exists('JiView')) {
		class JiView extends JView {}
	}
}