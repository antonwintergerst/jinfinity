<?php 
/**
 * @version     $Id: script.install.php 038 2014-12-19 14:13:00Z Anton Wintergerst $
 * @package     Jinfinity Installer for Joomla 1.7-3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiInstallerInstallerScript
{
    public function preflight($type, $parent)
    {
        if(!defined('INSTALLER_ROOT')) define('INSTALLER_ROOT', dirname(__FILE__));
        if(!defined('EXTENSIONS_ROOT')) define('EXTENSIONS_ROOT', INSTALLER_ROOT.'/extensions');

        $this->prepareInstaller();

        // load installer language
        JFactory::getLanguage()->load('com_jiinstaller', JPATH_ADMINISTRATOR);

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();

        // assemble install directories
        $this->assembleExtensions();

		// install extensions
        $this->messages = array();

        $status = new stdClass;
        $status->templates = array();
        $status->components = array();
        $status->modules = array();
        $status->plugins = array();

        // install components
        if(JFolder::exists(INSTALLER_ROOT.'/components')) {
            if($extensions = JFolder::folders(INSTALLER_ROOT.'/components')) {
                foreach($extensions as $extension) {
                    if($result = $this->installExtension($extension, 'com')) $status->components[] = $result;
                }
            }
        }

        // install plugins
        if(JFolder::exists(INSTALLER_ROOT.'/plugins')) {
            if($groups = JFolder::folders(INSTALLER_ROOT.'/plugins')) {
                foreach($groups as $group) {
                    if($extensions = JFolder::folders(INSTALLER_ROOT.'/plugins/'.$group)) {
                        foreach($extensions as $extension) {
                            if($result = $this->installExtension($extension, 'plg', $group)) {
                                // enable plugin
                                $this->enablePlugin($extension, $group);

                                $status->plugins[] = $result;
                            }
                        }
                    }
                }
            }
        }

        // install modules
        if(JFolder::exists(INSTALLER_ROOT.'/modules')) {
            if($extensions = JFolder::folders(INSTALLER_ROOT.'/modules')) {
                foreach($extensions as $extension) {
                    if($result = $this->installExtension($extension, 'mod')) $status->modules[] = $result;
                }
            }
        }

        // install templates
        if(JFolder::exists(INSTALLER_ROOT.'/templates')) {
            if($extensions = JFolder::folders(INSTALLER_ROOT.'/templates')) {
                foreach($extensions as $extension) {
                    if($result = $this->installExtension($extension, 'tpl')) $status->templates[] = $result;
                }
            }
        }

        // check for empty status
        $totalinstalled = 0;
        foreach($status as $results) {
            $totalinstalled+= count($results);
        }
        if($totalinstalled==0) $this->raiseError(JText::_('COM_JIINSTALLER_NOTHING_ERROR'));

        ob_end_clean();
        ob_start();
        ob_implicit_flush(false);
        $this->installationResults($status);
        $msg = ob_get_contents();
        ob_end_clean();

        $app->setUserState('com_installer.message', $msg);

        // remove installer
        $this->cleanupInstall();
        $this->removeInstaller();

        // redirect to avoid output of standard messages
        $this->redirectInstaller();
    }

    public function enablePlugin($plugin, $group)
    {
        $db = JFactory::getDbo();
        $query = "UPDATE #__extensions SET `enabled`=1 WHERE `type`='plugin' AND `element`=".$db->Quote($plugin)." AND `folder`=".$db->Quote($group);
        $db->setQuery($query);
        $db->query();
    }

    public function redirectInstaller()
    {
        $app = JFactory::getApplication();
        $view = $app->input->get('option') == 'com_installer' ? $app->input->get('view') : '';
        $app->redirect('index.php?option=com_installer' . ($view ? '&view=' . $view : ''));
    }

    private $childextensions = array();
    private function assembleExtensions()
    {
        if(version_compare(JVERSION, '3', 'ge')) {
            $srcdir = 'j3';
        } elseif(version_compare(JVERSION, '1.7', 'ge')) {
            $srcdir = 'j25';
        } elseif(version_compare(JVERSION, '1.5', 'ge') && version_compare(JVERSION, '1.6', 'l')) {
            $srcdir = 'j15';
        } else {
            $this->raiseError(JText::_('COM_JIINSTALLER_JVERSION_ERROR'));
        }

        $xmlsearches = array(
            'com'=>'administrator/components',
            'mod'=>'modules',
            'plg'=>'plugins',
            'tpl'=>'templates'
        );
        // find manifests
        foreach($xmlsearches as $type=>$xmlsearch) {
            $srcpaths = array(
                EXTENSIONS_ROOT.'/'.$srcdir,
                EXTENSIONS_ROOT.'/all'
            );
            foreach($srcpaths as $src) {
                $srcpath = $src.'/'.$xmlsearch;
                if(file_exists($srcpath)) {
                    if($type=='plg') {
                        if($groups = JFolder::folders($srcpath)) {
                            foreach($groups as $group) {
                                if($extensions = JFolder::folders($srcpath.'/'.$group)) {
                                    foreach($extensions as $extension) {
                                        if(in_array($extension, array('.', '..'))) continue;

                                        if($srcdir=='j15') {
                                            $manifestfile = $srcpath.'/'.$group.'/'.$extension.'.xml';
                                        } else {
                                            $manifestfile = $srcpath.'/'.$group.'/'.$extension.'/'.$extension.'.xml';
                                        }
                                        if(!file_exists($manifestfile)) continue;

                                        // find files
                                        $this->assembleExtension(
                                            (object)array(
                                                'type'=>$type,
                                                'group'=>$group,
                                                'name'=>$extension,
                                                'srcpaths'=>$srcpaths,
                                                'manifest'=>$manifestfile
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    } else {
                        if($extensions = JFolder::folders($srcpath)) {
                            foreach($extensions as $extension) {
                                if(in_array($extension, array('.', '..')) || !is_dir($srcpath.'/'.$extension)) continue;
                                if($type=='tpl') {
                                    $manifest = 'templateDetails.xml';
                                } elseif($type=='com' && $srcdir=='j15') {
                                    // Joomla 1.5 uses manifest.xml
                                    $manifest = 'manifest.xml';
                                } else {
                                    $manifest = str_replace('com_', '', $extension).'.xml';
                                }

                                $manifestfile = $srcpath.'/'.$extension.'/'.$manifest;
                                if(!file_exists($manifestfile)) continue;

                                // find files
                                $this->assembleExtension(
                                    (object)array(
                                        'type'=>$type,
                                        'name'=>$extension,
                                        'srcpaths'=>$srcpaths,
                                        'manifest'=>$manifestfile
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    private function assembleExtension($extension)
    {
        // find files in common and jversion directories
        foreach($extension->srcpaths as $src) {
            $extension->src = $src;

            $dest = INSTALLER_ROOT;
            if(isset($this->childextensions[$extension->type.$extension->name])) $dest = $this->childextensions[$extension->type.$extension->name];

            if($extension->type=='plg') {
                if(version_compare(JVERSION, '1.7', 'ge')) {
                    $src = $extension->src.'/plugins/'.$extension->group.'/'.$extension->name;
                    $dest.= '/plugins/'.$extension->group.'/'.$extension->name;
                } else {
                    // Joomla 1.5 has plugin manifest in group directory
                    $src = $extension->src.'/plugins/'.$extension->group;
                    $dest.= '/plugins/'.$extension->group;
                }
            } elseif($extension->type=='com') {
                $src = $extension->src.'/components/'.$extension->name;
                $dest.= '/components/'.$extension->name;
            } elseif($extension->type=='mod') {
                $src = $extension->src.'/modules/'.$extension->name;
                $dest.= '/modules/'.$extension->name;
            } elseif($extension->type=='tpl') {
                $src = $extension->src.'/templates/'.$extension->name;
                $dest.= '/templates/'.$extension->name;
            }

            if(!file_exists($dest)) mkdir($dest, 0755, true);

            // read xml
            $manifest = simplexml_load_file($extension->manifest);

            // TODO: check FREE isn't being installed over PRO
            // find installed version

            // check if installed is FREE or PRO


            // copy manifest
            JFile::copy($extension->manifest, $dest.'/'.basename($extension->manifest));

            if(isset($manifest->files)) {
                // copy site files
                foreach($manifest->files->filename as $file) {
                    $file = (string) $file;
                    $srcfile = $src.'/'.$file;
                    if(file_exists($srcfile)) {
                        if($extension->type=='com') {
                            if(!file_exists($dest.'/site')) mkdir($dest.'/site', 0755, true);
                            $destfile = $dest.'/site/'.$file;
                        } else {
                            if(!file_exists($dest)) mkdir($dest, 0755, true);
                            $destfile = $dest.'/'.$file;
                        }
                        JFile::copy($srcfile, $destfile);
                    }
                }

                // copy site directories
                foreach($manifest->files->folder as $dir) {
                    $dir = (string) $dir;
                    $srcdir = $src.'/'.$dir;
                    if(file_exists($srcdir)) {
                        if($extension->type=='com') {
                            if(!file_exists($dest.'/site')) mkdir($dest.'/site', 0755, true);
                            JFolder::copy($srcdir, $dest.'/site/'.$dir);
                        } else {
                            JFolder::copy($srcdir, $dest.'/'.$dir, '', true);
                        }
                    }
                }
            }

            if(isset($manifest->media)) {
                // copy media directories
                foreach($manifest->media->folder as $dir) {
                    $dir = (string) $dir;
                    $srcdir = $extension->src.'/media/'.$dir;
                    if(file_exists($srcdir)) {
                        if(!file_exists($dest.'/media')) mkdir($dest.'/media', 0755, true);
                        JFolder::copy($srcdir, $dest.'/media/'.$dir, '', true);
                    }
                }
            }

            if(isset($manifest->languages)) {
                // copy site language files
                foreach($manifest->languages->language as $path) {
                    $path = (string) $path;
                    $srcfile = $extension->src.'/language/'.$path;
                    if(file_exists($srcfile)) {
                        $destfile = $dest.'/language/'.$path;
                        if(!file_exists(dirname($destfile))) mkdir(dirname($destfile), 0755, true);
                        JFile::copy($srcfile, $destfile);
                    }
                }
            }

            if($extension->type=='com') {
                if(isset($manifest->scriptfile)) {
                    // copy script file
                    $file = (string) $manifest->scriptfile;
                    $srcfile = $extension->src.'/administrator/components/'.$extension->name.'/'.$file;
                    if(file_exists($srcfile)) JFile::copy($srcfile, $dest.'/'.$file);
                }
                if(isset($manifest->installfile)) {
                    // copy install file
                    $file = (string) $manifest->installfile;
                    $srcfile = $extension->src.'/administrator/components/'.$extension->name.'/'.$file;
                    if(file_exists($srcfile)) JFile::copy($srcfile, $dest.'/'.$file);
                }
                if(isset($manifest->uninstallfile)) {
                    // copy uninstall file
                    $file = (string) $manifest->uninstallfile;
                    $srcfile = $extension->src.'/administrator/components/'.$extension->name.'/'.$file;
                    if(file_exists($srcfile)) JFile::copy($srcfile, $dest.'/'.$file);
                }

                if(isset($manifest->administration->languages)) {
                    // copy admin language files
                    foreach($manifest->administration->languages->language as $path) {
                        $path = (string) $path;
                        $srcfile = $extension->src.'/administrator/language/'.$path;
                        if(file_exists($srcfile)) {
                            $destfile = $dest.'/admin/language/'.$path;
                            if(!file_exists(dirname($destfile))) mkdir(dirname($destfile), 0755, true);
                            JFile::copy($srcfile, $destfile);
                        }
                    }
                }

                if(isset($manifest->administration->files)) {
                    // copy admin files
                    foreach($manifest->administration->files->filename as $file) {
                        $file = (string) $file;
                        $srcfile = $extension->src.'/administrator/components/'.$extension->name.'/'.$file;
                        if(file_exists($srcfile)) {
                            if(!file_exists($dest.'/admin')) mkdir($dest.'/admin', 0755, true);
                            JFile::copy($srcfile, $dest.'/admin/'.$file);
                        }
                    }

                    // copy admin directories
                    foreach($manifest->administration->files->folder as $dir) {
                        $dir = (string) $dir;
                        $srcdir = $extension->src.'/administrator/components/'.$extension->name.'/'.$dir;
                        if(file_exists($srcdir)) {
                            if(!file_exists($dest.'/admin')) mkdir($dest.'/admin', 0755, true);
                            JFolder::copy($srcdir, $dest.'/admin/'.$dir, '', true);
                        }
                    }
                }

                if(isset($manifest->modules->module)) {
                    // attach child modules
                    foreach($manifest->modules->module as $module) {
                        $module = $module->attributes();
                        $module = (string) $module['module'];
                        $this->childextensions['mod'.$module] = $dest;
                    }
                }

                if(isset($manifest->plugins->plugin)) {
                    // attach child plugins
                    foreach($manifest->plugins->plugin as $plugin) {
                        $plugin = $plugin->attributes();
                        $plugin = (string) $plugin['plugin'];
                        $this->childextensions['plg'.$plugin] = $dest;
                    }
                }

            } elseif($extension->type=='plg') {
                if(isset($manifest->languages)) {
                    // copy admin language files
                    foreach($manifest->languages->language as $path) {
                        $path = (string) $path;
                        $srcfile = $extension->src.'/administrator/language/'.$path;
                        if(file_exists($srcfile)) {
                            $destfile = $dest.'/language/'.$path;
                            if(!file_exists(dirname($destfile))) mkdir(dirname($destfile), 0755, true);
                            JFile::copy($srcfile, $destfile);
                        }
                    }
                }
            }
        }
    }

    private function raiseError($msg)
    {
        $app = JFactory::getApplication();
        $app->enqueueMessage($msg, 'error');

        // remove installer
        $this->cleanupInstall();
        $this->removeInstaller();

        // redirect to avoid output of standard messages
        $this->redirectInstaller();
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
            // check if already installed
            $db = JFactory::getDbo();
            $query = 'SELECT `extension_id` FROM #__extensions WHERE `element`='.$db->quote($extension).' AND `type`="template"';
            $db->setQuery($query);
            $exists = $db->loadResult();
            if($exists!=null) {
                $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_INSTALL_FAILED_TEMPLATE_EXISTS'), JText::_($extension)), 'error');
                return false;
            }
            $directory = 'templates';
        } else {
            return false;
        }

        if(isset($group)) {
            if(version_compare(JVERSION, '1.7', 'ge')) {
                $path = INSTALLER_ROOT.'/'.$directory.'/'.$group.'/'.$extension;
            } else {
                // Joomla 1.5 has plugin manifest in group directory
                $path = INSTALLER_ROOT.'/'.$directory.'/'.$group;
            }
        } else {
            $path = INSTALLER_ROOT.'/'.$directory.'/'.$extension;
        }

        // trim component type prefix
        if($type=='com') $extension = str_replace('com_', '', $extension);

        if($type=='tpl') {
            $manifest = $path.'/templateDetails.xml';
        } elseif($type=='com' && !version_compare(JVERSION, '1.7', 'ge')) {
            // Joomla 1.5 uses manifest.xml
            $manifest = $path.'/manifest.xml';
        } else {
            $manifest = $path.'/'.$extension.'.xml';
        }

        if(!JFolder::exists($path) || !JFile::exists($manifest)) return false;

        // install extension
        $installer = new JInstaller;
        $result = $installer->install($path);

        // load extension language
        $lang = JFactory::getLanguage();
        $lang_ext = ($type=='plg')? $type.'_'.$group.'_'.$extension : $type.'_'.$extension;
        $lang->load($lang_ext, INSTALLER_ROOT, null, false, true) || $lang->load($lang_ext, JPATH_ADMINISTRATOR, null, false, true);

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
            //$installtext = ($installer->isUpgrade())? JText::_('COM_JIINSTALLER_EXTENSION_UPDATED') : JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED');
            $installtext = JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALLED_SUCCESSFULLY'), $name, strtolower($installtext)), 'message');
        } else {
            $installtext = JText::_('COM_JIINSTALLER_EXTENSION_FAILED');
            $app->enqueueMessage(sprintf(JText::_('COM_JIINSTALLER_EXTENSION_INSTALL_FAILED'), $name), 'error');
        }
        $data = array();
        $manifest = simplexml_load_file($manifest);
        $data['name'] = (string) $manifest->name;
        $data['version'] = (string) $manifest->version;

        if(isset($group)) $data['group'] = $group;
        $data['type'] = $type;
        $data['alias'] = $extension;
        $data['message'] = $installer->message.$installer->get('extension_message');
        $data['installed'] = $result;
        if($type=='com') {
            $data['link'] = 'index.php?option=com_'.$extension;
        } elseif($type=='plg') {
            $data['link'] = 'index.php?option=com_plugins&view=plugins&filter_order=extension_id&filter_order_Dir=desc';
        } elseif($type=='mod') {
            $data['link'] = 'index.php?option=com_modules';
        } elseif($type=='tpl') {
            $data['link'] = 'index.php?option=com_templates&view=styles';
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
        <div class="jiadmin jiinstaller">
            <link href="<?php echo JURI::root(true).'/media/jiframework/css/admin.css'; ?>" rel="stylesheet" media="screen">
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
        <div class="item <?php echo $extension->type; ?> <?php echo $extension->alias; ?><?php if(isset($extension->group)) echo ' '.$extension->group; ?> ">
            <span class="item-image">
                <img src="<?php echo JURI::root(); ?>media/<?php echo $extension->alias; ?>/images/<?php echo $extension->alias; ?>-icon.png" alt="" />
            </span>
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

    private function prepareInstaller()
    {
        // copy language files
        $path = INSTALLER_ROOT.'/language';
        if($languages = JFolder::folders($path)) {
            JFolder::copy($path, JPATH_ADMINISTRATOR.'/language', '', true);
        }
    }

    private function cleanupInstall()
    {
        $installer = JInstaller::getInstance();
        $source = str_replace('\\', '/', $installer->getPath('source'));
        $tmp = dirname(str_replace('\\', '/', JFactory::getConfig()->get('tmp_path') . '/x'));

        if (strpos($source, $tmp) === false || $source == $tmp)
        {
            return;
        }

        $package_folder = dirname($source);

        if ($package_folder == $tmp)
        {
            $package_folder = $source;
        }

        $package_file = '';
        switch (JFactory::getApplication()->input->getString('installtype', ''))
        {
            case 'url':
                $package_file = JFactory::getApplication()->input->getString('install_url', '');
                $package_file = str_replace(dirname($package_file), '', $package_file);
                break;
            case 'upload':
            default:
                if (isset($_FILES) && isset($_FILES['install_package']) && isset($_FILES['install_package']['name']))
                {
                    $package_file = $_FILES['install_package']['name'];
                }
                break;
        }

        if (!$package_file && $package_folder != $source)
        {
            $package_file = str_replace($package_folder . '/', '', $source) . '.zip';
        }

        $package_file = $tmp . '/' . $package_file;

        JInstallerHelper::cleanupInstall($package_file, $package_folder);
    }

    public function removeInstaller()
    {
        $db = JFactory::getDBO();

        // remove component database entries
        $query = $db->getQuery(true)
            ->delete('#__menu')
            ->where('title = '.$db->quote('com_jiinstaller'));
        $db->setQuery($query);
        $db->execute();

        // reset the auto-increment
        if(in_array($db->name, array('mysql', 'mysqli'))) {
            $db->setQuery('ALTER TABLE `#__menu` AUTO_INCREMENT = 1');
            $db->execute();
        }

        $this->removeInstallerFiles();
    }

    public function removeInstallerFiles()
    {
        // delete language files
        $path = JPATH_ADMINISTRATOR.'/language';
        if($languages = JFolder::folders($path)) {
            foreach($languages as $lang) {
                JFile::delete($path.'/'.$lang.'/'.$lang.'.com_jiinstaller.ini');
            }
        }

        // delete component directories
        if(JFolder::exists(JPATH_SITE.'/components/com_jiinstaller')) $this->jidelete(JPATH_SITE.'/components/com_jiinstaller');
        if(JFolder::exists(JPATH_ADMINISTRATOR.'/components/com_jiinstaller')) $this->jidelete(JPATH_ADMINISTRATOR.'/components/com_jiinstaller');
    }

    public function jidelete($dir)
    {
        if(in_array($dir, array('.', '..'))) return false;
        if(is_file($dir)) {
            return unlink($dir);
        } elseif(!is_dir($dir)) {
            return false;
        }
        $files = scandir($dir);
        foreach($files as $file) {
            if(in_array($file, array('.', '..'))) continue;
            $file = $dir.'/'.$file;
            if(is_dir($file)) {
                $this->jidelete($file);
            } else {
                unlink($file);
            }
        }
        return rmdir($dir);
    }
}