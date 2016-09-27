<?php 
/**
 * @version     $Id: jicustomfields.php 051 2014-03-29 11:39:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsHelper
{
    public static $extension = 'com_jicustomfields';

    public static function addSubmenu($vName)
    {
        JHtmlSidebar::addEntry(
            JText::_('JICUSTOMFIELDS_FIELDS'),
            'index.php?option=com_jicustomfields&view=fields',
            $vName == 'fields');
        JHtmlSidebar::addEntry(
            JText::_('JICUSTOMFIELDS_CATMAPS'),
            'index.php?option=com_jicustomfields&view=catmaps',
            $vName == 'catmaps');
    }
}