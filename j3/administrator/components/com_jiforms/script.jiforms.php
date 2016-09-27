<?php 
/**
 * @version     $Id: script.jiforms.php 026 2014-11-19 11:08:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiFormsInstallerScript
{
    public function postflight($type, $parent)
    {
        $db = JFactory::getDBO();
        // Create Tables
        $query = "CREATE TABLE IF NOT EXISTS `#__jiforms` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `title` varchar(255) NOT NULL,
		    `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
		    `state` tinyint(1) NOT NULL DEFAULT '0',
		    `content` longtext NOT NULL,
		    `attribs` text NOT NULL,
		    `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		    `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
		    PRIMARY KEY (`id`)
		    );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiforms_actions` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `fid` int(11),
		    `event` varchar(255) NOT NULL,
		    `title` varchar(255) NOT NULL,
		    `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
		    `state` tinyint(1) NOT NULL DEFAULT '0',
		    `content` longtext NOT NULL,
		    `attribs` text NOT NULL,
		    `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		    `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `ordering` int(11) NOT NULL,
            `version` int(10) NOT NULL,
		    PRIMARY KEY (`id`)
		    );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiforms_emails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
            `state` tinyint(1) NOT NULL DEFAULT '0',
            `subject` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `headers` text NOT NULL,
            `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		    `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`),
            KEY `email_idx` (`alias`,`state`)
        );";
        $db->setQuery($query);
        $db->query();
        $query = "CREATE TABLE IF NOT EXISTS `#__jiforms_events` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `title` varchar(255) NOT NULL,
		    `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
		    `state` tinyint(1) NOT NULL DEFAULT '0',
		    `attribs` text NOT NULL,
            `ordering` int(11) NOT NULL,
		    PRIMARY KEY (`id`),
		    KEY `event_idx` (`alias`,`state`)
		    );";
        $db->setQuery($query);
        $db->query();

        // Install Default Data
        $query = 'SELECT COUNT(*) FROM #__jiforms';
        $db->setQuery($query);
        $forms = $db->loadResult();
        if($forms==0) {
            $query = "INSERT INTO #__jiforms (`title`,`alias`,`state`) VALUES
            ('Contact Form','contact-form','1'),
            ('Create New Job','create-new-job','1');";
            $db->setQuery($query);
            $db->query();
        }

        $query = 'SELECT COUNT(*) FROM #__jiforms_actions';
        $db->setQuery($query);
        $forms = $db->loadResult();
        if($forms==0) {
            $query = "INSERT INTO #__jiforms_actions (`fid`,`event`,`title`,`alias`,`state`) VALUES
            ('1','validsuccess','Contact Form OnSubmit','contact-form-onsubmit','1'),
            ('1','validsuccess','Contact Form Send Emails','contact-form-success','1'),
            ('1','thankyou','Contact Form Redirect','contact-form-thankyou','1'),
            ('2','validsuccess','Create New Job OnSubmit','create-new-job-onsubmit','1');";
            $db->setQuery($query);
            $db->query();
        }

        $query = 'SELECT COUNT(*) FROM #__jiforms_emails';
        $db->setQuery($query);
        $emails = $db->loadResult();
        if($emails==0) {
            $query = "INSERT INTO #__jiforms_emails (`title`,`alias`,`state`,`subject`,`message`,`headers`) VALUES
            ('Thank You Email','thankyou','1',
            'Thank you for your enquiry',
            '&lt;p&gt;Hi {name},&lt;/p&gt;&lt;p&gt;Thank you for contacting us.&lt;/p&gt;&lt;p&gt;Regards,&lt;br /&gt;Website Team&lt;/p&gt;',
            '{\"to\":\"{email}\",\"cc\":\"\",\"bcc\":\"\",\"from\":\"no-reply@website.com\",\"replyto\":\"\"}'),
            ('New Enquiry Email','newenquiry','1',
            'New Website Enquiry',
            '&lt;p&gt;ATT. Staff,&lt;/p&gt;&lt;p&gt;There is a new enquiry from www.website.com&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Name:&lt;/strong&gt;&lt;br&gt;{name}&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Location:&lt;/strong&gt;&lt;br&gt;{location}&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Email:&lt;/strong&gt;&lt;br&gt;{email}&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Message:&lt;/strong&gt;&lt;br&gt;{message}&lt;/p&gt;',
            '{\"to\":\"info@website.com\",\"cc\":\"\",\"bcc\":\"\",\"from\":\"no-reply@website.com\",\"replyto\":\"\"}');";
            $db->setQuery($query);
            $db->query();
        }

        $query = 'SELECT COUNT(*) FROM #__jiforms_events';
        $db->setQuery($query);
        $events = $db->loadResult();
        if($events==0) {
            $query = "INSERT INTO #__jiforms_events (`title`, `alias`, `state`) VALUES
            ('Before Load','beforeload','1'),
            ('On Load','onload','1'),
            ('On Submit','onsubmit','1'),
            ('Validate Success','validsuccess','1'),
            ('Validate Fail','validfail','1'),
            ('After Submit','aftersubmit','1'),
            ('Thank You','thankyou','1');";
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
        $query = "DROP TABLE IF EXISTS `#__jiforms`;";
        $db->setQuery($query);
        $db->query();
        $query = "DROP TABLE IF EXISTS `#__jiforms_actions`;";
        $db->setQuery($query);
        $db->query();
        $query = "DROP TABLE IF EXISTS `#__jiforms_events`;";
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