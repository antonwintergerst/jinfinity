<?php
/**
 * @version     $Id: jigrid.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiGridHelper
{
    public static $extension = 'com_jigrid';

    public static function addSubmenu($vName)
    {
        // Joomla 3.0 SideBar
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::addEntry(
                JText::_('COM_JIGRID_GRIDITEMS'),
                'index.php?option=com_jigrid&view=griditems',
                $vName == 'griditems'
            );
        }
        // Joomla 2.5 SubMenu
        if(version_compare(JVERSION, '1.6.0', 'ge')) {
            JSubMenuHelper::addEntry(
                JText::_('COM_JIGRID_GRIDITEMS'),
                'index.php?option=com_jigrid&view=griditems',
                $vName == 'griditems'
            );
        }
    }
}