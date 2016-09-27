<?php
/**
 * @version     $Id: snapshots.php 104 2014-07-21 17:19:00Z Anton Wintergerst $
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

class JiMigratorModelSnapshots extends JiModel
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

            // Set snapshots directory
            $snapshotsdir = $this->tmpdir.DS.'snapshots';
            $this->snapshotsdir = $snapshotsdir;

            // Set params directory
            $paramsdir = $this->tmpdir.DS.'params';
            $this->paramsdir = $paramsdir;

            // Create directories as required
            $this->createDirectory(array($tmpdir, $snapshotsdir, $paramsdir));

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

    /**
     * Returns all snapshots in snapshot directory
     * @return array
     */
    public function getSnapshots() {
        $this->setPaths();
        $items = array();
        $sortlist = array();
        // Saved logs
        if(file_exists($this->snapshotsdir)) {
            date_default_timezone_set('UTC');
            if($dh = opendir($this->snapshotsdir)) {
                while(($filename = readdir($dh))!==false) {
                    if($filename!='..' && $filename!='.') {
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        if($ext=='txt') {
                            $name = rtrim($filename, 'jisnapshot.txt');
                            $nameparts = explode('_', $name);
                            if(count($nameparts)==2) {
                                $date = $nameparts[0];
                                $time = date('h:i:s', strtotime($nameparts[1]));
                                $name = $date.' '.$time.' - Database snapshot created: '.$this->getTimePassed($date.' '.$time).' ago';
                                $item = new stdClass();
                                $item->name = $name;
                                $item->viewlink = 'index.php?option=com_jimigrator&view=snapshots&task=show&id='.$filename;
                                $item->restorelink = 'index.php?option=com_jimigrator&view=snapshots&task=restore&id='.basename($filename, '.txt');
                                $items[] = $item;
                                $sortlist[] = date('U', strtotime($date.' '.$time));
                            }
                        }
                    }
                }
            }
            array_multisort($sortlist, SORT_DESC, $items);
        }
        return $items;
    }

    /**
     * Returns snapshot manifest (overview of snapshot)
     * @return string
     */
    public function getSnapshot() {
        $this->setPaths();
        date_default_timezone_set('UTC');
        $snapshotfile = JRequest::getVar('id');
        if($snapshotfile!=null) $snapshotfile = $this->snapshotsdir.DS.$snapshotfile;

        if(file_exists($snapshotfile)) {
            $snapshot = file_get_contents($snapshotfile);
        } else {
            $snapshot = 'No snapshot file found';
        }
        return $snapshot;
    }

    /**
     * Method to create a snapshot
     * @param array|null $tables
     * @param bool $cleanup
     */
    public function saveSnapshot($tables=null, $cleanup=true) {
        $this->setPaths();
        if($tables==null) {
            $db = JFactory::getDBO();
            $tablelist = $db->getTableList();
            $prefix = $db->getPrefix();
            $tables = array();
            foreach($tablelist as $abstable) {
                $table = array(
                    'name'=>substr($abstable, strlen($prefix))
                );
                $tables[] = $table;
            }
        }
        if($cleanup) $this->cleanup();
        $snapshotid = date("Y-m-d_His", strtotime('now')).'jisnapshot';

        $params = $this->getParams();
        $params->set('snapshotid', $snapshotid);
        $params->set('tables', $tables);
        $params->set('passdata', null);
        $this->setParams($params);

        $this->setStatus(array('msg'=>'SNAPSHOT: Backup process started'));

        $this->setStatus(array('msg'=>'SNAPSHOT: Saving to '.$this->snapshotsdir.DS.$params->get('snapshotid')));

        $snapshotsubdir = $this->snapshotsdir.DS.$snapshotid;
        $this->createDirectory($snapshotsubdir);

        $this->dosnapshot();
    }
    function dosnapshot() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();
        // Setup page to stream live output
        if(!isset($this->complete)) {
            header("Content-type: text/html; charset=utf-8");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
            if(ob_get_level() > 0) ob_end_flush();
            echo 'Processing...';
            if(ob_get_level() > 0) {
                flush();
                ob_flush();
            }
        }

        $jparams = $this->getComponentParams();
        $params = $this->getParams();

        $snapshotsubdir = $this->snapshotsdir.DS.$params->get('snapshotid');

        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'dbexporter.php');

        // Set Processor Vars
        $limit = (int) $jparams->get('process_limit', 100);

        // Load the database table processor and set to export dbtable
        $tables = $params->get('tables', array());
        $passdata = new JiMigratorObject($params->get('passdata'));

        $processor = new DBExporter();
        $processor->setLimit($limit);
        $processor->setTmpdir($this->tmpdir);
        $processor->output = $snapshotsubdir;
        $processor->tables = $tables;
        $processor->complete = array($this, 'didCompleteExportProcessor');
        $processor->pass = array($this, 'didCompleteExportPass');
        $processor->data = $passdata;
        // Progress
        $processor->parentprogress = round(($passdata->get('currenttable', 0)/count($tables))*100, 2);
        $processor->totalprocessors = count($tables);

        $processor->process();
    }
    public function didCompleteExportPass($data) {
        if(isset($this->pass)) {
            call_user_func_array($this->pass, array($data));
        } else {
            // Progress
            $totalprogress = $data['totalprogress'];
            $passprogress = $data['passprogress'];

            $params = $this->getParams();
            $params->set('passdata', $data);
            $this->setParams($params);

            $this->setStatus(array(
                'msg'=>'SNAPSHOT: Pass Complete - Total Progress: '.$totalprogress.'%',
                'totalprogress'=>$totalprogress,
                'passprogress'=>$passprogress,
                'status'=>'pass',
                'url'=>'index.php?option=com_jimigrator&view=snapshots&task=dosnapshot'
            ));
            echo 'Pass Complete';
            echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=snapshots&task=dosnapshot' . "';</script>\n";
            exit;
        }
    }
    public function didCompleteExportProcessor($response=null) {
        $this->setPaths();
        $params = $this->getParams();
        // Save snapshot manifest
        $datecreated = str_replace('jisnapshot', '', $params->get('snapshotid'));
        $datecreated = str_replace('_', ' ', $datecreated);
        $manifest = 'JiSnapshot creation time '.$datecreated."\r\n";
        $manifest.= 'Database Tables Included:'."\r\n";
        $tables = $params->get('tables', array());
        foreach($tables as $table) {
            $manifest.= '#__'.$table['name']."\r\n";
        }
        file_put_contents($this->snapshotsdir.'/'.$params->get('snapshotid').'.txt', $manifest);

        if(isset($this->complete)) {
            // Notify parent
            call_user_func($this->complete);
        } else {
            $this->setStatus(array('msg'=>'SNAPSHOT: Backup process complete'));

            date_default_timezone_set('UTC');
            $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
            $result = '<h2>Snapshot created successfully!</h2><a class="jibtn tier1action small viewlogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$logfile.'" target="_blank">View Log</a>';
            $this->setStatus(array(
                'msg'=>$result,
                'status'=>'complete',
                'logfile'=>$logfile,
                'passprogress'=>100,
                'totalprogress'=>100
            ));
            echo $result;
        }
    }

    /**
     * Method to restore a snapshot
     * @return string
     */
    public function restoreSnapshot() {
        $this->setPaths();
        $this->cleanup();
        $params = $this->getParams();

        $this->setStatus(array('msg'=>'SNAPSHOT: Restoring has started'));

        $snapshotid = JRequest::getVar('id');
        if($snapshotid!=null) $snapshotsubdir = $this->snapshotsdir.DS.$snapshotid;

        $this->setStatus(array('msg'=>'SNAPSHOT: Reading from snapshot id: '.$params->get('snapshotid')));

        if(file_exists($snapshotsubdir) && is_dir($snapshotsubdir)) {
            $this->processingcomplete = false;

            $this->dorestore();
            while(!$this->processingcomplete) {
                sleep(1);
            }
        } else {
            $result = 'WARNING: No files found for snapshot. No data was restored';
        }

        return $result;
    }

    public function dorestore() {
        $this->setPaths();
        // Setup page to stream live output
        if(!isset($this->complete)) {
            header("Content-type: text/html; charset=utf-8");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
            if(ob_get_level() > 0) ob_end_flush();
            echo 'Processing...';
            if(ob_get_level() > 0) {
                flush();
                ob_flush();
            }
        }

        $jparams = $this->getComponentParams();
        $params = $this->getParams();

        $snapshotsubdir = $this->snapshotsdir.DS.$params->get('snapshotid');

        $tables = array();
        if($dh = opendir($snapshotsubdir)) {
            while(($filename = readdir($dh))!==false) {
                if($filename!='..' && $filename!='.') {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if($ext=='csv') {
                        $table = array(
                            'name'=>basename($filename, '.csv')
                        );
                        $tables[] = $table;
                    }
                }
            }
        }

        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'dbimporter.php');

        // Set Processor Vars
        $limit = (int) $jparams->get('process_limit', 100);
        $passdata = new JiMigratorObject($params->get('passdata'));

        // Load the database table processor and set to export dbtable
        $processor = new DBImporter();
        $processor->setLimit($limit);
        $processor->setTmpdir($this->tmpdir);
        $processor->setSourcedir($snapshotsubdir);
        $processor->tables = $tables;
        $processor->complete = array($this, 'didCompleteImportProcessor');
        $processor->pass = array($this, 'didCompleteImportPass');
        $processor->data = $passdata;

        // Progress
        $processor->parentprogress = round(($passdata->get('currenttable', 0)/count($tables))*100, 2);
        $processor->totalprocessors = count($tables);

        $processor->process();
    }
    public function didCompleteImportPass($data) {
        if(isset($this->pass)) {
            call_user_func_array($this->pass, array($data));
        } else {
            // Progress
            $totalprogress = $data['totalprogress'];
            $passprogress = $data['passprogress'];

            $params = $this->getParams();
            $params->set('passdata', $data);
            $params->set('totalprogress', $totalprogress);
            $params->set('passprogress', $passprogress);
            $this->setParams($params);

            $this->setStatus(array(
                'msg'=>'SNAPSHOT: Pass Complete - Total Progress: '.$totalprogress.'%',
                'totalprogress'=>$totalprogress,
                'passprogress'=>$passprogress,
                'status'=>'pass',
                'url'=>'index.php?option=com_jimigrator&view=snapshots&task=dorestore'
            ));
            echo 'Pass Complete';
            echo "<script>document.location.href='" . 'index.php?option=com_jimigrator&view=snapshots&task=dorestore' . "';</script>\n";
            exit;
        }
    }
    public function didCompleteImportProcessor($data=null) {
        $this->setStatus(array('msg'=>'SNAPSHOT: Restore completed successfully'));

        if(isset($this->complete)) {
            call_user_func_array($this->complete, array($data));
        } else {
            date_default_timezone_set('UTC');
            $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
            $result = '<h2>Restore completed successfully!</h2><a class="jibtn tier1action small viewlogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$logfile.'" target="_blank">View Log</a>';
            $this->setStatus(array(
                'msg'=>$result,
                'status'=>'complete',
                'logfile'=>$logfile,
                'passprogress'=>100,
                'totalprogress'=>100
            ));
            echo $result;
        }
    }

    public function complete() {
        $this->processingcomplete = true;
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
        $params->set('snapshotid', JRequest::getVar('id'));
        $params->set('passdata', null);
        $params->set('totalprogress', 0);
        $params->set('passprogress', 0);
        $this->setParams($params);
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
     * @return object
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
     * @param object $values
     */
    function setGlobalValues($values) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        $this->setPaths();

        if(!$values instanceof JiMigratorObject) $values = new JiMigratorObject($values);
        file_put_contents($this->paramsdir.DS.'globalvalues.json', $values->toJSON());
    }

    /**
     * Human readable time stamps
     * @param $time
     * @return string
     */
    private function getTimePassed($time) {
        $t1 = date('U', strtotime($time));
        $t2 = date('U', strtotime('now'));
        $t = $t2 - $t1;
        $result = '';
        // days
        $days = floor($t/86400);
        if($days>0) {
            $result.= $days.'d ';
            $t = $t - $days*86400;
        }
        // hours
        $hours = floor($t/3600);
        if($result!='' || $hours>0) {
            $result.= $hours.'h ';
            $t = $t - $hours*3600;
        }
        // minutes
        $minutes = floor($t/60);
        if($result!='' || $minutes>0) {
            $result.= $minutes.'m ';
            $t = $t - $minutes*60;
        }
        // seconds
        $seconds = floor($t);
        $result.= $seconds.'s';

        return $result;
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
        $model->setStatus($status);
    }
}