<?php 
/**
 * @version     $Id: categories.php 079 2014-07-24 13:47:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
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
    function process() {
        parent::process(true);

        if(!isset($this->catidmap)) {
            $this->catidmap = array();
        }
        if(!isset($this->catcount)) {
            // Find current category id
            $db = JFactory::getDBO();
            $query = 'SELECT MAX(id) FROM #__categories';
            $db->setQuery($query);
            $result = $db->loadResult();
            $this->catcount = (int) $result;
        }

        if(isset($this->tables[$this->currenttable])) {
            $table = $this->tables[$this->currenttable];
            $dbtable = $table['name'];
            $primarykey = (isset($table['pkey']))? $table['pkey'] : null;

            if($this->params->get('selective', 0)==1) {
                // TODO: split selective process into passes
                if($this->start==0) {
                    $this->setStatus(array('msg'=>'Starting '.$dbtable.' Export'));
                } else {
                    $this->setStatus(array('msg'=>'Resuming '.$dbtable.' Export'));
                }
                $this->dbtable = $dbtable;
                $filter = $this->params->get('filter', array());
                if(count($filter)>0) {
                    // Selective Export
                    $this->setStatus(array('msg'=>'Processing Selective Export'));

                    // Add Columns
                    $columns = $this->getColumns();
                    $this->setStatus(array('msg'=>'Adding '.$this->dbtable.' columns to CSV'));
                    $this->writeToCSV($columns, true);

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
     * Category Override - Query to select database rows
     * @return string
     */
    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable;
        $query.= ' WHERE `published`!=-2'; // ignore trashed items
        if($this->dbtable=='categories') $query.= ' AND `section` REGEXP "^[0-9]+$"'; // must belong to com_content component
        $query.= ' ORDER BY `ordering` ASC';
        return $query;
    }

    /**
     * Override - static columns from Joomla 3.0
     * @return array
     */
    public function getColumns() {
        $columns = array('id', 'asset_id', 'parent_id', 'lft', 'rgt', 'level', 'path', 'extension', 'title', 'alias', 'note', 'description', 'published', 'checked_out', 'checked_out_time', 'access', 'params', 'metadesc', 'metakey', 'metadata', 'created_user_id', 'created_time', 'modified_user_id', 'modified_time', 'hits', 'language', 'version');
        return $columns;
    }

    /**
     * Override - always use categories.csv as output file and only write header if this is the first table pass (sections)
     * @param $data
     * @param $filename
     * @param bool $isheader
     * @return bool|void
     */
    public function willWriteToCSV(&$data, &$filename, $isheader) {
        if($this->dbtable=='categories' && $isheader) return false;
        $filename = 'categories.csv';
    }

    /**
     * Override - only add categories.csv to the archive
     * @return array
     */
    public function buildEndProcessorData() {
        $data = parent::buildEndProcessorData();
        $data['sysfiles'] = array('categories'=>'categories.csv');
        return $data;
    }

    /**
     * Override - map Joomla 1.5 columns to Joomla 3.0
     * @param $item
     */
    public function willExportTableRow(&$item) {
        parent::willExportTableRow($item);
        if(!isset($item->section)) {
            // Sections as categories
            $this->catcount++;
            $catid = $this->catcount;

            $row = new stdClass();
            $row->id = $catid;
            $row->asset_id = '';
            $row->parent_id = ''; // let the importer decide the root category
            $row->lft = '';
            $row->rgt = '';
            $row->level = 1;
            $row->path = $item->alias;
            $row->extension = 'com_content';
            $row->title = $item->title;
            $row->alias = $item->alias;
            $row->note = '';
            $row->description = $item->description;
            $row->published = $item->published;
            $row->checked_out = $item->checked_out;
            $row->checked_out_time = $item->checked_out_time;
            if(!isset($item->access) || (isset($item->access) && $item->access==0)) $item->access = 1;
            $row->access = $item->access;
            $row->params = $item->params;
            $row->metadesc = '';
            $row->metakey = '';
            $row->metadata = '';
            $row->created_user_id = '';
            $row->created_time = '';
            $row->modified_user_id = '';
            $row->modified_time = '';
            $row->hits = '';
            $row->language = '';
            $row->version = '';

            // Map old id to new id for later
            $this->catidmap[$item->id] = $row->id;
        } else {
            // Categories
            $row = new stdClass();
            $row->id = $item->id;
            $row->asset_id = '';
            $newParentId = (isset($this->catidmap[$item->section]))? $this->catidmap[$item->section] : 1;
            if($item->section!=$newParentId) $this->setStatus(array('msg'=>'Setting parent_id from: '.$item->section.', to: '.$newParentId.' for ID #'.$item->id));
            $row->parent_id = $newParentId;
            $row->lft = '';
            $row->rgt = '';
            $row->level = 2;
            $row->path = $item->alias;
            $row->extension = 'com_content';
            $row->title = $item->title;
            $row->alias = $item->alias;
            $row->note = '';
            $row->description = $item->description;
            $row->published = $item->published;
            $row->checked_out = $item->checked_out;
            $row->checked_out_time = $item->checked_out_time;
            if(!isset($item->access) || (isset($item->access) && $item->access==0)) $item->access = 1;
            $row->access = $item->access;
            $row->params = $item->params;
            $row->metadesc = '';
            $row->metakey = '';
            $row->metadata = '';
            $row->created_user_id = '';
            $row->created_time = '';
            $row->modified_user_id = '';
            $row->modified_time = '';
            $row->hits = '';
            $row->language = '';
            $row->version = '';
        }
        $item = $row;
    }

    /**
     * Selective export as set by the content filter
     * @param $filter
     * @return array
     */
    function selective($filter) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'j15contentfilter.php');
        $this->helper = new ContentHelper();
        $rows = array();
        ksort($filter);
        foreach($filter as $path=>$state) {
            $subpaths = explode('/', trim($path, '/'));
            $currentpath = end($subpaths);
            $level = count($subpaths);
            $items = array();

            if($level==1) {
                // Check if item is a section
                $exists = $this->helper->is_section($currentpath);
                if($exists!=null) {
                    // Add this section
                    $response = $this->getSection($exists);
                    if($response!=null) $items[] = $response;
                    // Add subcategories
                    $response = $this->getCategories($exists);
                    if($response!=null) {
                        foreach($response as $item) {
                            $items[] = $item;
                        }
                    }
                }
            } else {
                // Check if item is a category
                $exists = $this->helper->is_category($currentpath);
                if($exists!=null) {
                    // Add this category
                    $response = $this->getCategory($exists);
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
     * Method to get a section
     * @param $sectionid
     * @return object|null
     */
    function getSection($sectionid) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__sections';
        $query.= ' WHERE `id`='.(int)$sectionid;
        $db->setQuery($query);
        $item = $db->loadObject();
        return $item;
    }

    /**
     * Method to get an array of categories found within a section
     * @param $sectionid
     * @return array|null
     */
    function getCategories($sectionid) {
        // TODO: resolve potential memory crash
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__categories';
        $query.= ' WHERE `section`='.(int)$sectionid;
        $db->setQuery($query);
        $categories = $db->loadObjectList();
        return $categories;
    }

    /**
     * Method to get a category
     * @param $catid
     * @return object|null
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