<?php 
/**
 * @version     $Id: menutypes.php 079 2013-08-15 14:10:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class MenuTypesExporter extends JiExporter {
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
     * Selective Export as set by the content filter
     * @param string $filter
     * @return array
     */
    function selective($filter) {
        require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/j15menufilter.php');
        $this->helper = new MenuHelper();
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
                $response = $this->getMenu($exists->id);
                if($response!=null) $items[] = $response;
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
     * @param $id
     * @return object|null
     */
    function getMenu($id) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__menu_types';
        $query.= ' WHERE `id`='.(int)$id;
        $db->setQuery($query);
        $item = $db->loadObject();
        return $item;
    }
}