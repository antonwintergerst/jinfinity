<?php
/**
 * @version     $Id: default.php 010 2013-07-01 22:30:00Z Anton Wintergerst $
 * @package     JiPhpMyAdmin for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$app = JFactory::getApplication();
$app->redirect(JURI::root().'administrator/components/com_jiphpmyadmin/phpmyadmin/index.php');
?>