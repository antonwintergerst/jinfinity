<?php 
/**
 * @version     $Id: content.php 078 2014-10-26 11:22:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class ContentExporter extends JiExporter {
    function process() {
        parent::process(true);

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
        $query.= ' WHERE `state`!=-2'; // ignore trashed items
        $query.= ' ORDER BY `ordering` ASC';
        return $query;
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
                $items = $this->getSectionChildren($currentpath);
            } elseif($level==2) {
                $items = $this->getCategoryChildren($currentpath);
            } elseif($level==3) {
                $items = $this->getArticle($currentpath);
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
     * Method to get section children
     * @param $path
     * @return array
     */
    function getSectionChildren($path) {
        $items = array();
        
        $exists = $this->helper->is_section($path);
        if($exists!=null) {
            $db = JFactory::getDBO();
            $query = 'SELECT a.* FROM #__content AS a';
            $query.= ' LEFT JOIN #__sections AS s ON (s.id=a.sectionid)';
            $query.= ' WHERE s.id='.(int)$exists;
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if($result!=null) $items = $result;
        }
        
        return $items;
    }

    /**
     * Method to get category children
     * @param $path
     * @return array
     */
    function getCategoryChildren($path) {
        $items = array();
        
        $exists = $this->helper->is_category($path);
        if($exists!=null) {
            $db = JFactory::getDBO();
            $query = 'SELECT a.* FROM #__content AS a';
            $query.= ' LEFT JOIN #__categories AS c ON (c.id=a.catid)';
            $query.= ' WHERE c.id='.(int)$exists;
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if($result!=null) $items = $result;
        }
        
        return $items;
    }

    /**
     * Method to get a article
     * @param $path
     * @return array
     */
    function getArticle($path) {
        $items = array();
        
        $exists = $this->helper->is_article($path);
        if($exists!=null) {
            $db = JFactory::getDBO();
            $query = 'SELECT * FROM #__content';
            $query.= ' WHERE id='.(int)$exists;
            $db->setQuery($query);
            $result = $db->loadObjectList();
            if($result!=null) $items = $result;
        }
        
        return $items;
    }
}