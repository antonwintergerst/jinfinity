<?php
/**
 * @version     $Id: processor.php 104 2013-12-04 14:08:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class JiProcessorHelper
 */
class JiProcessorHelper {
    function loadProcessor($classname, $includepath) {
        if(isset($classname) && isset($includepath)) {
            if(file_exists($includepath)) {
                require_once($includepath);
                if(class_exists($classname)) {
                    $processor = new $classname;
                    return $processor;
                }
            }
        }
        return;
    }
    /*
     * Paths to source processors from
     */
    public function setPaths($paths=null) {
        if(is_array($paths)) {
            foreach($paths as $path) {
                $this->paths[] = $path;
            }
        } elseif($paths!=null) {
            $this->paths[] = $paths;
        } else {
            // Default paths
            $this->paths[] = JPATH_SITE.'/administrator/components/com_jimigrator/jiprocessors';
        }
    }
    /*
     * Builds list of processors from XML files
     */
    public function getProcessors($order_pri='group', $order_sec='description') {
        // Set Paths
        if(!isset($this->paths)) $this->setPaths();
        // Build processors
        $processors = array();
        foreach($this->paths as $path) {
            $p = $this->getProcessorsFromPath($path);
            foreach($p as $processor) {
                $processors[] = $processor;
            }
        }
        // Set Order
        $order1 = array();
        $order2 = array();
        foreach($processors as $processor) {
            $order1[] = $processor->{$order_pri};
            $order2[] = $processor->{$order_sec};
        }
        array_multisort($order1, SORT_ASC, $order2, SORT_ASC, $processors);

        return $processors;
    }
    function getProcessorsFromPath($path) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $processors = array();
        if(is_dir($path)) {
            if($dh = opendir($path)) {
                while(($file=readdir($dh))!==false) {
                    $fileparts = explode('.', $file);
                    $filetype = end($fileparts);
                    $filename = $fileparts[0];
                    if($filetype=='xml') {
                        $xml = simplexml_load_file($path.'/'.$file);
                        $xmljversion = $xml['version'];
                        $xmlname = $xml->getName();
                        if(version_compare(JVERSION, $xmljversion, 'ge')) {
                            // Create processor Object
                            if(isset($xml->name[0])) {
                                $name = (string) $xml->name[0];
                                $name = strtolower($name);
                            } else {
                                $name = strtolower($filename);
                            }

                            $processor = $this->loadProcessor($name.$xmlname, $path.'/'.$filename.'.php');
                            if($processor!=null) {
                                $processor->xmlpath = $path.'/';
                                $processor->name = $name;
                                $processor->group = (isset($xml->group[0]))? (string) $xml->group[0] : 'thirdparty';
                                $processor->path = $path.'/'.$processor->name.'.php';
                                $processor->description = (string) $xml->description[0];
                                $processor->ready = true;
                                $processor->params = new JiMigratorObject();
                                // Get Files
                                $files = array();
                                if(isset($xml->files)) {
                                    foreach($xml->files->filename as $file) {
                                        $filename = (string) $file[0];
                                        $files[] = $filename;
                                        if(!file_exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'tmp'.DS.'input'.DS.$filename)) $processor->ready = false;
                                    }
                                }
                                // Get Fields
                                $fields = array();
                                if(isset($xml->fields)) {
                                    foreach($xml->fields->field as $xmlfield) {
                                        $field = new stdClass();
                                        // Get Attributes
                                        foreach($xmlfield->attributes() as $attrkey=>$attrvalue) {
                                            $field->{$attrkey} = (string) $attrvalue;
                                        }
                                        // Get Options
                                        $options = array();
                                        foreach($xmlfield->option as $xmloption) {
                                            $value = (string) $xmloption->attributes()->value;
                                            $options[$value] = (string) $xmloption[0];
                                        }
                                        $field->options = $options;
                                        $fields[] = $field;

                                        if(isset($field->name)) {
                                            // Set ID
                                            $field->id = $processor->name.$field->name;
                                            $field->name = $field->name;

                                            // Set Value
                                            require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'field.php');
                                            $fieldhelper = new JiMigratorFieldHelper();
                                            $fieldhelper->setPaths(JPATH_SITE.'/administrator/components/com_jimigrator/jifields');
                                            $JiField = $fieldhelper->loadType($field);
                                            $processor->params->set($field->name, $JiField->getValue());
                                        }
                                    }
                                }
                                $processor->fields = $fields;
                                // Get Tables
                                $tables = array();
                                if(isset($xml->tables)) {
                                    foreach($xml->tables->table as $xmlfield) {
                                        $table = array();
                                        // Get Attributes
                                        foreach($xmlfield->attributes() as $attrkey=>$attrvalue) {
                                            $table[$attrkey] = (string) $attrvalue;
                                        }
                                        $tables[] = $table;
                                    }
                                }
                                $processor->tables = $tables;
                                $processor->runorder = (int) $processor->params->get('runorder', 1000);
                                $processors[] = $processor;
                            }
                        }
                    }
                }
            }
        }
        return $processors;
    }
}

/**
 * Class JiProcessor
 */
class JiProcessor {
    public $params;
    public $processed;
    public $start;
    public $limit;
    public $total;
    protected $tmpdir;
    protected $sourcedir;
    public $db;
    public $parentprogress = 0;
    public $totalprogress = 0;
    public $passprogress = 0;
    public $totalprocessors = 1;
    public function get($var, $default=null) {
        switch($var) {
            case 'params':
                return $this->getParams();
                break;
            case 'data':
                return $this->getData();
                break;
            case 'globalvars':
                return $this->getGlobalVars();
                break;
            default:
                if(isset($this->{$var})) {
                    return $this->{$var};
                } else {
                    return $default;
                }
                break;
        }
    }
    function setParams($params) {
        $this->params = $params;
    }
    function getParams() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if($this->params==null) {
            $this->params = new JiMigratorObject();
        }
        return $this->params;
    }
    function getGlobalVars() {
        $params = $this->getParams();
        $globalvars = $params->get('globalvalues', '');
        $globalvars = explode(',', $globalvars);
        $result = array();
        foreach($globalvars as $key) {
            $key = trim($key);
            if($key!=null && $key!='') $result[] = $key;
        }
        return $result;
    }
    function getData() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if($this->data==null) $this->data = new JiMigratorObject();
        return $this->data;
    }
    function setProcessed($processed) {
        $this->processed = $processed;
    }
    function getProcessed() {
        return (int) $this->processed;
    }
    function setStart($start) {
        $this->start = $start;
    }
    function getStart() {
        return (int) $this->start;
    }
    function setLimit($limit) {
        $this->limit = $limit;
    }
    function getLimit() {
        return (int) $this->limit;
    }
    function setTmpdir($tmpdir) {
        $this->tmpdir = $tmpdir;
    }
    function getTmpdir() {
        return $this->tmpdir;
    }
    function setSourcedir($sourcedir) {
        $this->sourcedir = $sourcedir;
    }
    function getSourcedir() {
        return $this->sourcedir;
    }
    function setDb($db) {
        $this->db = $db;
    }
    function getDb($db) {
        if($this->db==null) $this->db = JFactory::getDBO();
        return $this->db;
    }
    public function process() {
        // Debug Mode
        $params = $this->get('params');
        $this->debug = ($params->get('debug', 0)==1)? true : false;
        if($this->debug) $this->setStatus(array('msg'=>'RUNNING PROCESS AS DEBUG'));
        $this->debuglevel = $params->get('debuglevel', 0);

        $data = $this->get('data');
        $this->currenttable = $data->get('currenttable', 0);
        $this->startpass = $data->get('currentpass', 0);
        $this->currentpass = $data->get('currentpass', 0);
        $this->totalprocessed = $data->get('totalprocessed', 0);

        $this->start = $data->get('start', 0);
        $this->processed = 0;
        $this->total = null;
        $this->end = $this->start+$this->limit;
    }

    public function processor() {
        $this->willBeginPass();
        $this->didCompletePass();
    }

    /**
     * Common willBeginPass
     */
    public function willBeginPass() {
        $this->end = ($this->start+$this->limit<$this->total)? $this->start+$this->limit : $this->total;
        // DEBUG LVL0
        if(isset($this->tables) && count($this->tables)>1) {
            $totaltables = count($this->tables);
            $tableprogress = ($this->total>0)? round(($this->start/$this->total)*100, 2) : 100;
            $this->passprogress = round((($this->currenttable - 1)/$totaltables)*100 + $tableprogress/$totaltables, 2);
        } else {
            $this->passprogress = ($this->total>0)? round(($this->start/$this->total)*100, 2) : 100;
        }

        if($this->totalprocessors==1) {
            if(isset($this->tables) && count($this->tables)>1) {
                // Show subpass progress as pass and outer pass progress as total
                $this->totalprogress = $this->passprogress;
                $this->passprogress = ($this->total>0)? round(($this->start/$this->total)*100, 2) : 100;
            } else {
                $this->totalprogress = $this->passprogress;
            }
        } else {
            // Ignore subpass progress and show progress bars relative to outer processor
            $this->totalprogress = round($this->parentprogress+($this->passprogress/$this->totalprocessors), 2);
        }
        if($this->total>0) {
            $msg = 'Processing '.$this->dbtable.' '.$this->start.' - '.$this->end.' / '.$this->total;
        } else {
            $this->end = 1;
            $msg = 'WARNING: No '.$this->dbtable.' rows found';
        }
        $this->setStatus(array(
            'msg'=>$msg,
            'totalprogress'=>$this->totalprogress,
            'passprogress'=>$this->passprogress
        ));
    }

    /**
     * Common didCompletePass
     */
    public function didCompletePass() {
        if($this->end<$this->total) {
            // Process the next batch
            $this->start = $this->start + $this->limit;
            // Wait a bit before starting the next batch
            //usleep(250000);
            $this->processor();
        } else {
            // Completed processor, signal callback
            // Callback has optional response
            call_user_func_array($this->complete, array(null));
        }
    }

    /**
     * Processor complete callback
     */
    public function complete() {

    }

    /**
     * @Depreciated use getItems() and getTotal() instead
     * @param $filename
     * @return array
     */
    public function readCSV($filename) {
        $items = array();
        if(file_exists($filename)) {
            if(($handle = fopen($filename, 'r'))!==false) {
                // Read CSV
                $count = 0;
                $header = array();
                while(($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Process Data
                    if($count==0) {
                        // Create header
                        $header = $row;
                    } else {
                        $item = new stdClass();
                        foreach($row as $c=>$cell) {
                            // Rebuild escaped new lines
                            $cell = str_replace('"\n"', "\n", $cell);
                            // Rebuild escaped carriage returns
                            $cell = str_replace('"\r"', "\r", $cell);
                            // Build object using header and row cells
                            if(isset($header[$c]) && strlen($header[$c])>0) $item->{$header[$c]} = $cell;
                        }
                        $items[] = $item;
                    }
                    $count++;
                }
            }
        }
        return $items;
    }
    public function setStatus($status) {
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
        	$model = JModelLegacy::getInstance('Status', 'JiMigratorModel');
		} else {
			$model = JModel::getInstance('Status', 'JiMigratorModel');
		}
        $model->setStatus($status);
    }
	public function objectToArray($object) {
		$array = array();
		foreach($object as $key=>$value) {
			$array[$key] = $value;
		}
		return $array;
	}
    public function objectToColumns($data, $excludekeys=array()) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        $cols = array();
        for($i=0; $i<count($keys); $i++){
            if(!in_array($keys[$i], $excludekeys)) $cols[] = "`".$keys[$i]."`";
        }
        return implode(',', $cols);
    }
    public function objectToInsert($data, $excludekeys=array()) {
        $db = JFactory::getDBO();
        
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        $cols = array();
        for($i=0; $i<count($keys); $i++){
            if(!in_array($keys[$i], $excludekeys)) $cols[] = $db->quote($variables[$keys[$i]]);
        }
        return implode(',', $cols);
    }
    public function objectToUpdate($data, $excludekeys=array()) {
        $db = JFactory::getDBO();
        
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        $cols = array();
        for($i=0; $i<count($keys); $i++){
            if(!in_array($keys[$i], $excludekeys)) $cols[] = "`".$keys[$i]."`=".$db->quote($variables[$keys[$i]]);
        }
        return implode(',', $cols);
    }
}