<?php
/**
 * @version     $Id: extensions.php 035 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');

class JiExtensionManagerModelExtensions extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	An optional associative array of configuration settings.
     * @see		JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'id',
                'title', 'title',
                'alias', 'alias',
                'publisher', 'publisher',
                'downloadhits', 'downloadhits',
                'updatehits', 'updatehits',
                'state', 'state',
                'publish_up', 'publish_up',
                'publish_down', 'publish_down',
                'ordering', 'ordering'
            );
        }

        parent::__construct($config);
    }
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }
        $ids = $this->getUserStateFromRequest($this->context.'.filter.ids', 'filter_ids');
        $this->setState('filter.ids', $ids);

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $context = $this->getUserStateFromRequest($this->context.'.filter.context', 'filter_context', '');
        $this->setState('filter.context', $context);

        // List state information.
        parent::populateState('title', 'asc');
    }
    public function refreshServer() {
        $jinput = JFactory::getApplication()->input;
        $url = $jinput->get('url', null, 'raw');
        $dlkey = $jinput->get('dlkey', null, 'raw');
        $jversion = $jinput->get('jversion', '*');
        $postfields = array(
            'dlkey'=>$dlkey,
            'jversion'=>$jversion
        );

        // url cannot be blank
        if($url==null) die;

        // only allow url calls from administrator
        if(!JFactory::getApplication()->isAdmin()) die;

        // only allow when logged in
        $user = JFactory::getUser();
        if(!$user->id) die;

        if(substr($url, 0, 4)!= 'http') $url = 'http://' . $url;

        $html = '';
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $html = $this->curl($url, $postfields);
        } else {
            $file = @fopen($url, 'r');
            if ($file) {
                $html = array();
                while (!feof($file)) {
                    $html[] = fgets($file, 1024);
                }
                $html = implode('', $html);
            }
        }

        return $html;
    }
    protected function curl($url, $postfields=array())
    {
        $timeout = JFactory::getApplication()->input->getInt('timeout', 3);
        $timeout = min(array(30, max(array(3, $timeout))));

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Jinfinity');
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, count($postfields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));

        //follow on location problems
        if (ini_get('open_basedir') == '' && ini_get('safe_mode') != '1' && ini_get('safe_mode') != 'On') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $html = curl_exec($ch);
        } else {
            $html = $this->curl_redir_exec($ch);
        }
        curl_close($ch);
        return $html;
    }

    protected function curl_redir_exec($ch)
    {
        static $curl_loops = 0;
        static $curl_max_loops = 20;

        if ($curl_loops++ >= $curl_max_loops) {
            $curl_loops = 0;
            return false;
        }

        curl_setopt($ch, CURLOPT_HEADER, true);
        $data = curl_exec($ch);

        list($header, $data) = explode("\n\n", str_replace("\r", '', $data), 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url) {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            if (!$url['scheme']) {
                $url['scheme'] = $last_url['scheme'];
            }
            if (!$url['host']) {
                $url['host'] = $last_url['host'];
            }
            if (!$url['path']) {
                $url['path'] = $last_url['path'];
            }
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
            curl_setopt($ch, CURLOPT_URL, $new_url);
            return $this->curl_redir_exec($ch);
        } else {
            $curl_loops = 0;
            return $data;
        }
    }
    public function getItems()
    {
        $rows = $this->getItemsByXML();

        if (empty($rows)) {
            return array();
        }

        $ids = $this->getState('filter.ids');

        $items = array();

        foreach ($rows as $row) {
            $item = $this->initItem();
            if (isset($row['title'])) {
                $item->title = $row['title'];
                $item->id = isset($row['id']) ? $row['id'] : preg_replace('#[^a-z\-]#', '', str_replace('?', '-', strtolower($item->title)));
                if (!empty($ids) && !in_array($item->id, $ids)) {
                    continue;
                }
                $item->alias = isset($row['alias']) ? $row['alias'] : strtolower($item->title);
                $item->element = isset($row['element']) ? $row['element'] : $item->id;
                $item->types = array();

                $types = array();
                if (isset($row['type'])) {
                    $types = explode(',', $row['type']);
                }
                $this->checkInstalled($item, $types);

                $items[$item->id] = $item;
            }
        }
        return $items;
    }
    /**
     * Return an object list with items from the xml file
     */
    function getItemsByXML()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            //return $this->cache[$store];
        }

        $items = array();

        jimport('joomla.filesystem.file');

        if (!JFile::exists(JPATH_COMPONENT . '/extensions.xml')) {
            return $items;
        }

        $file = JFile::read(JPATH_COMPONENT . '/extensions.xml');

        if (!$file) {
            return $items;
        }

        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $file, $fields);
        xml_parser_free($xml_parser);

        foreach ($fields as $field) {
            if ($field['tag'] != 'EXTENSION'
                || !isset($field['attributes'])
            ) {
                continue;
            }

            $item = array();
            foreach ($field['attributes'] as $val => $key) {
                $item[strtolower($val)] = $key;
            }
            $items[] = $item;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }

    /**
     * Return an empty extension item object
     */
    function initItem()
    {
        $item = new stdClass;
        $item->id = 0;
        $item->name = '';
        $item->alias = '';
        $item->element = '';
        $item->installed = '';
        $item->version = '';
        $item->pro = 0;
        $item->old = 1;
        $item->haspro = 1;
        $item->types = array();
        $item->missing = array();

        return $item;
    }

    /**
     * Return an empty type object
     */
    function initType()
    {
        $item = new stdClass;
        $item->id = 0;
        $item->type = '';
        $item->link = '';

        return $item;
    }

    /**
     * Return an empty extension item
     */
    function checkInstalled(&$item, $types = array())
    {
        jimport('joomla.filesystem.file');

        $file = '';

        foreach ($types as $type) {
            $el = $this->initType();
            $el->type = $type;
            list($xml, $client_id) = $this->getXML($type, $item->element);

            $el->client_id = $client_id;
            $el->link = $this->getURL($type, $item->element, $item->title, $client_id);
            if (!$xml) {
                $item->missing[] = $type;
            } else {
                $el->id = $this->getID($type, $item->element);
                if (!$file) {
                    $file = $xml;
                }
            }
            $item->types[$type] = $el;
        }

        if (!$file) {
            $item->missing = array();
        } else {
            $xml = JApplicationHelper::parseXMLInstallFile($file);
            if ($xml && isset($xml['version'])) {
                $item->installed = 1;
                $item->version = $xml['version'];
                if(!$item->version) $item->version = '0.0.0';

                if(strstr($item->version, 'PRO')!==false) {
                    $item->pro = 1;
                } elseif(strstr($item->version, 'FREE')!==false) {
                    $item->licence = 'free';
                }
                $item->version = str_replace(array('FREE', 'PRO'), '', $item->version);
            }
        }
    }

    /**
     * Get the extension url
     */
    function getXML($type, $element)
    {
        $client_id = 1;
        $xml = '';
        switch ($type) {
            case 'com':
                if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $element . '.xml')) {
                    $xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_SITE . '/components/com_' . $element . '/' . $element . '.xml')) {
                    $client_id = 0;
                    $xml = JPATH_SITE . '/components/com_' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml')) {
                    $xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml';
                } else if (JFile::exists(JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml')) {
                    $client_id = 0;
                    $xml = JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml';
                }
                break;
            case 'plg_content':
                if (JFile::exists(JPATH_PLUGINS . '/content/' . $element . '/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/content/' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_PLUGINS . '/content/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/content/' . $element . '.xml';
                }
                break;
            case 'plg_system':
                if (JFile::exists(JPATH_PLUGINS . '/system/' . $element . '/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/system/' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_PLUGINS . '/system/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/system/' . $element . '.xml';
                }
                break;
            case 'plg_editors-xtd':
                if (JFile::exists(JPATH_PLUGINS . '/editors-xtd/' . $element . '/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/editors-xtd/' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_PLUGINS . '/editors-xtd/' . $element . '.xml')) {
                    $xml = JPATH_PLUGINS . '/editors-xtd/' . $element . '.xml';
                }
                break;
            case 'mod':
                if (JFile::exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml')) {
                    $xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml')) {
                    $client_id = 0;
                    $xml = JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml';
                } else if (JFile::exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml')) {
                    $xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
                } else if (JFile::exists(JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml')) {
                    $client_id = 0;
                    $xml = JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
                }
                break;
        }
        return array($xml, $client_id);
    }

    /**
     * Get the extension url
     */
    function getURL($type, $element, $name, $client_id = 1)
    {
        list($type, $folder) = explode('_', $type . '_');

        $link = '';
        switch ($type) {
            case 'com';
                $link = 'option=com_' . $element;
                break;
            case 'mod';
                $link = 'option=com_modules&filter_client_id=' . $client_id . '&filter_module=mod_' . $element . '&filter_search=';
                break;
            case 'plg';
                $name = JText::_($name);
                $name = preg_replace('#^(.*?)\?.*$#', '\1', $name);
                $link = 'option=com_plugins&filter_folder=' . $folder . '&filter_search=' . $name;
                break;
        }

        return $link;
    }

    /**
     * Get the extension id
     */
    function getID($type, $element)
    {
        $db = JFactory::getDBO();

        list($type, $folder) = explode('_', $type . '_');

        $query = $db->getQuery(true);
        $query->from('#__extensions as e')
            ->select('e.extension_id');

        switch ($type) {
            case 'com';
                $query->where('e.type = ' . $db->quote('component'))
                    ->where('e.element = ' . $db->quote('com_' . $element));
                break;
            case 'mod';
                $query->where('e.type = ' . $db->quote('module'))
                    ->where('e.element = ' . $db->quote('mod_' . $element));
                break;
            case 'plg';
                $query->where('e.type = ' . $db->quote('plugin'))
                    ->where('e.element = ' . $db->quote($element))
                    ->where('e.folder = ' . $db->quote($folder));
                break;
        }

        $db->setQuery($query);
        return $db->loadResult();
    }
}