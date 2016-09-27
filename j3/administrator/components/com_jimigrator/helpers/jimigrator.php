<?php 
/**
 * @version     $Id: jimigrator.php 033 2014-12-15 10:21:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorHelper
{
    public static $extension = 'com_jimigrator';

    public static function addSubmenu($vName)
    {
    	// Joomla 3.0 SideBar
    	if(version_compare(JVERSION, '3.0.0', 'ge')) {
	        JHtmlSidebar::addEntry(
	            JText::_('JIMIGRATOR'),
	            'index.php?option=com_jimigrator&view=jimigrator',
	            $vName == 'jimigrator'
			);
            JHtmlSidebar::addEntry(
                JText::_('JIMIGRATOR_SNAPSHOTS'),
                'index.php?option=com_jimigrator&view=snapshots',
                $vName == 'snapshots'
            );
			JHtmlSidebar::addEntry(
	            JText::_('JIMIGRATOR_IMPORT'),
	            'index.php?option=com_jimigrator&view=import',
	            $vName == 'import'
	        );
			JHtmlSidebar::addEntry(
	            JText::_('JIMIGRATOR_EXPORT'),
	            'index.php?option=com_jimigrator&view=export',
	            $vName == 'export'
	        );
			JHtmlSidebar::addEntry(
	            JText::_('JIMIGRATOR_TOOLS'),
	            'index.php?option=com_jimigrator&view=tools',
	            $vName == 'tools'
	        );
			JHtmlSidebar::addEntry(
	            JText::_('JIMIGRATOR_LOGS'),
	            'index.php?option=com_jimigrator&view=logs',
	            $vName == 'logs'
	        );
		}
    	// Joomla 2.5 SubMenu
    	if(version_compare(JVERSION, '1.6.0', 'ge')) {
	        JSubMenuHelper::addEntry(
	            JText::_('JIMIGRATOR'),
	            'index.php?option=com_jimigrator&view=jimigrator',
	            $vName == 'jimigrator'
	        );
            JSubMenuHelper::addEntry(
                JText::_('JIMIGRATOR_SNAPSHOTS'),
                'index.php?option=com_jimigrator&view=snapshots',
                $vName == 'snapshots'
            );
	        JSubMenuHelper::addEntry(
	            JText::_('JIMIGRATOR_IMPORT'),
	            'index.php?option=com_jimigrator&view=import',
	            $vName == 'import'
			);
	        JSubMenuHelper::addEntry(
	            JText::_('JIMIGRATOR_EXPORT'),
	            'index.php?option=com_jimigrator&view=export',
	            $vName == 'export'
	        );
	        JSubMenuHelper::addEntry(
	            JText::_('JIMIGRATOR_TOOLS'),
	            'index.php?option=com_jimigrator&view=tools',
	            $vName == 'tools'
	        );
			JSubMenuHelper::addEntry(
	            JText::_('JIMIGRATOR_LOGS'),
	            'index.php?option=com_jimigrator&view=logs',
	            $vName == 'logs'
	        );
		}
    }
}