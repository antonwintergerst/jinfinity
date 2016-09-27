<?php 
/**
 * @version     $Id: content.php 099 2013-10-25 15:31:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class ContentExporter extends JiExporter {
    /**
     * Override to handle selective content
     */
    public function process($bypass=true) {
        parent::process($bypass);

        if(isset($this->tables[$this->currenttable])) {
            $table = $this->tables[$this->currenttable];
            $dbtable = $table['name'];
            $primarykey = (isset($table['pkey']))? $table['pkey'] : null;

            if($this->start==0) {
                $this->setStatus(array('msg'=>'Starting '.$dbtable.' Export'));
            } else {
                $this->setStatus(array('msg'=>'Resuming '.$dbtable.' Export'));
            }
            if($this->params->get('selective', 0)==1) {
                // TODO: split selective process into passes
                $this->dbtable = $dbtable;
                $filter = $this->params->get('filter', array());
                if(count($filter)>0) {
                    // Selective Export
                    $this->setStatus(array('msg'=>'Processing Selective Export'));

                    // Add Columns
                    $columns = $this->getColumns();
                    $this->setStatus(array('msg'=>'Adding '.$this->dbtable.' columns to CSV'));
                    $this->writeToCSV($columns);

                    $rows = $this->selective($filter);
                    foreach($rows as $row) {
                        $this->writeToCSV($row);
                    }
                    $this->setStatus(array('msg'=>'Total rows found and included: '.count($rows)));
                    // Completed processor, execute complete callback
                    $this->setStatus(array('msg'=>'Export '.$this->dbtable.' complete'));

                    $data = $this->buildEndProcessorData();
                    call_user_func_array($this->complete, array($data));
                } else {
                    call_user_func_array($this->complete, array(null));
                }
            } else {
                // Complete Export
                $this->exportTable($dbtable, $primarykey);
            }
        } else {
            call_user_func_array($this->complete, array(null));
        }
    }

    /**
     * Selective Export as set by the content filter
     * @param $filter
     * @return array
     */
    function selective($filter) {
        require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/j25contentfilter.php');
        $this->helper = new ContentHelper();
        $this->helper->db = $this->db;
        $rows = array();
        ksort($filter);

        foreach($filter as $path=>$state) {
            $subpaths = explode('/', trim($path, '/'));
            $currentpath = end($subpaths);
            $level = count($subpaths);
            
            $items = array();
            // Check if item is a category
            $exists = $this->helper->is_category($currentpath, $level);

            if($exists!=false) {
                $response = $this->getArticlesRecursive($exists->id);
                if($response!=null) $items = $response;
            } else {
                // Check if item is a article
                $exists = $this->helper->is_article($currentpath);
                if($exists!=false) {
                    $response = $this->getArticle($exists);
                    if($response!=null) $items[] = $response;
                }
            }
            foreach($items as $item) {
                if($state=='include') {
                    $this->willExportTableRow($item);
                    $rows[$item->id] = $item;
                } elseif($state=='exclude') {
                    unset($rows[$item->id]);
                }
            }
        }
        return $rows;
    }

    /**
     * Returns an array of articles found recursively within a category
     * @param $catid
     * @return array
     */
    function getArticlesRecursive($catid) {
        // TODO: resolve potential memory crash
        $items = array();
        $db = JFactory::getDBO();
        // Check if category has subcategories
        $query = 'SELECT `id`, `title`, `alias`, `parent_id` FROM #__categories';
        $query.= ' WHERE `extension`="com_content"';
        $query.= ' AND `parent_id`='.(int)$catid;
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        if($categories!=null) {
            foreach($categories as $category) {
                $response = $this->getArticlesRecursive($category->id);
                foreach($response as $item) {
                    $items[] = $item;
                }
            }
        }
        // Get articles
        $query = 'SELECT * FROM #__content';
        $query.= ' WHERE `catid`='.(int)$catid;
        $db->setQuery($query);
        $articles = $db->loadObjectList();
        if($articles!=null) {
            foreach($articles as $item) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param int $aid
     * @return object|null
     */
    function getArticle($aid) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__content';
        $query.= ' WHERE `id`='.(int)$aid;
        $db->setQuery($query);
        $item = $db->loadObject();
        return $item;
    }
}