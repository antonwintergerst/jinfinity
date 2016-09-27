<?php
/**
 * @version     $Id: jiforms.php 011 2013-11-13 13:30:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiFormsHelper
{
    public static $extension = 'com_jiforms';

    public static function addSubmenu($vName)
    {
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla 3.0 SideBar
            JHtmlSidebar::addEntry(
                JText::_('COM_JIFORMS_FORMS'),
                'index.php?option=com_jiforms&view=forms',
                $vName == 'forms'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIFORMS_ACTIONS'),
                'index.php?option=com_jiforms&view=actions',
                $vName == 'actions'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIFORMS_EVENTS'),
                'index.php?option=com_jiforms&view=events',
                $vName == 'events'
            );
            JHtmlSidebar::addEntry(
                JText::_('COM_JIFORMS_EMAILS'),
                'index.php?option=com_jiforms&view=emails',
                $vName == 'emails'
            );
        } elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
            // Joomla 2.5 SubMenu
            JSubMenuHelper::addEntry(
                JText::_('COM_JIFORMS_FORMS'),
                'index.php?option=com_jiforms&view=forms',
                $vName == 'forms'
            );
            JSubMenuHelper::addEntry(
                JText::_('COM_JIFORMS_ACTIONS'),
                'index.php?option=com_jiforms&view=actions',
                $vName == 'actions'
            );
            JSubMenuHelper::addEntry(
                JText::_('COM_JIFORMS_EVENTS'),
                'index.php?option=com_jiforms&view=events',
                $vName == 'events'
            );
            JSubMenuHelper::addEntry(
                JText::_('COM_JIFORMS_EMAILS'),
                'index.php?option=com_jiforms&view=emails',
                $vName == 'emails'
            );
        }
    }
}