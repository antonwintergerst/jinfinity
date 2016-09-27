<?php
/**
 * @version     $Id: script.jigrid.php 049 2014-12-19 14:10:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 1.7+
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

        // install extensions
        $this->installer_root = $parent->getParent()->getPath('source');
        $this->messages = array();

        $status = new stdClass;
        $status->templates = array();
        $status->components = array();
        $status->modules = array();
        $status->plugins = array();

        // install plugins
        if(JFolder::exists($this->installer_root.'/plugins')) {
            if($groups = JFolder::folders($this->installer_root.'/plugins')) {
                foreach($groups as $group) {
                    if($extensions = JFolder::folders($this->installer_root.'/plugins/'.$group)) {
                        foreach($extensions as $extension) {
                            if($result = $this->installExtension($extension, 'plg', $group)) {
                                // enable plugin
                                $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($extension)." AND folder=".$db->Quote($group);
                                $db->setQuery($query);
                                $db->query();

                                $status->plugins[] = $result;
                            }
                        }
                    }
                }
            }
        }

        // install modules
        if(JFolder::exists($this->installer_root.'/modules')) {
            if($extensions = JFolder::folders($this->installer_root.'/modules')) {
                foreach($extensions as $extension) {
                    if($result = $this->installExtension($extension, 'mod')) $status->modules[] = $result;
                }
            }
        }

        // remove front-end parts
        jimport('joomla.filesystem.folder');
        $src = JPATH_SITE.'/components/com_jimigrator';
        if(JFolder::exists($src)) JFolder::delete($src);

        // show results
        $this->installationResults($status);
    }

    private function installExtension($extension, $type=null, $group=null)
    {
        // must have an extension type
        if(!isset($type)) return false;

        $app = JFactory::getApplication();
        if($type=='com') {
            $directory = 'components';
        } elseif($type=='mod') {
            $directory = 'modules';
        } elseif($type=='plg') {
            $directory = 'plugins';
        } elseif($type=='tpl') {
            $directory = 'templates';
        } else {
            return false;
        }

        if(isset($group)) {
            $path = $this->installer_root.'/'.$directory.'/'.$group.'/'.$extension;
        } else {
            $path = $this->installer_root.'/'.$directory.'/'.$extension;
        }

        // trim type prefix
        if($type=='com') $extension = ltrim($extension, '_'.$type);

        if(!JFolder::exists($path) || !JFile::exists($path.'/'.$extension.'.xml')) return false;

        // install extension
        $installer = new JInstaller;
        $result = $installer->install($path);

        // load extension language
        $lang = JFactory::getLanguage();
        $lang_ext = ($type=='plg')? $type.'_'.$group.'_'.$extension : $type.'_'.$extension;
        $lang->load($lang_ext, $this->installer_root, null, false, true) || $lang->load($lang_ext, JPATH_ADMINISTRATOR, null, false, true);

        // set installer message
        if($type=='com') {
            $name = JText::_($extension).' (component)';
        } elseif($type=='mod') {
            $name = JText::_($extension).' (module)';
        } elseif($type=='plg') {
            $name = JText::_($extension).' ('.$group.' plugin)';
        } elseif($type=='tpl') {
            $name = JText::_($extension).' (template)';
        }
        if($result) {
            $installtext = JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED_SUCCESSFULLY'), $name, strtolower($installtext)), 'message');
        } else {
            $installtext = JText::_('COM_JIINSTALLER_EXTENSION_FAILED');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALL_FAILED'), $name), 'error');
        }
        $data = $installer->parseXMLInstallFile($path.'/'.$extension.'.xml');
        if(isset($group)) $data['group'] = $group;
        $data['type'] = $type;
        $data['alias'] = $extension;
        $data['message'] = $installer->message;
        $data['installed'] = $result;
        if($type=='com') {
            $data['link'] = 'index.php?option=com_'.$extension;
        } elseif($type=='plg') {
            $data['link'] = 'index.php?option=com_plugins&view=plugins&filter_order=extension_id&filter_order_Dir=desc';
        } elseif($type=='mod') {
            $data['link'] = 'index.php?option=com_modules';
        }
        $data['installtext'] = $installtext;

        // set licence
        $data['licence'] = '';
        if(strpos($data['version'], 'PRO')!==false) {
            $data['licence'] = 'PRO';
        } elseif(strpos($data['version'], 'FREE')!==false) {
            $data['licence'] = 'FREE';
        }
        $data['version'] = str_replace(array('FREE', 'PRO'), '', $data['version']);

        return (object) $data;
    }

    private function installationResults($status)
    {
        // core joomla script/stylesheet functionality not available here
        ?>
        <div class="jiadmin jiinstaller jigrid">
            <?php foreach($status as $type=>$extensions): ?>
                <?php if(count($extensions)>0): ?>
                    <div class="items extensions <?php echo $type; ?>">
                        <h2 class="subtitle"><?php echo JText::_('COM_JIINSTALLER_'.$type.'_INCLUDED'); ?></h2>
                        <?php foreach($extensions as $extension): ?>
                            <?php $this->renderExtensionResult($extension); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php
    }

    private function renderExtensionResult($extension)
    { ?>
        <div class="item <?php echo $extension->type; ?> <?php echo $extension->group; ?> <?php echo $extension->alias; ?>">
            <div class="item-text">
                <h2 class="item-title">
                    <?php if(isset($extension->link)): ?>
                        <a href="<?php echo $extension->link; ?>" title="<?php echo sprintf(JText::_('COM_JIINSTALLER_EXTENSION_LINK_HINT'), $extension->name); ?>">
                            <span class="item-name"><?php echo JText::_($extension->name); ?></span>
                            <span class="item-version">v<?php echo $extension->version; ?></span>
                        </a>
                    <?php else: ?>
                        <span class="item-name"><?php echo JText::_($extension->name); ?></span>
                        <span class="item-version">v<?php echo $extension->version; ?></span>
                    <?php endif; ?>
                </h2>
                <span class="item-status">
                    <?php if($extension->installed): ?>
                        <span class="label label-info"><?php echo $extension->installtext; ?></span>
                        <?php if($extension->type=='plg'): ?>
                            <span class="label label-info"><?php echo JText::_('COM_JIINSTALLER_ENABLED'); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="label label-danger"><?php echo $extension->installtext; ?></span>
                        <?php if($extension->type=='plg'): ?>
                            <span class="label label-danger"><?php echo JText::_('COM_JIINSTALLER_ENABLED'); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if($extension->licence=='FREE'): ?>
                        <span class="label label-error item-licence free"><?php echo $extension->licence; ?></span>
                    <?php elseif($extension->licence=="PRO"): ?>
                        <span class="label label-success item-licence pro"><?php echo $extension->licence; ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="item-description">
                <?php echo $extension->message; ?>
            </div>
            <?php if($extension->licence=='FREE'): ?>
                <div class="licence-upgrade">
                    <?php echo sprintf(JText::_('JI_PRO_UPGRADE'), JText::_($extension->alias)); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php
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
    private function uninstallationResults($status) {
    }
}