<?php
/**
 * @version     $Id: mod_jigrid_context.php 001 2014-11-27 20:29:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$helperpath = JPATH_SITE.DS.'media'.DS.'jigrid'.DS.'helper.php';
if(!file_exists($helperpath)) {
    echo JText::_('TPL_JIGRID_WARNING_GRIDMISSING');
    exit;
}
$jinput = JFactory::getApplication()->input;

echo $jinput->get('screencontext').' '.$jinput->get('browsercontext', null, 'raw');