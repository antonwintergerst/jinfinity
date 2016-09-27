<?php 
/**
 * @version     $Id: categories.php 102 2013-10-08 11:09:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class CategoriesExporter extends JiExporter {
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
     * Categories Override - Query to select database rows
     * @return string
     */
    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable;
        $query.= ' WHERE NOT (`alias`="root") AND NOT (`path`="uncategorised")';
        $query.= ' ORDER BY `lft` ASC';
        return $query;
    }

    /**
     * Selective Export as set by the content filter
     * @param $filter
     * @return array
     */
    function selective($filter) {
        require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/j25contentfilter.php');
        $this->helper = new ContentHelper();
        $rows = array();
        ksort($filter);
        foreach($filter as $path=>$state) {
            $subpaths = explode('/', trim($path, '/'));
            $currentpath = end($subpaths);
            $level = count($subpaths);
            
            $items = array();
            // Check if item is a category
            $exists = $this->helper->is_category($currentpath);
            if($exists!=null) {
                // Add this category
                $response = $this->getCategory($exists->id);
                if($response!=null) $items[] = $response;
                // Add subcategories
                $response = $this->getCategoriesRecursive($exists->id);
                if($response!=null) {
                    foreach($response as $item) {
                        $items[] = $item;
                    }
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
     * Returns an array of subcategories found recursively within a category
     * @param $catid
     * @return array
     */
    function getCategoriesRecursive($catid) {
        // TODO: resolve potential memory crash
        $items = array();
        $db = JFactory::getDBO();
        // Check if category has subcategories
        $query = 'SELECT * FROM #__categories';
        $query.= ' WHERE `extension`="com_content"';
        $query.= ' AND `parent_id`='.(int)$catid;
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        if($categories!=null) {
            foreach($categories as $category) {
                $items[] = $category;
                $response = $this->getCategoriesRecursive($category->id);
                foreach($response as $item) {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }

    /**
     * Returns a category row
     * @param $catid
     * @return mixed
     */
    function getCategory($catid) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__categories';
        $query.= ' WHERE `id`='.(int)$catid;
        $db->setQuery($query);
        $item = $db->loadObject();
        return $item;
    }
}