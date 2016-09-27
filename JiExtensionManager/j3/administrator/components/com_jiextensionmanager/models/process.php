<?php
/**
 * @version     $Id: process.php 033 2014-12-17 16:24:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modelitem');

class JiExtensionManagerModelProcess extends JModelItem
{
    /**
     * @var		string	The prefix to use with controller messages.
     */
    protected $text_prefix = 'JIEXTENSIONMANAGER';

    /**
     * Get the extensions data
     */
    public function getItems()
    {
        $ids = JFactory::getApplication()->input->get('ids', array(), 'array');
        $urls = JFactory::getApplication()->input->get('urls', array(), 'array');

        if (empty($ids) || empty($urls)) {
            return array();
        }

        $model = JModelLegacy::getInstance('Extensions', 'JiExtensionManagerModel', array('ignore_request'=>true));
        $model->setState('filter.ids', $ids);
        $items = $model->getItems();
        foreach ($ids as $i => $id) {
            if (isset($urls[$i])) {
                $items[$id]->url = $urls[$i];
            } else {
                unset($items[$id]);
            }
        }

        return $items;
    }

    /**
     * Download and install
     */
    function install($id, $url) {
        $params = JComponentHelper::getParams('com_jiextensionmanager');

        if(!is_string($url)) return JText::_('JIEXTENSIONMANAGER_ERROR_INVALID_URL');

        $config = JFactory::getConfig();

        $url = 'http://' . str_replace('http://', '', $url);
        $target = $config->get('tmp_path') . '/' . uniqid($id) . '.zip';
        $dlkey = $params->get('dlkey', '');

        $postfields = array('dlkey'=>$dlkey);

        jimport('joomla.filesystem.file');
        JFactory::getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);

        if(!(function_exists('curl_init') && !function_exists('curl_exec')) && !ini_get('allow_url_fopen')) {
            return JText::_('JIEXTENSIONMANAGER_ERROR_DOWNLOAD_FAILED');
        } elseif(function_exists('curl_init') && function_exists('curl_exec')) {
            /* USE CURL */
            $curl = curl_init();
            // Set Options
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Jinfinity');
            curl_setopt($curl, CURLOPT_REFERER, JURI::root());
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, count($postfields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postfields));

            $content = curl_exec($curl);
            curl_close($curl);
        } else {
            /* USE FOPEN */
            $handle = @fopen($url, 'r');
            if (!$handle) {
                return JText::_('JIEXTENSIONMANAGER_ERROR_SERVER_CONNECTION');
            }

            $content = '';
            while (!feof($handle)) {
                $content .= fread($handle, 4096);
                if ($content === false) {
                    return JText::_('JIEXTENSIONMANAGER_ERROR_DOWNLOAD_FAILED');
                }
            }
            fclose($handle);
        }
        if (empty($content)) {
            return JText::_('JIEXTENSIONMANAGER_ERROR_DOWNLOAD_FAILED');
        }
        // Write buffer to file
        JFile::write($target, $content);

        jimport('joomla.installer.installer');
        jimport('joomla.installer.helper');

        // Get an installer instance
        $installer = JInstaller::getInstance();

        // Unpack the package
        $package = JInstallerHelper::unpack($target);

        // Cleanup the install files
        if (!is_file($package['packagefile'])) {
            $config = JFactory::getConfig();
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }
        //JInstallerHelper::cleanupInstall($package['packagefile'], $package['packagefile']);

        // Install the package
        if (!$installer->install($package['dir'])) {
            // There was an error installing the package
            return JText::sprintf('JIEXTENSIONMANAGER_INSTALL_ERROR', JText::_('JIEXTENSIONMANAGER_TYPE_' . strtoupper($package['type'])));
        }

        return true;
    }
    function uninstall($id) {
        $model = JModelLegacy::getInstance('Extensions', 'JiExtensionManagerModel');
        $item = $model->getItems(array($id));
        $item = $item[$id];

        $ids = array();
        foreach ($item->types as $type) {
            if ($type->id) {
                $ids[] = $type->id;
            }
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/manage.php';
        $installer = JModelLegacy::getInstance('Manage', 'InstallerModel');
        $result = $installer->remove($ids);
        echo $result;
    }
}