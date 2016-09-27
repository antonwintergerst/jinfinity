<?php 
/**
 * @version     $Id: menu.php 079 2014-07-24 13:47:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class MenuExporter extends JiExporter {
    /**
     * Override to handle selective content
     */
    function process() {
        parent::process(true);

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
     * Menu Override - Query to select database rows
     * @return string
     */
    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable;
        $query.= ' WHERE `published`!=-2'; // ignore trashed items
        $query.= ' ORDER BY `parent` ASC, `ordering` ASC';
        return $query;
    }

    /**
     * Override - hybrid columns for Joomla 3.0
     * @return array
     */
    public function getColumns() {
        $columns = parent::getColumns();
        if(!in_array('lft', $columns)) $columns[] = 'lft';
        if(!in_array('rgt', $columns)) $columns[] = 'rgt';
        if(!in_array('level', $columns)) $columns[] = 'level';
        return $columns;
    }

    /**
     * Override - map Joomla 1.5 columns to Joomla 3.0
     * @param $item
     */
    public function willExportTableRow(&$item) {
        parent::willExportTableRow($item);
        $item->lft = '';
        $item->rgt = '';
        $item->level = isset($item->sublevel)? $item->sublevel+1 : 1;
    }

    /*
     * Selective Export as set by the menu filter
     */
    function selective($filter) {
        require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/j15menufilter.php');
        $this->helper = new MenuHelper();
        $this->helper->db = $this->db;
        $rows = array();
        ksort($filter);
        foreach($filter as $path=>$state) {
            $subpaths = explode('/', trim($path, '/'));
            $currentpath = end($subpaths);
            $level = count($subpaths);
            
            $items = array();
            // Check if item is a menu
            $exists = $this->helper->is_menu($currentpath);
            if($exists!=null) {
                $response = $this->getMenuItemsRecursive(null, $exists->menutype);
                if($response!=null) $items = $response;
            } else {
                // Check if item is a menu item
                $exists = $this->helper->is_menuitem($currentpath);
                if($exists!=null) {
                    // Add this menu item
                    $response = $this->getMenuItem($exists->id);
                    if($response!=null) $items[] = $response;
                    // Add child menu items
                    $response = $this->getMenuItemsRecursive($exists->id);
                    if($response!=null) {
                        foreach($response as $item) {
                            $items[] = $item;
                        }
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
     * Returns an array of menu items found recursively within a menu item or menu
     * @param int|null $pid
     * @param string|null $menutype
     * @return array
     */
    function getMenuItemsRecursive($pid=null, $menutype=null) {
        // TODO: resolve potential memory crash
        $items = array();
        $db = JFactory::getDBO();
        // Check if menu item has child menu items
        $query = 'SELECT * FROM #__menu';
        if($pid!=null) {
            $query.= ' WHERE `parent`='.(int)$pid;
        } else {
            $query.= ' WHERE `menutype`='.$db->quote($menutype);
        }
        $db->setQuery($query);
        $menuitems = $db->loadObjectList();
        if($menuitems!=null) {
            foreach($menuitems as $menuitem) {
                $items[] = $menuitem;
                $response = $this->getMenuItemsRecursive($menuitem->id);
                foreach($response as $item) {
                    $items[] = $item;
                }
            }
        }
        return $items;
    }

    /**
     * @param int $id
     * @return object|null
     */
    function getMenuItem($id) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__menu';
        $query.= ' WHERE `id`='.(int)$id;
        $db->setQuery($query);
        $item = $db->loadObject();
        return $item;
    }
}