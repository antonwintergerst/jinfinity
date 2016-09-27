<?php 
/**
 * @version     $Id: export.php 239 2014-12-15 11:12:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiMigratorModelExport extends JiModel
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
            $outputdir = $this->tmpdir.DS.'output';
            $this->outputdir = $outputdir;
            $this->outputzipfile = $this->tmpdir.DS.'output.zip';

            // Set params directory
            $paramsdir = $this->tmpdir.DS.'params';
            $this->paramsdir = $paramsdir;

            // Create directories as required
            $this->createDirectory(array($tmpdir, $outputdir, $paramsdir));

            $this->hasSetPaths = true;
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

    function getForm() {
        $this->setPaths();

        $exporters = $this->getExporters();
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'field.php');
        $fieldhelper = new JiMigratorFieldHelper();
        $fieldhelper->setPaths(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'jifields');
        $form = array();
        foreach($exporters as $exporter) {
        	$fieldlist = array();
            foreach($exporter->fields as $field) {
                if(isset($field->name)) $field->id = $exporter->name.$field->name;
                $JiField = $fieldhelper->loadType($field);
                $fieldlist[] = $JiField;
            }
            $form[$exporter->name] = $fieldlist;
        }
        return $form;
    }

    function getExporters() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiprocessor.php');
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiexporter.php');

        $processorHelper = new JiProcessorHelper();
        // Set exporter paths
        $paths = $this->findExporterPaths();
        $processorHelper->setPaths($paths);
        $this->exporters = $processorHelper->getProcessors();
        return $this->exporters;
    }

    function findExporterPaths() {
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
        $paths[] = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'models'.DS.'exporters';
        return $paths;
    }

    function getGroups() {
        $exporters = $this->getExporters();
        // Setup Groups
        $coregroup = new stdClass();
        $coregroup->title = 'Joomla Core';
        $coregroup->exporters = array();
        $thirdgroup = new stdClass();
        $thirdgroup->title = '3rd-Party';
        $thirdgroup->exporters = array();
        // Group Exporters
        foreach($exporters as $exporter) {
            if($exporter->group=='joomlacore') {
                $coregroup->exporters[] = $exporter;
            } else {
                $thirdgroup->exporters[] = $exporter;
            }
        }
        $groups = array($coregroup, $thirdgroup);
        return $groups;
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
        $params->set('files', null);
        $params->set('sysfiles', null);
        $params->set('jifields', null);
        $params->set('totalprogress', 0);
        $params->set('passprogress', 0);
        $this->setParams($params);

        $this->setGlobalValues(array());

        // Remove output directory
        if(file_exists($this->outputdir)) $this->delTree($this->outputdir);

        // Delete old ZIP
        if(file_exists($this->outputzipfile)) unlink($this->outputzipfile);
    }

    /**
     * Export entry point
     */
    function export() {
        $jparams = $this->getComponentParams();
        $this->cleanup();
        $this->setPaths();

        // Create output directory
        $this->createDirectory($this->outputdir);

        // Save Params
        $params = $this->getParams();
        $processors = array();
        $enabledProcessors = JRequest::getVar('exporters');
        if($enabledProcessors!=null) {
            foreach($enabledProcessors as $processor=>$value) {
                if($value=='on') $processors[] = $processor;
            }
        }
        $params->set('exporters', $processors);
        $params->set('processall', JRequest::getVar('processall', false));
        $params->set('purgetmp', JRequest::getVar('purgetmp', false));
        $params->set('jifields', JRequest::getVar('jifields'));

        $this->setParams($params);

        if($jparams->get('keepalive', 0)==1 && !ini_get('safe_mode')) {
            // Keep alive
            ini_set('max_execution_time', 0);
            //ignore_user_abort(1);
            set_time_limit(0);
        }

        $this->doexport();
    }

    /**
     * Export Iterator
     * @return string
     */
    function doexport() {
        // Handle PHP errors
        register_shutdown_function(array($this, 'fatal_handler'));
        //set_error_handler(array($this, 'fatal_handler'));

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
        $enabledProcessors = $params->get('exporters', array());


        // Build & Order Processors
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiprocessor.php');
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiexporter.php');
        $processorHelper = new JiProcessorHelper();
        // Set exporter paths
        $paths = $this->findExporterPaths();
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
                    $activetables[] = $table['name'];
                }
            }
        }
        $this->processors = $activeprocessors;

        // Free up memory
        unset($processors, $processor, $processorHelper);
        if(function_exists('gc_collect_cycles')) gc_collect_cycles();

        // Execute Processors
        $this->currentprocessor = $params->get('currentprocessor', 0);
        $this->totalprocessors = count($this->processors);

        if($this->totalprocessors>0) {
            // Start exporting
            $this->processor();

            // Keep script alive until all processors have finished
            while($this->currentprocessor<$this->totalprocessors) {
                sleep(1);
            }
            ob_start();
        } else {
            $this->setStatus(array('msg'=>'No processors selected to execute!'));
            $this->setStatus(array(
                'msg'=>'<h2>Export Failed</h2><span>No processors selected to execute</span>',
                'totalprogress'=>100,
                'passprogress'=>100,
                'status'=>'failed'
            ));
        }
        $result = 'Export Complete!';
        return $result;
    }

    /**
     * Processor for JiExporters
     */
    function processor() {
        $this->setPaths();
        $jparams = $this->getComponentParams();
        $params = $this->getParams();

        // Progress
        $totalprogress = $params->get('totalprogress', 0);
        $passprogress = $params->get('passprogress', 0);

        /*$this->setStatus(array(
            'msg'=>'EXPORT: Processing is '.$totalprogress.'% complete',
            'totalprogress'=>$totalprogress,
            'pass'=>$passprogress
        ));
        $this->setStatus(array(
            'msg'=>'EXPORT: Pass is '.$passprogress.'% complete',
            'totalprogress'=>$totalprogress,
            'pass'=>$passprogress
        ));*/

        $this->setStatus(array(
            'msg'=>'EXPORT: Loading processor...',
            'totalprogress'=>$totalprogress,
            'pass'=>$passprogress
        ));
        if(isset($this->processors[$this->currentprocessor])) {
            $processor = $this->processors[$this->currentprocessor];
            $processor->setLimit($jparams->get('process_limit', 100));
            $processor->setTmpdir($this->tmpdir);
            $processor->output = $this->outputdir;
            $processor->complete = array($this, 'didCompleteProcessor');
            $processor->pass = array($this, 'didCompletePass');
            require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
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

            $this->setStatus(array('msg'=>'EXPORT: Executing processor'));
            $processor->process();

            // Free up memory
            unset($processor, $globalvars, $key);
            if(function_exists('gc_collect_cycles')) gc_collect_cycles();
        } else {
            $this->didCompleteProcessor();
        }
    }

    /**
     * This function persists the current state and reloads the export script
     * JiProcessors use this function to split up large processing.
     * @param array $data
     */
    public function didCompletePass($data) {
        // Progress
        $totalprogress = $data['totalprogress'];
        $passprogress = $data['passprogress'];

        $params = $this->getParams();
        $params->set('passdata', $data);
        //$params->set('currentprocessor', $this->currentprocessor);
        $params->set('totalprogress', $totalprogress);
        $params->set('passprogress', $passprogress);

        // Update sysfiles
        if(isset($data['sysfiles'])) {
            $sysfiles = $params->get('sysfiles', array());
            $newsysfiles = $data['sysfiles'];
            foreach($newsysfiles as $key=>$file) {
                $sysfiles[$key] = $file;
            }
            $params->set('sysfiles', $sysfiles);
        }

        // Update files
        if(isset($data['files'])) {
            $files = $params->get('files', array());
            $newfiles = $data['files'];
            foreach($newfiles as $key=>$file) {
                $files[$key] = $file;
            }
            $params->set('files', $files);
        }
        $this->setParams($params);

        // Update global values
        if(isset($data['globalvalues'])) {
            $globalvalues = $this->getGlobalValues();
            foreach($data['globalvalues'] as $key=>$value) {
                $globalvalues->set($key, $value);
            }
            $this->setGlobalValues($globalvalues);
        }
        // Wait a bit before starting next step
        //usleep(250);
        $this->setStatus(array(
            'msg'=>'EXPORT: Pass Complete - Total Progress: '.$totalprogress.'%',
            'totalprogress'=>$totalprogress,
            'passprogress'=>$passprogress,
            'status'=>'pass',
            'url'=>'index.php?option=com_jimigrator&view=export&task=doexport'
        ));
        echo 'Pass Complete';
        echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=export&task=doexport' . "';</script>\n";
        exit;
    }

    /**
     * JiProcessors use this function as a callback to signal process completion
     * @param array $data
     */
    public function didCompleteProcessor($data=null) {
        $jparams = $this->getComponentParams();

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
        if($data!=null) {
            // Update sysfiles
            if(isset($data['sysfiles'])) {
                $sysfiles = (array) $params->get('sysfiles', array());
                $newsysfiles = $data['sysfiles'];
                foreach($newsysfiles as $key=>$file) {
                    $sysfiles[$key] = $file;
                }
                $params->set('sysfiles', $sysfiles);
            }

            // Update files
            if(isset($data['files'])) {
                $files = (array) $params->get('files', array());
                $newfiles = $data['files'];
                foreach($newfiles as $key=>$file) {
                    $files[$key] = $file;
                }
                $params->set('files', $files);
            }

            // Update global values
            if(isset($data['globalvalues'])) {
                $globalvalues = $this->getGlobalValues();
                foreach($data['globalvalues'] as $key=>$value) {
                    $globalvalues->set($key, $value);
                }
                $this->setGlobalValues($globalvalues);
            }
        }

        $this->setParams($params);

        // Free up memory
        unset($data, $key, $value);
        if(function_exists('gc_collect_cycles')) gc_collect_cycles();


        if($this->currentprocessor<$this->totalprocessors) {
            // do next processor
            $this->setStatus(array(
                'msg'=>'EXPORT: Finished processor - Total Progress: '.$totalprogress.'%',
                'passprogress'=>100,
                'totalprogress'=>$totalprogress,
                'status'=>'pass',
                'url'=>'index.php?option=com_jimigrator&view=export&task=doexport'
            ));
            echo 'Finished Processor';
            echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=export&task=doexport' . "';</script>\n";
            exit;
        } else {
            // Completed all processors
            $this->setStatus(array(
                'msg'=>'EXPORT: Wrapping up...',
                'totalprogress'=>99,
                'passprogress'=>100
            ));

            $zipfile = $this->outputzipfile;


            if($jparams->get('zipengine', 'native')=='native') {
                $zip = new ZipArchive;
                $zip->open($zipfile, ZipArchive::CREATE);
            } else {
                require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiziparchive.php');
                $zip = new JiZipArchive;
                $zip->open($zipfile, true);
            }

            // Add System Files
            $sysfiles = (array) $params->get('sysfiles', array());
            if(count($sysfiles)>0) {
                foreach($sysfiles as $file) {
                    $filepath = $this->outputdir.DS.$file;
                    if(file_exists($filepath)) {
                        $zip->addFile($filepath, $file);
                        $this->setStatus(array('msg'=>'Added file to Migration ZIP: '.$file));
                    }
                }
            } else {
                $this->setStatus(array('msg'=>'WARNING: No system files found'));
            }

            // Add Site Files
            $files = (array) $params->get('files', array());

            if(count($files)>0) {
                $zip->addEmptyDir('sitefiles');

                $rootparts = explode(DS, JPATH_SITE);
                $rootlevel = count($rootparts);
                foreach($files as $file) {
                    // Ensure files are prefixed correctly
                    $file = ltrim($file, '/');
                    if(substr(JPATH_SITE, 0, 1)=='/') $file = '/'.$file;
                    if(file_exists($file)) {
                        // Find Parent Directories
                        $parentdirs = explode(DS, $file);
                        $total = count($parentdirs);
                        $currentlevel = 1;
                        $currentdir = 'sitefiles/';

                        // Directory separators must be "/" for archives
                        foreach($parentdirs as $key=>$parentdir){
                            $parentdir = trim($parentdir, '\\');
                            $parentdir = trim($parentdir, '/');
                            if($parentdir!='' && $currentlevel>$rootlevel) {
                                if($key<($total-1)) {
                                    // Add Parent Directory
                                    $zip->addEmptyDir($currentdir.$parentdir);
                                    $currentdir.= $parentdir.'/';
                                } elseif(is_dir($file)) {
                                    // Add Directory
                                    $zip->addEmptyDir($currentdir.$parentdir);
                                    $this->setStatus(array('msg'=>'Added directory to Migration ZIP: '.$currentdir.$parentdir));
                                    $currentdir.= $parentdir.'/';
                                } elseif(is_file($file)) {
                                    // Add File
                                    $zip->addFile($file, $currentdir.$parentdir);
                                    $this->setStatus(array('msg'=>'Added file to Migration ZIP: '.$currentdir.$parentdir));
                                }
                            }
                            $currentlevel++;
                        }
                    }
                }
            }

            // Save Zip
            $this->setStatus(array('msg'=>'Saving Migration Archive...'));

            $zip->close();

            // Wait a bit for zip to complete
            sleep(5);

            // Delete tmp CSVs
            if($params->get('purgetmp', false) && count($sysfiles)>0) {
                $this->delTree($this->outputdir);
                $this->setStatus(array('msg'=>'Purged temporary files!'));
            }

            $this->setStatus(array('msg'=>'Export Complete!'));

            $zipsize = (file_exists($zipfile))? $this->human_filesize(filesize($zipfile)) : '0bytes';

            date_default_timezone_set('UTC');
            $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
            $html = '<ul class="jiactions">';
            $html.= '<li><h2>Export Complete!</h2><a class="jibtn tier1action small viewlogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$logfile.'" target="_blank">View Log</a></li>';
            $html.= '<li><a class="jibtn tier1action downloadbtn" href="index.php?option=com_jimigrator&view=export&task=download&dlfile='.$zipfile.'">Download Migration ZIP ('.$zipsize.')</a></li>';
            $html.= '<li><a class="jibtn tier2action deletebtn" href="index.php?option=com_jimigrator&view=export&task=delete&dlfile='.$zipfile.'">Delete Migration ZIP (Make sure download is finished first!)</a></li>';
            $html.= '</ul>';
            $this->setStatus(array(
                'msg'=>$html,
                'status'=>'complete',
                'totalprogress'=>100,
                'passprogress'=>100,
                'logfile'=>$logfile
            ));
        }
    }

    public function getMigration() {
        $this->setPaths();
        $zipfile = $this->outputzipfile;
        if(!file_exists($zipfile)) return false;

        $return = new stdClass();
        $return->path = $zipfile;
        $return->size = $this->human_filesize(filesize($zipfile));
        return $return;
    }

    function human_filesize($bytes, $decimals = 2) {
        if(!isset($bytes) || $bytes==0) return '0';
        try {
            $sz = 'BKMGTP';
            $factor = floor((strlen($bytes) - 1) / 3);
            $result = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
        } catch(ErrorException $e) {
            $this->setStatus(array('msg'=>'Error reading migration file size: '.$e));
            $result = '0';
        }
        return $result;
    }

    /**
     * Deletes a whole directory tree returning true|false
     * @param $dir
     * @return bool
     */
    function delTree($dir) {
        if(!file_exists($dir) || !is_dir($dir)) return false;

        $files = array_diff(scandir($dir), array('.','..'));
        foreach($files as $file) {
            (is_dir($dir.DS.$file)) ? $this->delTree($dir.DS.$file) : unlink($dir.DS.$file);
        }
        return rmdir($dir);
    }

    function deleteFile($filename) {
        if(file_exists($filename)) unlink($filename);
        $returnurl = 'index.php?option=com_jimigrator&view=export';
        header("Location: ".$returnurl);
    }

    function downloadFile($filename) {
        // Get User
        $user = JFactory::getUser();
        $deniedurl = $_SERVER['HTTP_REFERER'];
        
        $fileparts = explode('.', $filename);
        $type = end($fileparts);
        // Check if file exists
        if(!file_exists($filename)) {
            header("Location: ".$deniedurl);
            // Close the script
            exit;
        }
        // Check if file has allowed extension type
        $allowedExtensions = array('zip');
        if(!in_array($type, $allowedExtensions)) {
            header("Location: ".$deniedurl);
            // Close the script
            exit;
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        
        // Suggest better filename for browser to use when saving file:
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Content-Transfer-Encoding: binary');
        // Caching headers:
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        // Send a formatted size of file
        header('Content-Length: '.sprintf('%d', filesize($filename)));
        // Read the file in a way that all browsers understand
        $handle = fopen($filename, 'rb');
        fpassthru($handle);
        fclose($handle);
        
        // Exit before anything is added to the binary
        exit;
    }

    /**
     * Method to get component params
     * @param string $component
     * @return mixed
     */
    function getComponentParams($component='com_jimigrator') {
        jimport('joomla.application.component.helper');
        $params = JComponentHelper::getParams($component);
        return $params;
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
     */
    public function setStatus($status) {
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Status', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Status', 'JiMigratorModel');
        }
        $model->setStatus($status);
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

                date_default_timezone_set('UTC');
                $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
                $this->setStatus(array(
                    'msg'=>'<h2>Export Failed!</h2><a class="jibtn tier1action small viewlogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$logfile.'" target="_blank">View Log</a>',
                    'logfile'=>$logfile,
                    'totalprogress'=>100,
                    'passprogress'=>100,
                    'status'=>'failed'
                ));
                exit;
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
    }
}