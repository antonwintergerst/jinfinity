<?php 
/**
 * @version     $Id: status.php 077 2014-07-21 17:19:00Z Anton Wintergerst $
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

class JiMigratorModelLogs extends JiModel
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

            // Set logs directory
            $logsdir = $this->tmpdir.DS.'logs';
            $this->logsdir = $logsdir;
            $this->tmplogfile = $this->tmpdir.DS.'tmplog.txt';

            // Create directories as required
            $this->createDirectory(array($tmpdir, $logsdir));

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

    function get($var, $default=null) {
        switch($var) {
            case 'rootdir':
            case 'tpmdir':
            case 'tmplogfile':
            case 'logsdir':
                $this->setPaths();
                break;
            default:
                break;
        }
        $return = (isset($this->{$var}))? $this->{$var} : $default;
        return $return;
    }

    /**
     * Method to retrieve an array of log files
     * @return array
     */
    public function getLogs() {
        $this->setPaths();

		$items = array();
        $sortlist = array();
        // Include tmp log
        if(file_exists($this->tmplogfile)) {
            $item = new stdClass();
            $item->name = '-- Temporary Log --';
            $item->link = 'index.php?option=com_jimigrator&view=logs&task=showlog';
            $items[] = $item;
            $sortlist[] = 0;
        }
        // Saved logs
        if(file_exists($this->logsdir)) {
            date_default_timezone_set('UTC');
            if($dh = opendir($this->logsdir)) {
                while(($filename = readdir($dh))!==false) {
                    if($filename!='..' && $filename!='.') {
                        $name = rtrim($filename, 'migratelog.txt');
                        $nameparts = explode('_', $name);
                        if(count($nameparts)==2) {
                            $date = $nameparts[0];
                            $time = date('h:i:s', strtotime($nameparts[1]));
                            $name = $date.' '.$time.' - Migration log created: '.$this->getTimePassed($date.' '.$time).' ago (Click to view this log)';
                            $item = new stdClass();
                            $item->name = $name;
                            $item->link = 'index.php?option=com_jimigrator&view=logs&task=showlog&logfile='.$filename;
                            $items[] = $item;
                            $sortlist[] = date('U', strtotime($date.' '.$time));
                        }
                    }
                }
            }
            array_multisort($sortlist, SORT_DESC, $items);
        }
		return $items;
	}

    /**
     * Method to retrieve a log file
     * @return string
     */
    public function getLog() {
        $this->setPaths();

        $logfile = JRequest::getVar('logfile');
        if($logfile!=null) {
            $logfile = $this->logsdir.'/'.$logfile;
        } else {
            $logfile = $this->tmplogfile;
        }
        if(file_exists($logfile)) {
            $log = file_get_contents($logfile);
        } else {
            $log = 'No log file found';
        }
        return $log;
    }

    /**
     * Method to store a log file
     * @param string $log
     * @param string|null $logfile Defaults to tmplogfile
     * @param bool $append
     * @return bool
     */
    public function setLog($log, $logfile=null, $append=false) {
        $this->setPaths();
        if($logfile!=null) {
            // Check logs directory was created
            if(!file_exists($this->logsdir) || !is_dir($this->logsdir)) return false;
            $logfile = $this->logsdir.DS.$logfile;
        } else {
            $logfile = $this->tmplogfile;
        }
        try {
            if($append) {
                $response = file_put_contents($logfile, $log, FILE_APPEND);
            } else {
                $response = file_put_contents($logfile, $log);
            }
            if($response!==false) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * Method to create human readable time stamps
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
}