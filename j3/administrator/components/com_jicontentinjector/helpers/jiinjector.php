<?php
/*
 * @version     $Id: jicontentinjector.php 010 2013-06-05 18:19:00Z Anton Wintergerst $
 * @package     Jinfinity Content Injector for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiContentInjectorHelper
{
    public static $extension = 'com_jicontentinjector';

    public static function addSubmenu($vName)
    {
        // Joomla 3.0 SideBar
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::addEntry(
                JText::_('JICONTENTINJECTOR_INJECTIONS'),
                'index.php?option=com_jicontentinjector&view=injections',
                $vName == 'injections'
            );
        }
        // Joomla 2.5 SubMenu
        if(version_compare(JVERSION, '1.6.0', 'ge')) {
            JSubMenuHelper::addEntry(
                JText::_('JICONTENTINJECTOR_INJECTIONS'),
                'index.php?option=com_jicontentinjector&view=injections',
                $vName == 'injections'
            );
        }
    }
}