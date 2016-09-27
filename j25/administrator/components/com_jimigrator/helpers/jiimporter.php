<?php
/**
 * @version     $Id: jiimporter.php 133 2014-12-16 12:21:00Z Anton Wintergerst $
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
 * Class JiImporter
 */
class JiImporter extends JiProcessor {

    public $sourcedir;

    /**
     * JiImporter implementation of the process entry point
     * Executes complete callback if $this->currenttable index is outside $this->tables scope
     * @uses $this->complete
     */
    public function process($bypass=false) {
        parent::process();

        if(!$bypass) {
            if(isset($this->tables[$this->currenttable])) {
                $table = $this->tables[$this->currenttable];
                $dbtable = (isset($table['source']))? $table['source'] : $table['name'];
                $primarykey = (isset($table['pkey']))? $table['pkey'] : null;

                if($this->start==0) {
                    $this->setStatus(array('msg'=>'Starting '.$dbtable.' Import'));
                } else {
                    $this->setStatus(array('msg'=>'Resuming '.$dbtable.' Import'));
                }
                $this->importTable($dbtable, $primarykey);
            } else {
                call_user_func_array($this->complete, array(null));
            }
        }
    }

    /**
     * Get total rows in CSV (excludes header)
     * @param $filename
     * @return int
     */
    public function getTotal($filename) {
        $count = 0;
        if(file_exists($filename)) {
            if(($handle = fopen($filename, 'r'))!==false) {
                // Read CSV
                while(($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $count++;
                }
            }
        }
        // Offset header
        if($count>0) $count--;
        return $count;
    }

    /**
     * Load rows from CSV
     * @param $filename
     * @param $start
     * @param $limit
     * @return array
     */
    public function getItems($filename, $start, $limit) {
        $items = array();
        if(file_exists($filename)) {
            if(($handle = fopen($filename, 'r'))!==false) {
                // Read CSV
                $count = 0;
                $header = array();
                while(($row = fgetcsv($handle, 0, ',', '"', '"')) !== FALSE) {
                    // Process Data
                    if($count==0) {
                        // Create header
                        $header = $row;
                    } else if($count>=$start && $count<=$start+$limit) {
                        $item = new stdClass();
                        foreach($row as $c=>$cell) {
                            // Rebuild escaped carriage returns
                            $cell = str_replace('"\r\n"', "\r\n", $cell);
                            $cell = str_replace('"\r"', "\r", $cell);
                            // Rebuild escaped new lines
                            $cell = str_replace('"\n"', "\n", $cell);
                            //$cell = str_replace('""', '"', $cell);
                            // Build object using header and row cells
                            if(isset($header[$c]) && strlen($header[$c])>0) $item->{$header[$c]} = $cell;
                        }
                        $items[] = $item;
                    }
                    if($count==$start+$limit) break;
                    $count++;
                }
            }
        }
        return $items;
    }

    /**
     * Method to start the import table processor
     * @param string $dbtable
     * @param string $primarykey
     * @return bool
     */
    public function importTable($dbtable, $primarykey = null) {
        $this->dbtable = $dbtable;
        $this->setStatus(array('msg'=>'Importing '.$dbtable));
        $dbtable = $this->getInputTable();
        $params = $this->get('params');

        // Check if table exists
        $db = JFactory::getDBO();
        $tablelist = $db->getTableList();
        $abstable = $db->getPrefix().$dbtable;
        if(!in_array($abstable, $tablelist)) {
            $this->setStatus(array('msg'=>$dbtable.' table not found!'));
            $this->didCompletePass();
            return false;
        }

        $this->total = $this->getTotal($this->sourcedir.'/'.$this->dbtable.'.csv');
        /* >>> FREE >>> */if($this->total>100) $this->total = 100;/* <<< FREE <<< */
        $this->primarykey = $primarykey;
        if($this->total>0) {
            // Preprocessing
            $this->truncateTable($dbtable);
            // Import Rows
            $this->importTableProcessor();
        } else {
            $this->setStatus(array('msg'=>'No '.$dbtable.' rows found!'));
            $this->didCompletePass();
        }
    }

    public function shouldTruncateTable($dbtable) {
        $params = $this->get('params');
        $result = ($params->get('truncate', 0)==1 && $this->currentpass==0);
        return $result;
    }
    public function truncateTable($dbtable) {
        if($this->shouldTruncateTable($dbtable)) {
            $db = JFactory::getDBO();
            $query = 'TRUNCATE TABLE `#__'.$dbtable.'`';
            $db->setQuery($query);
            if(!$this->debug) $db->query();
            $this->setStatus(array('msg'=>'Cleared '.$dbtable));

            $this->didTruncateTable($dbtable);
        }
    }

    public function didTruncateTable($dbtable) {

    }

    /**
     * Method to get input table name
     * @return string
     */
    public function getInputTable() {
        $dbtable = $this->dbtable;
        return $dbtable;
    }

    /**
     * Main import table processor
     * @var array $items
     * @var $item database row item
     * @var $db
     * @var int $passtotal
     * @var int $i
     */
    public function importTableProcessor() {
        $params = $this->get('params');
        $this->willBeginPass();

        $this->setStatus(array('msg'=>'Loading data from '.$this->dbtable.'.csv'));
        $items = $this->getItems($this->sourcedir.'/'.$this->dbtable.'.csv', $this->start, $this->limit);
        $passtotal = count($items);

        $db = JFactory::getDBO();
        $headers = $this->getColumns();

        $dbtable = $this->getInputTable();
        $this->setStatus(array('msg'=>'Importing data to #__'.$dbtable));

        for($i=0; $i<$passtotal; $i++) {
            if(isset($items[$i])) {
                // DEBUG LVL1
                if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Processing '.$this->dbtable.' item '.($i+$this->start+1).' / '.$this->total));

                $item = $items[$i];

                $this->willImportTableRow($item);

                if(in_array($this->primarykey, $headers)) {
                    $exists = $this->doesTableRowExist($item);
                } else {
                    $exists = null;
                }
                // Remove primary key if exists
                if($params->get('resetid', 0)==1 && $exists==null && isset($item->{$this->primarykey})) {
                    unset($item->{$this->primarykey});
                }

                // Check if item properties exist in columns
                $invalidkeys = array();
                foreach($item as $key=>$value) {
                    if(!in_array($key, $headers)) {
                        $invalidkeys[$key] = $item->{$key};
                        unset($item->{$key});
                    }
                }

                // Build common query parts
                $columns = $this->objectToColumns($item);
                $insert = $this->objectToInsert($item);

                if($exists==null) {
                    // Only insert new items
                    $query = 'INSERT IGNORE INTO `#__'.$dbtable.'` ('.$columns.')';
                    $query.= ' VALUES ('.$insert.')';
                    $db->setQuery($query);
                    if(!$this->debug) $db->query();
                    $newid = (!$this->debug)? $db->insertid() : 'Undetermined';

                    // DEBUG LVL1
                    if($this->debuglevel>=1) {
                        if(isset($item->title)) {
                            $this->setStatus(array('msg'=>'Setting `id` to: '.$newid.' for #__'.$dbtable.' - Title: '.$item->title));
                        } else {
                            $this->setStatus(array('msg'=>'Setting `id` to: '.$newid.' for #__'.$dbtable));
                        }
                        $this->setStatus(array('msg'=>'Creating item for #__'.$dbtable.' '.($this->start+$i+1).'/'.$this->total.' - ID #'.$newid));
                    }
                    $item->id = $newid;
                } elseif($params->get('overwrite', 0)==1) {
                    // Push all items into database
                    $update = $this->objectToUpdate($item);
                    $query = 'INSERT INTO `#__'.$dbtable.'` ('.$columns.')';
                    $query.= ' VALUES ('.$insert.')';
                    $query.= ' ON DUPLICATE KEY UPDATE '.$update;
                    $db->setQuery($query);
                    if(!$this->debug) $db->query();

                    // DEBUG LVL1
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Updating item for #__'.$dbtable.' '.($this->start+$i+1).'/'.$this->total).' - ID #'.$item->id);
                } elseif($params->get('append', 0)==1 && isset($item->id)) {
                    // Append to existing
                    $columns = $this->objectToColumns($item, array('id'));
                    $insert = $this->objectToInsert($item, array('id'));

                    $query = 'INSERT IGNORE INTO `#__'.$dbtable.'` ('.$columns.')';
                    $query.= ' VALUES ('.$insert.')';
                    $db->setQuery($query);
                    if(!$this->debug) $db->query();

                    $newid = (!$this->debug)? $db->insertid() : 'Undetermined';
                    // DEBUG LVL1
                    if($this->debuglevel>=1) {
                        $this->setStatus(array('msg'=>'Setting `id` from: '.$item->id.' to: '.$newid.' for #__'.$dbtable.' - ID #'.$item->id));
                        $this->setStatus(array('msg'=>'Creating #__'.$dbtable.' '.($this->start+$i+1).'/'.$this->total.' - ID #'.$newid));
                    }
                    $this->didAppendItem($item, $newid);
                    $item->id = $newid;
                } else {
                    // DEBUG LVL1
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'WARNING: user_id '.$item->user_id.' already exists for #__'.$dbtable.' and was skipped'));
                }

                // DEBUG LVL1 Development debug
                if($this->debug && $this->debuglevel==1) {
                    $this->setStatus(array('msg'=>'Columns: '.$columns));
                    $this->setStatus(array('msg'=>'Insert: '.$insert));
                }

                // DEBUG LV0 Check there are no errors
                if($db->getErrorMsg()) $this->setStatus(array('msg'=>'ERROR: '.$db->getErrorMsg()));

                // restore invalid keys to item object
                foreach($invalidkeys as $key=>$value) {
                    $item->{$key} = $value;
                }
                $this->didImportTableRow($item);
            }
        }
        // Free up memory
        unset($items, $db, $i, $columns, $insert, $item, $query, $newid, $columndata, $headers, $key, $value);
        if(function_exists('gc_collect_cycles')) gc_collect_cycles();

        $this->didCompletePass();
    }

    public function didAppendItem(&$item, $newid)
    {

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

        $dbtable = $this->getInputTable();

        $db = JFactory::getDBO();
        $query = 'SHOW COLUMNS FROM '.$db->getPrefix().$dbtable;
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
     * Executes before storing in database
     * @param $item
     */
    public function willImportTableRow(&$item) {
        $params = $this->get('params');
        if($keydata = $params->get('keymap')) {
            //$this->setStatus(array('msg'=>$keydata));
            // Shift values to match map
            $keymap = array();
            $lines = explode("\n", $keydata);
            foreach($lines as $row) {
                $parts = explode(':', $row);
                $key = trim($parts[0]);
                if(isset($parts[1])) {
                    $parts[1] = trim($parts[1]);
                    if(preg_match_all('/([^,= ]+)=([^,= ]+)/', $parts[1], $matches)) {
                        $values = array_combine($matches[1],$matches[2]);
                        if(isset($item->{$key}) && isset($values[$item->{$key}])) {
                            $oldval = $item->{$key};
                            $newval = $values[$item->{$key}];
                            if($this->debuglevel>=1) {
                                $suffix = (isset($item->id))? ' - id: '.$item->id : '';
                                $this->setStatus(array('msg'=>'Setting `'.$key.'` from: '.$oldval.' to: '.$newval.' for #__'.$this->dbtable.$suffix));
                            }
                            $item->{$key} = $newval;
                        }
                        $keymap[$key] = $values;
                    }
                }
            }
        }
    }

    /**
     * Executes after storing in database
     * @param $item
     */
    public function didImportTableRow(&$item) {
    }

    /**
     * Method to find existing row in database
     * @param $item
     * @return null|string
     */
    public function doesTableRowExist($item) {
        $params = $this->get('params');
        $exists = null;
        if($this->primarykey!=null) {
            $dbtable = $this->getInputTable();
            $db = JFactory::getDBO();
            // Check if exists
            $value = $item->{$this->primarykey};
            if(!is_int($value)) $value = $db->quote($value);
            $query = 'SELECT `'.$this->primarykey.'` FROM `#__'.$dbtable.'` WHERE `'.$this->primarykey.'`='.$value;
            $db->setQuery($query);
            $exists = $db->loadResult();
            // DEBUG LV0 Check there are no errors
            if($db->getErrorMsg()) $this->setStatus(array('msg'=>$db->getErrorMsg()));
        }
        return $exists;
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
                $this->importTableProcessor();
            }
        } else {
            $this->currenttable++;
            if($this->currenttable < count($this->tables)) {
                // Defer dbtable pass to callback for next script cycle
                $data = $this->buildEndTableData();
                call_user_func_array($this->pass, array($data));
            } else {
                // Completed processor, execute complete callback
                $title = (isset($this->dbtable))? $this->dbtable : 'pass';
                $this->setStatus(array('msg'=>'Import '.$title.' complete'));

                $data = $this->buildEndProcessorData();
                call_user_func_array($this->complete, array($data));
            }
        }
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
        $data = array(
            'currenttable'=>$this->currenttable,
            'currentpass'=>0,
            'start'=>0,
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
        $data = array(
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
     * Method to reset alias and title
     * @param $alias
     * @param $title
     * @param string $language
     * @return array
     */
    public function resetAlias($alias, $title, $language='*', $conditions=array()) {
        if(!isset($alias) || $alias==null) $alias = JApplicationHelper::stringURLSafe($title);

        $dbtable = $this->getInputTable();
        $db = JFactory::getDBO();
        $query = 'SELECT `title` FROM #__'.$dbtable.' WHERE `alias`='.$db->quote($alias).' AND `language`='.$db->quote($language);
        if(count($conditions)>0) $query.= ' AND'.implode(' AND ', $conditions);

        $db->setQuery($query);
        $result = $db->loadObject();
        while($result!=null) {
            if($title==$result->title) $title = JString::increment($title);
            $alias = JString::increment($alias, 'dash');

            $query = 'SELECT `title` FROM #__'.$dbtable.' WHERE `alias`='.$db->quote($alias).' AND `language`='.$db->quote($language);
            if(count($conditions)>0) $query.= ' AND'.implode(' AND ', $conditions);
            $db->setQuery($query);
            $result = $db->loadObject();
        }
        return array($title, $alias);
    }

    /**
     * Method to rebuild params
     * @param $params
     * @return string
     */
    public function rebuildParams($params) {
        if($params!=null && json_decode($params)==null) {
            $newparams = array();
            // Read params
            $params = explode("\n", $params);
            if(count($params>0)) {
                foreach($params as $param) {
                    if(strlen(trim($param))>0) {
                        $parts = explode('=', $param);
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if(strlen($key)>0 && strlen($value)>0) {
                            // Add parameter to newparams
                            $newparams[$key] = $value;
                        }
                    }
                }
            }
            $params = (count($newparams)>0)? json_encode($newparams) : '';
        }
        return $params;
    }

    public function unparse_url($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}