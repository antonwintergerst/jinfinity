<?php
/**
 * @version     $Id: jiforms.php 010 2013-08-26 14:21:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/components/com_jiforms/helpers/route.php');

// Build Controller
if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
$controller = JControllerLegacy::getInstance('JiForms');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();