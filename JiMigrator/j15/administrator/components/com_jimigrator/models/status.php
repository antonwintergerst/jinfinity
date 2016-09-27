<?php 
/**
 * @version     $Id: status.php 080 2014-07-22 22:26:00Z Anton Wintergerst $
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

class JiMigratorModelStatus extends JiModel
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

            $this->tmpstatusfile = $this->tmpdir.DS.'status.txt';

            // Create directories as required
            $this->createDirectory(array($tmpdir));

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
     * Method to set the process status
     * @param $status
     * @param bool $keepalive
     */
    function setStatus($status, $keepalive=true) {
        $this->setPaths();
        /*if($keepalive) {
            ob_start();
            // Keep feeding the browser something to know its all still working
            echo '.';
            if(ob_get_level() > 0) {
                flush();
                ob_flush();
            }
        }*/

        // prevent negative progress values
        if(isset($status['totalprogress']) && $status['totalprogress']<0) $status['totalprogress'] = 1;
        if(isset($status['passprogress']) && $status['passprogress']<0) $status['passprogress'] = 1;

        // Update status
        $fp = fopen($this->tmpstatusfile, "w");
        fwrite($fp, json_encode($status));
        fclose($fp);

        // Save log file
        date_default_timezone_set('UTC');
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $logsModel = JModelLegacy::getInstance('Logs', 'JiMigratorModel');
        } else {
            $logsModel = JModel::getInstance('Logs', 'JiMigratorModel');
        }

        if(isset($status['status']) && ($status['status']=='complete' || $status['status']=='failed')) {
            // Save a copy of this log
            if(isset($status['logfile'])) {
                $logfile = $status['logfile'];
            } else {
                $logfile = date("Y-m-d_His", strtotime('now')).'migratelog.txt';
            }
            $log = '';
            if(file_exists($logsModel->get('tmplogfile'))) $log = file_get_contents($logsModel->get('tmplogfile'));
            $response = $logsModel->setLog($log, $logfile);
            if($response===false) {
                if($status['status']=='complete') {
                    $this->setStatus(array(
                        'msg'=>'Process completed successfully but failed to save log file',
                        'totalprogress'=>100,
                        'passprogress'=>100
                    ));
                } else {
                    $this->setStatus(array(
                        'msg'=>'Process failed and the log file failed to save',
                        'totalprogress'=>100,
                        'passprogress'=>100
                    ));
                }
            }
        } else {
            // Update Log
            $log = date("Y-m-d H:i:s", strtotime('now')).' '.$status['msg']."\r\n";
            $response = $logsModel->setLog($log, null, true);
            if($response===false) {
                $this->setStatus(array('msg'=>'Failed to save log file'));
            }
        }
    }

    /**
     * Method to get the process status
     * @return string
     */
    function getStatus() {
        $this->setPaths();

        if(file_exists($this->tmpstatusfile)) {
            $source = json_decode(file_get_contents($this->tmpstatusfile));
            $status = json_encode($source);
        } else {
            $status = json_encode(array('msg'=>'Please wait...loading processor...'));
        }
        return $status;
    }

    /**
     * Method to remove temporary status and log files
     */
    function cleanup() {
        $this->setPaths();

        if(file_exists($this->tmpstatusfile)) unlink($this->tmpstatusfile);
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $logsModel = JModelLegacy::getInstance('Logs', 'JiMigratorModel');
        } else {
            $logsModel = JModel::getInstance('Logs', 'JiMigratorModel');
        }
        if(file_exists($logsModel->get('tmplogfile'))) unlink($logsModel->get('tmplogfile'));
    }
}