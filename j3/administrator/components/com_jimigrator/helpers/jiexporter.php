<?php
/**
 * @version     $Id: jiexporter.php 087 2014-12-16 12:21:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiprocessor.php');

/**
 * Class JiExporter
 */
class JiExporter extends JiProcessor {

    public $output;

    /**
     * JiExporter implementation of the process entry point
     * Executes complete callback if $this->currenttable index is outside $this->tables scope
     * @uses $this->complete
     */
    public function process($bypass=false) {
        parent::process();

        if(!$bypass) $this->exportTable();
    }

    /**
     * Entry point to start exporting an entire database table
     * @param $dbtable
     * @return bool|null
     */
    public function exportTable($dbtable=null) {
        if(isset($this->tables[$this->currenttable])) {
            $table = $this->tables[$this->currenttable];
            $dbtable = $table['name'];
            $primarykey = (isset($table['pkey']))? $table['pkey'] : null;

            if($this->start==0) {
                $this->setStatus(array('msg'=>'Starting '.$dbtable.' Export'));
            } else {
                $this->setStatus(array('msg'=>'Resuming '.$dbtable.' Export'));
            }

            $this->dbtable = $dbtable;

            // Check if table exists
            $db = JFactory::getDBO();
            $tablelist = $db->getTableList();
            $abstable = $db->getPrefix().$this->dbtable;
            if(!in_array($abstable, $tablelist)) {
                $this->setStatus(array('msg'=>$this->dbtable.' table not found!'));
                $this->didCompletePass();
                return false;
            }

            $this->writeColumnsToCSV();

            $this->total = null;

            // Add rows
            $this->exportTableProcessor();
        } else {
            $data = $this->buildEndProcessorData();
            call_user_func_array($this->complete, array($data));
        }
    }

    public function writeColumnsToCSV() {
        if($this->start==0) {
            $columns = $this->getColumns();
            $this->setStatus(array('msg'=>'Adding '.$this->dbtable.' columns to CSV'));
            $this->writeToCSV($columns, true);
        }
    }

    /**
     * Method to get database table columns
     * @return array
     */
    public function getColumns() {
        $columns = array();

        // Depreciated (Joomla 1.5 does not implement this method in the DBO class)
        /*$db = JFactory::getDBO();
        $columndata = $db->getTableColumns($db->getPrefix().$this->dbtable);
        $columns = array_keys($columndata);*/

        $db = JFactory::getDBO();
        $query = 'SHOW COLUMNS FROM '.$db->getPrefix().$this->dbtable;
        $db->setQuery($query);
        $columndata = $db->loadObjectList();
        if($columndata!=null) {
            foreach($columndata as $column) {
                $columns[] = $column->Field;
            }
        }
        return $columns;
    }

    /**
     * Executes before exporting a database table
     */
    public function willBeginPass() {
        if($this->total==null) {
            $db = JFactory::getDBO();
            $query = $this->buildExportQuery();
            $db->setQuery($query, 0, 1);
            $items = $db->loadObjectList();

            if($items!=null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
                /* >>> FREE >>> */if($this->total>100) $this->total = 100;/* <<< FREE <<< */
            } else {
                $this->total = 0;
            }
        }
        parent::willBeginPass();
    }

    /**
     * Query to select database rows
     * @return string
     */
    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable;
        return $query;
    }

    /**
     * Exports all data returned from this pass
     * @uses $this->willBeginPass()
     * @uses $this->writeToCSV()
     * @uses $this->didCompletePass()
     */
    public function exportTableProcessor() {
        $this->willBeginPass();

        if($this->total>0) {
            $db = JFactory::getDBO();
            $query = $this->buildExportQuery();
            $db->setQuery($query, $this->start, $this->limit);
            $items = $db->loadObjectList();

            // Check there are no errors
            if($db->getErrorMsg()) $this->setStatus(array('msg'=>$db->getErrorMsg()));

            if($items!=null) {
                foreach($items as $item) {
                    if($this->shouldExportTableRow($item)) {
                        $this->willExportTableRow($item);
                        $this->writeToCSV($item);
                    }
                }
            }
            // Free up memory
            unset($items, $db, $item, $query);
            if(function_exists('gc_collect_cycles')) gc_collect_cycles();
        }

        $this->didCompletePass();
    }

    /**
     * Determines whether an item will be exported
     * @param object $item
     * @return bool
     */
    public function shouldExportTableRow(&$item) {
        return true;
    }

    /**
     * Executes before exporting a database table row
     * @param object $item
     */
    public function willExportTableRow(&$item) {
        $this->totalprocessed++;
    }

    /**
     * Increments to the next pass, dbtable, or finishes the loop with the complete callback
     * @uses $this->pass
     * @uses $this->complete
     */
    public function didCompletePass() {
        $this->currentpass++;
        if($this->end<$this->total) {
            // Process the next batch
            $this->start = $this->start + $this->limit;
            if($this->currentpass-$this->startpass >= 5) {
                // Setup data for next pass
                $data = $this->buildEndPassData();
                // Defer pass to callback for next script cycle
                call_user_func_array($this->pass, array($data));
            } else {
                // Do the next pass now, but wait a bit before starting
                usleep(250000);
                $this->exportTableProcessor();
            }
        } else {
            $this->currenttable++;
            if($this->currenttable < count($this->tables)) {
                if($this->total!=null) {
                    // Defer dbtable pass to callback for next script cycle
                    $data = $this->buildEndTableData();
                    call_user_func_array($this->pass, array($data));
                } else {
                    // Do the next table now, but wait a bit before starting
                    usleep(250000);
                    $this->exportTable();
                }
            } else {
                // Completed processor, execute complete callback
                $this->setStatus(array('msg'=>'Export '.$this->dbtable.' complete'));

                $data = $this->buildEndProcessorData();
                call_user_func_array($this->complete, array($data));
            }
        }
    }

    /**
     * Method to get output filename
     * @return string
     */
    public function getOutputFilename() {
        $filename = $this->dbtable.'.csv';
        return $filename;
    }

    /**
     * Method to build the data response sent to the process handler when completing a pass
     * @return array
     */
    public function buildEndPassData() {
        $data = array(
            'currenttable'=>$this->currenttable,
            'currentpass'=>$this->currentpass,
            'start'=>$this->start,
            'totalprogress'=>$this->totalprogress,
            'passprogress'=>$this->passprogress
        );
        // Global values
        $globalvars = $this->get('globalvars');
        $globalvalues = array();
        foreach($globalvars as $key) {
            if(isset($this->{$key})) $globalvalues[$key] = $this->{$key};
        }
        if(count($globalvalues)>0) $data['globalvalues'] = $globalvalues;
        return $data;
    }

    /**
     * @return array
     */
    public function buildEndTableData() {
        $sysfiles = (array) $this->data->get('sysfiles', array());
        $sysfiles[$this->dbtable] = $this->getOutputFilename();
        $data = array(
            'currenttable'=>$this->currenttable,
            'currentpass'=>0,
            'start'=>0,
            'sysfiles'=>$sysfiles,
            'totalprogress'=>$this->totalprogress,
            'passprogress'=>100
        );
        // Global values
        $globalvars = $this->get('globalvars');
        $globalvalues = array();
        foreach($globalvars as $key) {
            if(isset($this->{$key})) $globalvalues[$key] = $this->{$key};
        }
        if(count($globalvalues)>0) $data['globalvalues'] = $globalvalues;
        return $data;
    }

    /**
     * Method to build the data response sent to the process handler when completing a processor
     * @return array
     */
    public function buildEndProcessorData() {
        $sysfiles = (array) $this->data->get('sysfiles', array());
        if($this->dbtable!=null) $sysfiles[$this->dbtable] = $this->getOutputFilename();

        $data = array(
            'sysfiles'=>$sysfiles,
            'totalprogress'=>$this->totalprogress,
            'passprogress'=>$this->passprogress
        );
        // Global values
        $globalvars = $this->get('globalvars');
        $globalvalues = array();
        foreach($globalvars as $key) {
            if(isset($this->{$key})) $globalvalues[$key] = $this->{$key};
        }
        if(count($globalvalues)>0) $data['globalvalues'] = $globalvalues;
        return $data;
    }

    /**
     * @param $data
     * @param $filename
     * @param bool $isheader
     * @return bool|void
     */
    public function willWriteToCSV(&$data, &$filename, $isheader=false) {
        return true;
    }

    /**
     * Saves data as CSV to the output directory
     * @uses $this->output
     * @uses $this->dbtable
     * @param mixed $data
     * @param bool $isheader
     */
    public function writeToCSV($data, $isheader=false) {
        $filename = $this->getOutputFilename();
        $response = $this->willWriteToCSV($data, $filename, $isheader);
        if($response!==false && $data!=null) {
            // Find total
            $i = 0;
            foreach($data as $tmp) {
                $i++;
            }
            $total = $i;

            // Build line
            $csv = '';
            $i = 0;
            foreach($data as $cell) {
                // Escape Double Quotes
                $cell = str_replace('"', '""', $cell);
                // Escape New Lines
                $cell = str_replace("\n", '""\n""', $cell);
                // Escape Carriage Returns
                $cell = str_replace("\r", '""\r""', $cell);
                $cell = str_replace("\r\n", '""\r\n""', $cell);
                $csv.= '"'.$cell.'"';
                $i++;
                if($i<$total) $csv.= ',';
            }
            $csv.= "\r\n";
            file_put_contents($this->output.'/'.$filename, $csv, FILE_APPEND);
        }
    }
}