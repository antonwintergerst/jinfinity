<?php 
/**
 * @version     $Id: j25tools.php 059 2013-08-14 15:16:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 2.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
jimport( 'joomla.application.component.view' );
class JiMigratorModelJ25Tools extends JiModel
{
    function __construct() {
        parent::__construct();
        // Set Processor Vars
        $this->start = 0;
        $this->limit = 100;
        $this->processed = 0;
        $this->total = null;
    }
    protected function __distruct()
    {
        $this->setup = null;
        $this->rootdir = null;
        $this->sectioncount = null;
        $this->catcount = null;
        $this->categories = null;
        $this->createnew = null;
        $this->start = null;
        $this->limit = null;
        $this->processed = null;
        $this->total = null;
    }
    function menuResolver() {
        $this->processed = 0;
        $this->start = 0;
        $this->total = null;
        $this->menuResolverProcessor();
    }
    function menuResolverProcessor() {
        // Rebuild Menu Item Params
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `link`, `component_id` FROM #__menu WHERE `type`="component"';
        $db->setQuery($query, $this->start, $this->limit);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            if($this->total==null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
            }
            foreach($items as $item) {
                $args = array();
                parse_str(parse_url($item->link, PHP_URL_QUERY), $args);

                if (isset($args['option'])) {
                    // Load the language file for the component.
                    $lang = JFactory::getLanguage();
                    $lang->load($args['option'], JPATH_ADMINISTRATOR, null, false, false)
                    ||  $lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], null, false, false)
                    ||  $lang->load($args['option'], JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
                    ||  $lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], $lang->getDefault(), false, false);

                    // Determine the component id.
                    $component = JComponentHelper::getComponent($args['option']);
                    if(isset($component->id)) {
                        $query = 'UPDATE #__menu SET `component_id`='.$db->quote($component->id).' WHERE `id`='.$item->id;
                        $db->setQuery($query);
                        $db->query();
                        echo 'Updated item #'.$item->id.' component_id to "'.$component->id.'" <br/>';
                    }
                }
            }
            $this->processed = $this->processed + count($items);
            echo date("Y-m-d H:i:s").' Processed items '.$this->start.' - '.($this->start+count($items)).' / '.$this->total.'<br/>';
            if($this->processed<$this->total) {
                // Process the next batch
                $this->start = $this->start + $this->limit;
                // Wait a bit before starting the next batch
                usleep(250000);
                $this->menuResolverProcessor();
            } else {
                echo 'All done!<br/>';
            }
        }
    }
    function menuParams() {
        $this->processed = 0;
        $this->start = 0;
        $this->total = null;
        $this->menuParamsProcessor();
    }
    function menuParamsProcessor() {
        // Rebuild Menu Item Params
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `params` FROM #__menu WHERE `params` IS NOT NULL';
        $db->setQuery($query, $this->start, $this->limit);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            if($this->total==null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
            }
            foreach($items as $item) {
                $oldparams = json_decode($item->params);
                if($oldparams==null && $item->params!=null) {
                    $newparams = array();
                    // Read params
                    $params = explode("\n", $item->params);
                    if(count($params>0)) {
                        foreach($params as $param) {
                            if(strlen(trim($param))>0) {
                                $parts = explode('=', $param);
                                // -1 is an incompatible Joomla 1.5 variable for menu_image
                                if($parts[0]=='menu_image' && isset($parts[1]) && $parts[1]=='-1') $parts[1] = "";
                                // Add parameter to newparams
                                $newparams[$parts[0]] = isset($parts[1])? $parts[1] : '';
                            }
                        }
                    }
                    // Update params in database
                    $query = 'UPDATE #__menu SET `params`='.$db->quote(json_encode($newparams)).' WHERE `id`='.$item->id;
                    $db->setQuery($query);
                    $db->query();
                    echo 'Updated item #'.$item->id.' params to "'.json_encode($newparams).'" <br/>';
                }
            }
            $this->processed = $this->processed + count($items);
            echo date("Y-m-d H:i:s").' Processed items '.$this->start.' - '.($this->start+count($items)).' / '.$this->total.'<br/>';
            if($this->processed<$this->total) {
                // Process the next batch
                $this->start = $this->start + $this->limit;
                // Wait a bit before starting the next batch
                usleep(250000);
                $this->menuParamsProcessor();
            } else {
                echo 'All done!<br/>';
            }
        }
    }
    function menuImages() {
        $this->processed = 0;
        $this->start = 0;
        $this->total = null;
        $this->menuImagesProcessor();
    }
    function menuImagesProcessor() {
        // Rebuild Menu Item Params
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `params` FROM #__menu WHERE `params` IS NOT NULL';
        $db->setQuery($query, $this->start, $this->limit);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            if($this->total==null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
            }
            foreach($items as $item) {
                $oldparams = json_decode($item->params);
                if($oldparams!=null && $item->params!=null) {
                    if(isset($oldparams->menu_image) && $oldparams->menu_image=='-1') {
                        $oldparams->menu_image = "";
                        // Update params in database
                        $query = 'UPDATE #__menu SET `params`='.$db->quote(json_encode($oldparams)).' WHERE `id`='.$item->id;
                        $db->setQuery($query);
                        $db->query();
                        echo 'Updated item #'.$item->id.' menu image to "'.$oldparams->menu_image.'" <br/>';
                    }
                }
            }
            $this->processed = $this->processed + count($items);
            echo date("Y-m-d H:i:s").' Processed items '.$this->start.' - '.($this->start+count($items)).' / '.$this->total.'<br/>';
            if($this->processed<$this->total) {
                // Process the next batch
                $this->start = $this->start + $this->limit;
                // Wait a bit before starting the next batch
                usleep(250000);
                $this->menuImagesProcessor();
            } else {
                echo 'All done!<br/>';
            }
        }
    }
    function moduleParams() {
        $this->processed = 0;
        $this->start = 0;
        $this->total = null;
        $this->moduleParamsProcessor();
    }
    function moduleParamsProcessor() {
        // Rebuild Menu Item Params
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `params` FROM #__modules WHERE `params` IS NOT NULL';
        $db->setQuery($query, $this->start, $this->limit);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            if($this->total==null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
            }
            foreach($items as $item) {
                $oldparams = json_decode($item->params);
                if($oldparams==null && $item->params!=null) {
                    $newparams = array();
                    // Read params
                    $params = explode("\n", $item->params);
                    if(count($params>0)) {
                        foreach($params as $param) {
                            if(strlen(trim($param))>0) {
                                $parts = explode('=', $param);
                                // Add parameter to newparams
                                $newparams[$parts[0]] = isset($parts[1])? $parts[1] : '';
                            }
                        }
                    }
                    // Update params in database
                    $query = 'UPDATE #__modules SET `params`='.$db->quote(json_encode($newparams)).' WHERE `id`='.$item->id;
                    $db->setQuery($query);
                    $db->query();
                    echo 'Updated item #'.$item->id.' params to "'.json_encode($newparams).'" <br/>';
                }
            }
            $this->processed = $this->processed + count($items);
            echo date("Y-m-d H:i:s").' Processed items '.$this->start.' - '.($this->start+count($items)).' / '.$this->total.'<br/>';
            if($this->processed<$this->total) {
                // Process the next batch
                $this->start = $this->start + $this->limit;
                // Wait a bit before starting the next batch
                usleep(250000);
                $this->moduleParamsProcessor();
            } else {
                echo 'All done!<br/>';
            }
        }
    }
    function contentAliases() {
        $preserve = 0;
        // Rebuild Article Aliases
        $db = JFactory::getDBO();
        $query = 'SELECT `id`, `title`, `alias` FROM #__content';
        if($preserve) $query.= ' WHERE `alias` IS NULL OR `alias`=""';
        $db->setQuery($query);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            foreach($items as $item) {
                $alias = JApplication::stringURLSafe($item->title, 'dash');
                $query = 'UPDATE #__content SET `alias`='.$db->quote($alias).' WHERE `id`='.$item->id;
                $db->setQuery($query);
                $db->query();
                echo 'Updated item #'.$item->id.' alias to "'.$alias.'" <br/>';
            }
        }
    }
	function contentAssetIds() {
		// Rebuild Article Asset IDs
        $db = JFactory::getDBO();
        $query = 'SELECT `id`, `alias`, `catid` FROM #__content';
        $db->setQuery($query);
        $items = $db->loadObjectList();
        
        if($items!=null) {
        	$table = JTable::getInstance('Content');
            foreach($items as $item) {
            	echo 'Rebuilding item #'.$item->id.' with alias "'.$item->alias.'"<br/>';
            	$exists = $table->load($item->id);
				// Ensure unique alias
				list($title, $alias) = $this->generateNewTitle($table->catid, $table->alias, $table->title);
				$table->alias = $alias;
				if($exists && !$table->store()) JError::raiseError(500, $table->getError() );
			}
		}
    }
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = JTable::getInstance('Content');
		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}
    function contentAttribs() {
        // Reset Article Attribs
        $db = JFactory::getDBO();
        $query = 'SELECT `id`, `attribs`, `access` FROM #__content';
        $db->setQuery($query);
        $items = $db->loadObjectList();
        
        if($items!=null) {
            foreach($items as $item) {
                if(json_decode($item->attribs)==null) $item->attribs = '';
                $query = 'UPDATE #__content SET';
                $query.= ' `attribs`='.$db->quote($item->attribs);
                if($item->access==0) $query.=', `access`=1';
                $query.=' WHERE `id`='.$item->id;
                $db->setQuery($query);
                $db->query();
                echo 'Updated item #'.$item->id.' attribs and access <br/>';
            }
        }
    }
    function contentAdopt() {
        // Function to adopt orphaned articles
        $db = JFactory::getDBO();
        $query = 'SELECT `id` FROM #__categories WHERE `extension`="com_content" AND `alias`="uncategorised"';
        $db->setQuery($query);
        $rootcat = $db->loadResult();
        // Get Orphans
        $query = 'SELECT `id` FROM #__content WHERE `catid`=0';
        $db->setQuery($query);
        $orphans = $db->loadObjectList();
        
        if($orphans!=null) {
            foreach($orphans as $item) {
                $query = 'UPDATE #__content SET `catid`='.$rootcat.' WHERE `id`='.$item->id;
                $db->setQuery($query);
                $db->query();
                echo 'Updated item #'.$item->id.' catid to "'.$rootcat.'" <br/>';
            }
        }
    }
    function clearAll() {
        // Clear categories
        $db = JFactory::getDBO();
        $query = 'TRUNCATE TABLE #__categories';
        $db->setQuery($query);
        $db->query();
        // Add default categories
        $query = "INSERT INTO `#__categories` VALUES
(1, 0, 0, 0, 13, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:07:09', 0, '0000-00-00 00:00:00', 0, '*'),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:26:37', 0, '0000-00-00 00:00:00', 0, '*'),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\",\"foobar\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:27:35', 0, '0000-00-00 00:00:00', 0, '*'),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:27:57', 0, '0000-00-00 00:00:00', 0, '*'),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:28:15', 0, '0000-00-00 00:00:00', 0, '*'),
(6, 31, 1, 9, 10, 1, 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:28:33', 0, '0000-00-00 00:00:00', 0, '*'),
(7, 32, 1, 11, 12, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:28:33', 0, '0000-00-00 00:00:00', 0, '*');";
        $db->setQuery($query);
        $db->query();
        // Clear articles
        $query = 'TRUNCATE TABLE #__content';
        $db->setQuery($query);
        $db->query();
        // Clear Menu Types
        $query = 'TRUNCATE TABLE #__menu_types';
        $db->setQuery($query);
        $db->query();
        // Remove Site Menu Items
        $query = 'DELETE FROM #__menu WHERE client_id=0 AND alias!="root"';
        $db->setQuery($query);
        $db->query();
        // Remove Site Modules
        $query = 'DELETE FROM #__modules WHERE client_id=0';
        $db->setQuery($query);
        $db->query();
    }
}