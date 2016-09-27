<?php
/**
 * @version     $Id: script.install.php 036 2014-12-15 13:23:00Z Anton Wintergerst $
 * @package     Jinfinity Installer for Joomla 1.5 only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die;
require_once(dirname(__FILE__).'/script.install.php');

class Com_JiInstallerInstallerScriptJ15 extends Com_JiInstallerInstallerScript
{
    /**
     * Joomla 1.5 overrides
     */
    public function enablePlugin($plugin, $group)
    {
        $db = JFactory::getDbo();
        $query = "UPDATE #__plugins SET `published`=1 WHERE `element`=".$db->Quote($plugin)." AND `folder`=".$db->Quote($group);
        $db->setQuery($query);
        $db->query();
    }

    public function redirectInstaller()
    {
        $app = JFactory::getApplication();
        $view = JRequest::get('option')=='com_installer'? JRequest::get('view'): '';
        $app->redirect('index.php?option=com_installer' . ($view ? '&view=' . $view : ''));
    }

    public function removeInstaller()
    {
        $db = JFactory::getDBO();

        // remove component database entries
        $query = 'DELETE FROM #__menu WHERE `title`="com_jiinstaller"';
        $db->setQuery($query);
        $db->query();

        $query = 'DELETE FROM #__components WHERE `option`="com_jiinstaller"';
        $db->setQuery($query);
        $db->query();

        // reset the auto-increment
        if(in_array($db->name, array('mysql', 'mysqli'))) {
            $db->setQuery('ALTER TABLE `#__menu` AUTO_INCREMENT = 1');
            $db->query();
        }

        $this->removeInstallerFiles();
    }
}
$installer = new Com_JiInstallerInstallerScriptJ15();
$installer->preflight(null, null);