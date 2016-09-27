<?php
/**
 * @version     $Id: jiextensionserver.php 011 2014-01-04 11:36:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiExtensionServerHelper
{
    public static $extension = 'com_jiextensionserver';

    public static function addSubmenu($vName)
    {
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla 3.0 SideBar
            JHtmlSidebar::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_EXTENSIONS'),
                'index.php?option=com_jiextensionserver&view=extensions',
                $vName == 'extensions'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_BRANCHES'),
                'index.php?option=com_jiextensionserver&view=branches',
                $vName == 'branches'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_SUBVERSIONS'),
                'index.php?option=com_jiextensionserver&view=subversions',
                $vName == 'subversions'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_ACTIVITY'),
                'index.php?option=com_jiextensionserver&view=activities',
                $vName == 'activity'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_USERS'),
                'index.php?option=com_jiextensionserver&view=users',
                $vName == 'users'
            );
        } elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
            // Joomla 2.5 SubMenu
            JSubMenuHelper::addEntry(
                JText::_('COM_JIEXTENSIONSERVER_EXTENSIONS'),
                'index.php?option=com_jiextensionserver&view=extensions',
                $vName == 'extensions'
            );
        }
    }
}