<?php 
/**
 * @version     $Id: script.jiextensionserver.php 022 2014-02-27 11:22:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiExtensionServerInstallerScript
{
    public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();

        // Create Tables
        $query = "CREATE TABLE IF NOT EXISTS `#__jiextensions` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `title` varchar(255) NOT NULL,
		    `alias` varchar(255) NOT NULL,
		    `description` text NOT NULL,
		    `publisher` int(11) NOT NULL,
            `state` tinyint(3) NOT NULL,
		    `attribs` text NOT NULL,
		    `publish_up` datetime NOT NULL,
		    `publish_down` datetime NOT NULL,
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
            `access` int(10) NOT NULL,
		    PRIMARY KEY (`id`),
		    UNIQUE alias (`alias`)
        );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiextensions_activity` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `uid` int(11) NOT NULL,
		    `sid` int(11) NOT NULL,
		    `site` varchar(255) NOT NULL,
		    `activity` varchar(255) NOT NULL,
		    `date` datetime NOT NULL,
		    PRIMARY KEY (`id`)
        );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiextensions_branches` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `eid` int(11) NOT NULL,
		    `title` varchar(255) NOT NULL,
		    `alias` varchar(255) NOT NULL,
		    `latest` int(11) NOT NULL,
		    `description` text NOT NULL,
            `state` tinyint(3) NOT NULL,
		    `attribs` text NOT NULL,
		    `publish_up` datetime NOT NULL,
		    `publish_down` datetime NOT NULL,
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
		    PRIMARY KEY (`id`),
		    UNIQUE branch (`eid`, `alias`)
        );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiextensions_subversions` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `eid` int(11) NOT NULL,
		    `bid` int(11) NOT NULL,
		    `jversion` varchar(255) NOT NULL,
		    `subversion` int(11) NOT NULL,
		    `changelog` text NOT NULL,
		    `premium` tinyint(3) NOT NULL,
		    `filepath` varchar(255) NOT NULL,
		    `downloadurl` varchar(255) NOT NULL,
		    `uploadurl` varchar(255) NOT NULL,
		    `downloadhits` int(11) NOT NULL,
		    `updatehits` int(11) NOT NULL,
		    `created` datetime NOT NULL,
            `state` tinyint(3) NOT NULL,
		    `publish_up` datetime NOT NULL,
		    `publish_down` datetime NOT NULL,
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
            `access` int(10) NOT NULL,
		    PRIMARY KEY (`id`),
		    UNIQUE subversion (`eid`, `bid`, `subversion`)
        );";
        $db->setQuery($query);
        $db->query();

        // Install Extensions
        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;

        // Install Plugins
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/'.$group;
            if (JFolder::exists($src.'/plugins/'.$group.'/'.$name))
            {
                $path = $src.'/plugins/'.$group.'/'.$name;
            }
            $installer = new JInstaller;
            $result = $installer->install($path);

            $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
            $db->setQuery($query);
            $db->query();
            $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
        }
        // Install Modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module)
        {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            if (is_null($client))
            {
                $client = 'site';
            }
            ($client == 'administrator') ? $path = $src.'/administrator/modules/'.$name : $path = $src.'/modules/'.$name;

            if($client == 'administrator')
            {
                $db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
                $isUpdate = (int)$db->loadResult();
            }

            $installer = new JInstaller;
            $result = $installer->install($path);

            $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
            if($client == 'administrator' && !$isUpdate)
            {
                $db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
                $id = (int)$db->loadResult();

                $db->setQuery("INSERT IGNORE INTO #__modules_menu (`moduleid`,`menuid`) VALUES (".$id.", 0)");
                $db->query();
            }
        }

        $this->installationResults($status);
    }
    public function uninstall($parent)
    {
        $db = JFactory::getDBO();

        // Delete Tables
        $query = "DROP TABLE IF EXISTS `#__jiextensions`;";
        $db->setQuery($query);
        $db->query();
        $query = "DROP TABLE IF EXISTS `#__jiextensions_activity`;";
        $db->setQuery($query);
        $db->query();
        $query = "DROP TABLE IF EXISTS `#__jiextensions_branches`;";
        $db->setQuery($query);
        $db->query();
        $query = "DROP TABLE IF EXISTS `#__jiextensions_subversions`;";
        $db->setQuery($query);
        $db->query();

        // Delete Extensions
        $status = new stdClass;
        $status->modules = array();
        $status->plugins = array();
        $manifest = $parent->getParent()->manifest;
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin)
        {
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($name)." AND folder = ".$db->Quote($group);
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions))
            {
                foreach ($extensions as $id)
                {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('plugin', $id);
                }
                $status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
            }

        }
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module)
        {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            $db = JFactory::getDBO();
            $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($name)."";
            $db->setQuery($query);
            $extensions = $db->loadColumn();
            if (count($extensions))
            {
                foreach ($extensions as $id)
                {
                    $installer = new JInstaller;
                    $result = $installer->uninstall('module', $id);
                }
                $status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
            }

        }
        $this->uninstallationResults($status);
    }
    public function update($type) {
    }
    private function installationResults($status) {
    }
    private function uninstallationResults($status) {
    }
}