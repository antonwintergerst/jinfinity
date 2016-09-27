<?php
/**
 * @version     $Id: import.php 264 2014-12-16 11:16:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiMigratorModelImport extends JiModel
{
    function __construct() {
        parent::__construct();
        $this->hasSetPaths = false;
    }
    function setPaths() {
        if(!$this->hasSetPaths) {
            $this->rootdir = JPATH_SITE;

            // Set tmp directories
            $tmpdir = $this->rootdir.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'tmp';
            $this->tmpdir = $tmpdir;

            // Set output directory
            $inputdir = $this->tmpdir.DS.'input';
            $this->inputdir = $inputdir;
            $this->inputzipfile = $this->tmpdir.DS.'input.zip';

            // Set params directory
            $paramsdir = $this->tmpdir.DS.'params';
            $this->paramsdir = $paramsdir;

            // Create directories as required
            $this->createDirectory(array($tmpdir, $inputdir, $paramsdir));

            $this->hasSetPaths = true;
        }
    }

    function clear() {
        $this->setPaths();
        $this->cleanup();
        // Purge any old migration data
        $this->delTree($this->inputdir);
        $zipfile = $this->inputzipfile;
        if(file_exists($zipfile)) {
            // Delete previous migration archive
            unlink($zipfile);
        }
    }

    /**
     * Method to build a directory (automatically sets permissions and adds index.html as required)
     * @param string|array $dirs
     */
    function createDirectory($dirs) {
        if(!is_array($dirs)) $dirs = array($dirs);
        $indexfile = $this->rootdir.DS.'images'.DS.'index.html';
        $indexfileexists = file_exists($indexfile);
        foreach($dirs as $dir) {
            if(!file_exists($dir) || !is_dir($dir)) {
                mkdir($dir);
                chmod($dir, 0755);
            }
            if($indexfileexists && !file_exists($dir.DS.'index.html')) copy($indexfile, $dir.DS.'index.html');
        }
    }

    /**
     * Method to upload migration archive
     */
    function upload() {
        // Handle PHP errors
        register_shutdown_function(array($this, 'fatal_handler'));

        $this->cleanup();
        $this->setPaths();

        $this->setStatus(array('msg'=>'Uploading Migration Archive...'));
        $zipfile = $this->inputzipfile;

        // Upload Zip
        $inputvar = 'sourcezip';
        $haserrors = false;
        if(isset($_FILES[$inputvar])) {
            $this->setStatus(array('msg'=>'Processing upload...'));

            $allowedExtensions = array('7z', 'rar', 'tar', 'zip');
            $nameparts = explode('.', $_FILES[$inputvar]['name']);
            $type = strtolower(end($nameparts));

            if(in_array($type, $allowedExtensions) && is_uploaded_file($_FILES[$inputvar]['tmp_name'])) {
                if(file_exists($zipfile)) {
                    // Delete previous migration archive
                    unlink($zipfile);
                    $this->setStatus(array('msg'=>'Purged previous archive!'));
                    // Wait a bit after deleting
                    sleep(2);
                }
                move_uploaded_file($_FILES[$inputvar]['tmp_name'], $zipfile);
                // Wait a bit after moving
                sleep(2);
            } else {
                $this->setStatus(array('msg'=>'WARNING: Zip failed to upload. Incorrect filetype or other upload error'));
                $haserrors = true;
            }
        } else {
            $this->setStatus(array('msg'=>'WARNING: Zip failed to upload. File not found in upload array'));
            $haserrors = true;
        }
        if(file_exists($zipfile)) {
            $haserrors = false;
            $this->extractZip($zipfile);
        }

        if(!$haserrors) {
            $this->completeUpload();
        } else {
            $this->completeUpload('Upload Failed!', false);
        }
    }

    public function completeUpload($msg='Migration Uploaded Successfully!', $success=true)
    {
        $this->completeLog($msg, $success);
        // clear tmp status
        $this->cleanup();

        exit;
    }

    public function completeImport($msg='Import Complete!', $success=true)
    {
        $this->completeLog($msg, $success);
        // cleanup handled by ajax
        exit;
    }

    public function completeLog($msg='Complete', $success=true)
    {
        // set final status
        $this->setStatus(array('msg'=>$msg));

        // save log and return final status
        date_default_timezone_set('UTC');
        $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
        $this->setStatus(array(
            'msg'=>'<h2>'.$msg.'</h2><a class="jibtn tier1action small viewlogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$logfile.'" target="_blank">View Log</a>',
            'status'=>($success)?'complete':'failed',
            'totalprogress'=>100,
            'passprogress'=>100,
            'logfile'=>$logfile
        ));
    }

    /**
     * Method to extract migration archive
     * @param string $zipfile
     */
    function extractZip($zipfile) {
        $jparams = $this->getComponentParams();
        $this->setPaths();
        $params = $this->getParams();

        // Purge any old migration data
        $this->delTree($this->inputdir);
        $this->setStatus(array('msg'=>'Purged temporary files!'));
        // Wait a bit after deleting
        sleep(2);

        if(file_exists($zipfile)) {
            $extractdir = $this->inputdir;
            $this->createDirectory($extractdir);

            // Extract Zip
            $extractmethod = $params->get('extractmethod', 'shell');
            if($extractmethod=='php') {
                $this->setStatus(array('msg'=>'Extracting files using shell command...'));
                exec('unzip -o '.$zipfile.' -x -d '.$extractdir);
            } else {
                $this->setStatus(array('msg'=>'Extracting files using PHP ZipArchive class...'));
                if($jparams->get('zipengine', 'native')=='native') {
                    $zip = new ZipArchive;
                } else {
                    require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiziparchive.php');
                    $zip = new JiZipArchive;
                }
                if($zip->open($zipfile)===true) {
                    for($i = 0; $i < $zip->numFiles; $i++) {
                        $entry = $zip->getNameIndex($i);
                        if(substr($entry, -1)==DS || strstr($entry, '.')==false) continue; // skip directories
                        $zip->extractTo($extractdir, array($entry));
                    }
                    $zip->close();
                } else {
                    $this->setStatus(array('msg'=>'Failed to open ZIP Archive'));
                }
            }
        }
    }

    function getImportState() {
        $this->setPaths();
        $i = 0;
        $dir = $this->inputdir;
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..', 'index.html')) && !is_dir($dir.$file))
                    $i++;
            }
        }
        $state = ($i==0)? 'upload' : 'import';
        return $state;
    }

    /**
     * Builds importer form array
     * @return array
     */
    function getForm() {
        $this->setPaths();

        $importers = $this->getImporters();
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'field.php');
        $fieldhelper = new JiMigratorFieldHelper();
        $fieldhelper->setPaths(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'jifields');
        $form = array();
        foreach($importers as $importer) {
            $fieldlist = array();
            foreach($importer->fields as $field) {
                if(isset($field->name)) $field->id = $importer->name.$field->name;
                if(isset($field->options)) $field->params = array('options'=>$field->options);
                $JiField = $fieldhelper->loadType($field);
                $fieldlist[] = $JiField;
            }
            $form[$importer->name] = $fieldlist;
        }
        return $form;
    }

    function getImporters() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiprocessor.php');
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiimporter.php');

        $processorHelper = new JiProcessorHelper();
        // Set importer paths
        $paths = $this->findImporterPaths();
        $processorHelper->setPaths($paths);
        $importers = $processorHelper->getProcessors();
        return $importers;
    }

    function findImporterPaths() {
        $paths = array();

        $path = JPATH_SITE.DS.'administrator'.DS.'components';
        $components = scandir($path);

        foreach($components as $component) {
            if($component === '.' || $component === '..') continue;
            $subpath = $path.DS.$component;
            if(!is_dir($subpath)) continue;
            $subpath.= DS.'migrate';
            if(file_exists($subpath) && is_dir($subpath)) {
                $paths[] = $subpath;
            }
        }
        $paths[] = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'models'.DS.'importers';
        return $paths;
    }

    function getGroups() {
        $importers = $this->getImporters();
        // Setup Groups
        $coregroup = new stdClass();
        $coregroup->title = 'Joomla Core';
        $coregroup->importers = array();
        $thirdgroup = new stdClass();
        $thirdgroup->title = '3rd-Party';
        $thirdgroup->importers = array();
        // Group Importers
        foreach($importers as $importer) {
            if($importer->group=='joomlacore') {
                $coregroup->importers[] = $importer;
            } else {
                $thirdgroup->importers[] = $importer;
            }
        }
        $groups = array($coregroup, $thirdgroup);
        return $groups;
    }

    function getContent() {
        $this->setPaths();

        $items = array();
        $sortlist = array();

        $migratedir = $this->inputdir;
        if(file_exists($migratedir)) {
            if($dh = opendir($migratedir)) {
                while(($filename = readdir($dh))!==false) {
                    if($filename!='..' && $filename!='.' && $filename!='index.html') {
                        $item = new stdClass();
                        $item->name = $filename;
                        $items[] = $item;
                        $sortlist[] = $filename;
                    }
                }
            }
        }
        array_multisort($items, $sortlist, SORT_DESC);
        return $items;
    }

    function cleanup() {
        $this->setPaths();

        // Reset Status
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Status', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Status', 'JiMigratorModel');
        }
        $model->cleanup();

        // Reset params
        $params = $this->getParams();
        $params->set('currentprocessor', null);
        $params->set('passdata', null);
        $params->set('resumesnapshot', null);
        $params->set('jifields', null);
        $params->set('totalprogress', 0);
        $params->set('passprogress', 0);
        $this->setParams($params);

        if(JRequest::getVar('resetglobalvars', false)) {
            $this->setGlobalValues(array());
            $this->setStatus(array('msg'=>'Content transposition maps and global variables reset!'));
        }
    }

    /**
     * Import entry point
     */
    function import() {
        $jparams = $this->getComponentParams();
        $this->cleanup();

        // Save Params
        $params = $this->getParams();
        $processors = array();
        $enabledProcessors = JRequest::getVar('importers', null);
        if($enabledProcessors!=null) {
            foreach($enabledProcessors as $processor=>$value) {
                if($value=='on' ) $processors[] = $processor;
            }
        }
        $params->set('importers', $processors);
        $params->set('processall', JRequest::getVar('processall', false));
        $params->set('dobackup', JRequest::getVar('dobackup', false));
        $params->set('jifields', JRequest::getVar('jifields', null));

        $this->setParams($params);

        if($jparams->get('keepalive', 0)==1 && !ini_get('safe_mode')) {
            // Keep alive
            ini_set('max_execution_time', 0);
            //ignore_user_abort(1);
            set_time_limit(0);
        }

        $this->doimport();
    }

    /**
     * Import Iterator
     * @return string
     */
    function doimport() {
        // Handle PHP errors
        register_shutdown_function(array($this, 'fatal_handler'));

        // Setup page to stream live output
        header("Content-type: text/html; charset=utf-8");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
        if(ob_get_level() > 0) ob_end_flush();
        echo 'Processing...';
        if(ob_get_level() > 0) {
            flush();
            ob_flush();
        }

        // Get Params
        $params = $this->getParams();

        $this->setStatus(array('msg'=>'Initializing...'));

        $jifields = (array) $params->get('jifields');
        JRequest::setVar('jifields', $jifields);

        // Get Enabled Processors
        $enabledProcessors = $params->get('importers');

        // Build & Order Processors
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiprocessor.php');
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiimporter.php');
        $processorHelper = new JiProcessorHelper();
        // Set importer paths
        $paths = $this->findImporterPaths();
        $processorHelper->setPaths($paths);
        $processors = $processorHelper->getProcessors('runorder', 'description');

        // Only run active processors
        $activeprocessors = array();
        $activetables = array();
        foreach($processors as $processor) {
            if(in_array($processor->name, $enabledProcessors) || $params->get('processall', false)) {
                // attach jifield values to processor
                $proparams = $processor->get('params');
                foreach($jifields as $key=>$value) {
                    $prefix = substr($key, 0, strlen($processor->name));
                    if($prefix==$processor->name) {
                        $proparams->set(substr($key, strlen($processor->name)), $value);
                    }
                }
                $processor->setParams($proparams);

                $activeprocessors[] = $processor;
                foreach($processor->tables as $table) {
                    $activetables[] = $table;
                }
            }
        }
        $this->processors = $activeprocessors;

        // Free up memory
        //unset($processors, $processor, $processorHelper);
        //if(function_exists('gc_collect_cycles')) gc_collect_cycles();

        // Execute Processors
        if($params->get('dobackup')) {
            $this->currentprocessor = $params->get('currentprocessor', -1);
        } else {
            $this->currentprocessor = $params->get('currentprocessor', 0);
        }
        $this->totalprocessors = count($this->processors);

        if($this->totalprocessors>0) {
            if($this->currentprocessor==-1) {
                // Start creating snapshot (leads to import processors when finished)
                $this->setStatus(array('msg'=>'SNAPSHOT: Loading processor...'));
                $this->createSnapshot($activetables);
            } elseif($this->currentprocessor<$this->totalprocessors) {
                $this->setStatus(array('msg'=>'IMPORT: Loading processor...'.($this->currentprocessor+1).' / '.$this->totalprocessors));
                $this->processor();
            }

            // Keep script alive until all processors have finished
            while($this->currentprocessor<$this->totalprocessors) {
                sleep(1);
            }
            ob_start();
        } else {
            $this->completeImport('ERROR: No processors selected to execute!', false);
        }
        $this->completeUpload();
    }
    private function createSnapshot($tables) {
        $params = $this->getParams();
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        $model->complete = array($this, 'didCompleteSnapshotProcessor');
        $model->pass = array($this, 'didCompleteSnapshotPass');
        if($params->get('resumesnapshot')==1) {
            $model->dosnapshot();
        } else {
            $model->saveSnapshot($tables, false);
        }
    }
    public function didCompleteSnapshotPass($data) {
        // Progress
        $totalprogress = $data['totalprogress'];
        $passprogress = $data['passprogress'];

        $params = $this->getParams();
        $params->set('passdata', $data);
        $params->set('currentprocessor', $this->currentprocessor);
        $params->set('resumesnapshot', 1);
        $params->set('totalprogress', $totalprogress);
        $params->set('passprogress', $passprogress);
        $this->setParams($params);

        // prevent negative progress
        if($totalprogress<0) $totalprogress = 1;

        $this->setStatus(array(
            'msg'=>'SNAPSHOT: Pass Complete - Total Progress: '.$totalprogress.'%',
            'totalprogress'=>$totalprogress,
            'passprogress'=>$passprogress,
            'status'=>'pass',
            'url'=>'index.php?option=com_jimigrator&view=import&task=doimport'
        ));
        echo 'Backup Pass Complete';
        echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=import&task=doimport' . "';</script>\n";
        exit;
    }
    public function didCompleteSnapshotProcessor($data=null) {
        $this->currentprocessor = 0;
        $totalprogress = 0;
        $passprogress = 0;

        $params = $this->getParams();
        $params->set('passdata', null);
        $params->set('currentprocessor', 0);
        $params->set('resumesnapshot', null);
        $params->set('totalprogress', $totalprogress);
        $params->set('passprogress', $passprogress);
        $this->setParams($params);

        $this->setStatus(array(
            'msg'=>'SNAPSHOT: Backup Complete!',
            'totalprogress'=>$totalprogress,
            'passprogress'=>$passprogress,
            'status'=>'pass',
            'url'=>'index.php?option=com_jimigrator&view=import&task=doimport'
        ));
        echo 'Backup Complete';
        echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=import&task=doimport' . "';</script>\n";
        exit;
    }

    /**
     * Processor for JiImporters
     */
    function processor() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();
        $jparams = $this->getComponentParams();
        $params = $this->getParams();

        // Progress
        $totalprogress = $params->get('totalprogress', 0);
        $passprogress = $params->get('passprogress', 0);

        if(isset($this->processors[$this->currentprocessor])) {
            $processor = $this->processors[$this->currentprocessor];
            $processor->setLimit($jparams->get('process_limit', 100));
            $processor->setTmpdir($this->tmpdir);
            $processor->setSourcedir($this->inputdir);
            $processor->complete = array($this, 'didCompleteProcessor');
            $processor->pass = array($this, 'didCompletePass');
            $processor->data = new JiMigratorObject($params->get('passdata'));
            // Progress
            $processor->parentprogress = round(($this->currentprocessor/count($this->processors))*100, 2);
            $processor->totalprocessors = $this->totalprocessors;

            // Update processor global vars with global values
            $globalvars = $processor->get('globalvars');
            $globalvalues = $this->getGlobalValues();
            foreach($globalvars as $key) {
                $key = trim($key);
                $processor->{$key} = $globalvalues->get($key, null);
            }

            $this->setStatus(array(
                'msg'=>'IMPORT: Executing '.$procesor->name.' importer',
                'totalprogress'=>$totalprogress,
                'pass'=>$passprogress
            ));
            $processor->process();

            // Free up memory
            //unset($processor, $globalvars, $key);
            //if(function_exists('gc_collect_cycles')) gc_collect_cycles();
        } else {
            $this->completeImport('ERROR: Processor was not found or is missing!', false);
        }
    }

    /**
     * This function persists the current state and reloads the import script
     * JiProcessors use this function to split up large processing.
     * @param $data
     */
    public function didCompletePass($data) {
        // Progress
        $totalprogress = $data['totalprogress'];
        $passprogress = $data['passprogress'];

        $params = $this->getParams();
        $params->set('passdata', $data);
        $params->set('currentprocessor', $this->currentprocessor);
        $params->set('totalprogress', $totalprogress);
        $params->set('passprogress', $passprogress);
        $this->setParams($params);

        // Update global values
        if(isset($data['globalvalues'])) {
            $globalvalues = $this->getGlobalValues();
            foreach($data['globalvalues'] as $key=>$value) {
                $globalvalues->set($key, $value);
            }
            $this->setGlobalValues($globalvalues);
        }

        // prevent negative progress
        if($totalprogress<0) $totalprogress = 1;

        $this->setStatus(array(
            'msg'=>'IMPORT: Pass Complete - Total Progress: '.$totalprogress.'%',
            'totalprogress'=>$totalprogress,
            'passprogress'=>$passprogress,
            'status'=>'pass',
            'url'=>'index.php?option=com_jimigrator&view=import&task=doimport'
        ));
        echo 'Pass Complete';
        echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=import&task=doimport' . "';</script>\n";
        exit;
    }

    /**
     * JiProcessors use this function as a callback to signal process completion
     * @param $data
     */
    public function didCompleteProcessor($data=null) {
        $this->setPaths();
        $params = $this->getParams();

        $this->currentprocessor++;

        // Progress
        $totalprogress = round(($this->currentprocessor/count($this->processors))*100, 2);
        $passprogress = 0;

        // Clear passdata
        $params->set('passdata', null);
        $params->set('currentprocessor', $this->currentprocessor);
        $params->set('totalprogress', $totalprogress);
        $params->set('passprogress', $passprogress);

        // Update global values
        if(isset($data['globalvalues'])) {
            $globalvalues = $this->getGlobalValues();
            foreach($data['globalvalues'] as $key=>$value) {
                $globalvalues->set($key, $value);
            }
            $this->setGlobalValues($globalvalues);
        }

        $this->setParams($params);

        // Free up memory
        //unset($data, $key, $value);
        //if(function_exists('gc_collect_cycles')) gc_collect_cycles();


        if($this->currentprocessor<$this->totalprocessors) {
            // do next processor
            $this->setStatus(array(
                'msg'=>'IMPORT: Finished processor',
                'passprogress'=>100,
                'totalprogress'=>$totalprogress,
                'status'=>'pass',
                'url'=>'index.php?option=com_jimigrator&view=import&task=doimport'
            ));
            echo 'Finished Processor';
            echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=import&task=doimport' . "';</script>\n";
            exit;
            $this->processor();
        } else {
            // Completed all processors
            $this->setStatus(array(
                'msg'=>'IMPORT: Wrapping up...',
                'totalprogress'=>99,
                'passprogress'=>100
            ));

            /*$extractdir = $this->inputdir;
            if($params->get('purgetmp', false) && is_dir($extractdir)) {
                $this->delTree($extractdir);
                $this->setStatus(array('msg'=>'Purged temporary files!'));
            }*/

            $this->completeImport();
        }
    }

    /**
     * Deletes a whole directory tree returning true|false
     * @param $dir
     * @return bool
     */
    function delTree($dir) {
        if(!file_exists($dir)) return false;

        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir($dir.DS.$file)) ? $this->delTree($dir.DS.$file) : unlink($dir.DS.$file);
        }
        return rmdir($dir);
    }

    /**
     * Debug method to show memory usage
     */
    function showMemoryUsage() {
        if(!isset($this->memoryLimit)) $this->memoryLimit = (int) ini_get('memory_limit')*1048576;
        $memoryUsed = memory_get_usage(true);
        $this->setStatus(array('msg'=>'Memory Used: '.$memoryUsed.'/'.$this->memoryLimit));
        if($memoryUsed + 100000>$this->memoryLimit) {
            $this->setStatus(array('msg'=>'Fatal Error: exceeded memory limit', 'status'=>'failed'));
        }
    }

    /**
     * Method to get component params
     * @param string $component
     * @return mixed
     */
    function getComponentParams($component='com_jimigrator') {
        jimport('joomla.application.component.helper');
        $jparams = JComponentHelper::getParams($component);
        return $jparams;
    }

    /**
     * Method to get processor params
     * @return object JiObject
     */
    function getParams() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();

        $paramsfile = $this->paramsdir.DS.'params.json';
        $params = (file_exists($paramsfile))? file_get_contents($paramsfile) : null;
        $params = new JiMigratorObject($params);
        return $params;
    }

    /**
     * Method to save processor params
     * @param mixed $params
     */
    function setParams($params) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();

        if(!$params instanceof JiMigratorObject) $params = new JiMigratorObject($params);
        file_put_contents($this->paramsdir.DS.'params.json', $params->toJSON());
    }

    /**
     * Method to retrieve global values
     * @return object JiObject
     */
    function getGlobalValues() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();

        $paramsfile = $this->paramsdir.DS.'globalvalues.json';
        $params = (file_exists($paramsfile))? file_get_contents($paramsfile) : null;
        $params = new JiMigratorObject($params);
        return $params;
    }

    /**
     * Method to persist global values
     * @param mixed $values
     */
    function setGlobalValues($values) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();

        if(!$values instanceof JiMigratorObject) $values = new JiMigratorObject($values);
        file_put_contents($this->paramsdir.DS.'globalvalues.json', $values->toJSON());
    }

    /**
     * Updates the status and log files
     * @param array $status
     * @param string $logfile
     */
    public function setStatus($status, $logfile=null) {
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Status', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Status', 'JiMigratorModel');
        }
        $model->setStatus($status, $logfile);
    }

    public function fatal_handler() {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if( $error !== NULL) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
        }
        switch ($errno) {

            case E_ERROR:
            case E_USER_ERROR:
                $this->setStatus(array('msg'=>'PHP FATAL ERROR on [line] '.$errline.' in [file] '.$errfile));
                $this->setStatus(array('msg'=>$errstr));
                $this->setStatus(array('msg'=>'Execution aborted due to PHP fatal error'));

                $this->completeImport('Critical Error!', false);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                /*$this->setStatus(array('msg'=>'PHP WARNING on [line] '.$errline.' in [file] '.$errfile));
                $this->setStatus(array('msg'=>$errstr));*/
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                /*$this->setStatus(array('msg'=>'PHP NOTICE on [line] '.$errline.' in [file] '.$errfile));
                $this->setStatus(array('msg'=>$errstr));*/
                break;

            default:
                // Ignore E_DEPRECATED, E_STRICT etc.
                break;
        }

        //$this->setStatus(array('msg'=>'FATAL ERROR: '.$errno.' [file]: '.$errfile.' [line]: '.$errline.' [msg]: '.$errstr));
    }
}