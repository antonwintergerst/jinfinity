<?php
/**
 * @version     $Id: script.jigrid.php 047 2014-11-27 13:44:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiGridInstallerScript
{
    public function preflight($action, $installer) {

    }
    public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();

        // Create Tables
        $query = "CREATE TABLE IF NOT EXISTS `#__jigrid` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `alias` varchar(255) NOT NULL,
          `parent_id` int(11) NOT NULL,
          `lft` int(11) NOT NULL,
          `rgt` int(11) NOT NULL,
          `level` int(11) NOT NULL,
          `path` varchar(255) NOT NULL,
          `type` varchar(255) NOT NULL,
          `attribs` text NOT NULL,
          `state` tinyint(3) NOT NULL,
          `ordering` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        );";
        $db->setQuery($query);
        $db->query();

        $db = JFactory::getDBO();
        // Add default rows
        $query = 'SELECT COUNT(`id`) FROM #__jigrid';
        $db->setQuery($query);
        $rows = $db->loadResult();
        if($rows==null || $rows==0) {
            $query = 'INSERT INTO #__jigrid (`id`, `title`, `alias`, `parent_id`, `lft`, `rgt`, `level`, `path`, `type`, `attribs`, `state`, `ordering`) VALUES
(1, \'ROOT\', \'root\', 0, 0, 41, 0, \'\', \'grid\', \'null\', 1, 0),
(4, \'Nav Row\', \'nav\', 1, 5, 10, 1, \'nav\', \'row\', \'{"class":"centered","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","autospan":0,"minwidth":"","position":"","component":0,"message":0}\', 1, 0),
(5, \'Top Row\', \'top\', 1, 1, 4, 1, \'top\', \'row\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","autospan":0,"minwidth":"","position":"","component":0,"message":0}\', 1, 0),
(6, \'Main Row\', \'main\', 1, 15, 34, 1, \'main\', \'row\', \'{"class":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"8","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","position":"","component":0,"message":0}\', 1, 0),
(8, \'Footer Row\', \'footer\', 1, 35, 40, 1, \'footer\', \'row\', \'{"class":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"6","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","position":"","component":0,"message":0}\', 1, 0),
(17, \'Nav Cell\', \'navcell\', 4, 6, 7, 2, \'nav/navcell\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"9","autospan":1,"minwidth":"","position":"nav","component":0,"message":0}\', 1, 0),
(21, \'Top Cell\', \'topcell\', 5, 2, 3, 2, \'top/topcell\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"12","autospan":0,"minwidth":"","position":"top","component":0,"message":0}\', 1, 1),
(25, \'Left\', \'left\', 6, 16, 17, 2, \'main/left\', \'cell\', \'{"class":"hide-phone","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","span":"2","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","position":"left","component":0,"message":0}\', 0, 1),
(27, \'Right\', \'right\', 6, 32, 33, 2, \'main/right\', \'cell\', \'{"class":"hide-phone","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"4","autospan":0,"minwidth":"","position":"right","component":0,"message":0}\', 1, 3),
(32, \'Footer Left\', \'footerleft\', 8, 36, 37, 2, \'footer/footerleft\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"6","autospan":0,"minwidth":"","position":"footer","component":0,"message":0}\', 1, 1),
(33, \'Footer Right\', \'footerright\', 8, 38, 39, 2, \'footer/footerright\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"6","autospan":0,"minwidth":"","position":"footerright","component":0,"message":0}\', 1, 2),
(36, \'Main Grid\', \'maingrid\', 6, 18, 31, 2, \'main/maingrid\', \'grid\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"8","autospan":1,"minwidth":"","position":"","component":0,"message":0}\', 1, 2),
(37, \'Above Row\', \'above\', 36, 19, 22, 3, \'main/maingrid/above\', \'row\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","autospan":0,"minwidth":"","position":"","component":0,"message":0}\', 1, 0),
(38, \'Main Row\', \'mainrow\', 36, 23, 26, 3, \'main/maingrid/mainrow\', \'row\', \'{"class":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","span":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","position":"","component":0,"message":0}\', 1, 0),
(39, \'Below Row\', \'below\', 36, 27, 30, 3, \'main/maingrid/below\', \'row\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","autospan":0,"minwidth":"","position":"","component":0,"message":0}\', 1, 0),
(40, \'Above Cell\', \'abovecell\', 37, 20, 21, 4, \'main/maingrid/above/abovecell\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"12","autospan":0,"minwidth":"","position":"above","component":0,"message":0}\', 1, 1),
(41, \'Main\', \'maincell\', 38, 24, 25, 4, \'main/maingrid/mainrow/Main\', \'cell\', \'{"class":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","span":"12","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","position":"","component":1,"message":1}\', 1, 0),
(42, \'Below Cell\', \'belowcell\', 39, 28, 29, 4, \'main/maingrid/below/belowcell\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"12","autospan":0,"minwidth":"","position":"below","component":0,"message":0}\', 1, 0),
(46, \'Showcase Row\', \'showcase\', 1, 11, 14, 1, \'showcase\', \'row\', \'{"class":"hide-phone","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":1,"only_type":"","cols-tv":"","cols":"12","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"","autospan":0,"minwidth":"","position":"","component":0,"message":0}\', 1, 0),
(47, \'Showcase Cell\', \'showcasecell\', 46, 12, 13, 2, \'showcase/showcasecell\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"12","autospan":0,"minwidth":"","position":"showcase","component":0,"message":0}\', 1, 0),
(48, \'Nav Cell 2\', \'navcell2\', 4, 8, 9, 2, \'nav/navcell2\', \'cell\', \'{"class":"","hide_tv":0,"hide_desktop":0,"hide_tablet":0,"hide_phone":0,"only_type":"","cols-tv":"","cols":"","cols-tablet":"","cols-phone":"","ypercent-tv":"","ypercent":"","ypercent-tablet":"","ypercent-phone":"","span":"3","autospan":0,"minwidth":"","position":"nav2","component":0,"message":0}\', 1, 0);';
            $db->setQuery($query);
            $db->query();
        }

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
        $query = "DROP TABLE IF EXISTS `#__jigrid`;";
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