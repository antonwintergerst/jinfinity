<?php 
/**
 * @version     $Id: script.jicontentinjector.php 036 2014-12-19 13:02:00Z Anton Wintergerst $
 * @package     JiContentInjector for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiContentInjectorInstallerScript
{
    private $installer_root;
    public function postflight($type, $parent)
    {
        $this->installer_root = $parent->getParent()->getPath('source');

        $db = JFactory::getDBO();
        // Create Tables
        $query = "CREATE TABLE IF NOT EXISTS `#__jiinjections` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `title` varchar(255) NOT NULL,
		    `state` tinyint(3) NOT NULL,
		    `context` varchar(255) NOT NULL DEFAULT 'content',
		    `selector` varchar(255) NOT NULL,
		    `injection` text NOT NULL,
		    `attribs` text NOT NULL,
		    `publish_up` datetime NOT NULL,
		    `publish_down` datetime NOT NULL,
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
		    PRIMARY KEY (`id`)
		    );";
        $db->setQuery($query);
        $db->query();

        // install extensions
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
            $installtext = ($installer->isUpgrade())? JText::_('COM_JIINSTALLER_EXTENSION_UPDATED') : JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED_SUCCESSFULLY'), $name, strtolower($installtext)), 'message');
        } else {
            $installtext = ($installer->isUpgrade())? JText::_('COM_JIINSTALLER_EXTENSION_UPDATE') : JText::_('COM_JIINSTALLER_EXTENSION_INSTALL');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALL_FAILED'), $name, strtolower($installtext)), 'error');
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
        <div class="jiadmin jiinstaller jicontentinjector">
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
        $query = "DROP TABLE IF EXISTS `#__jiinjections`;";
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