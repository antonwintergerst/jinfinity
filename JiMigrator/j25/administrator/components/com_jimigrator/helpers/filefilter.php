<?php
/**
 * @version     $Id: filefilter.php 148 2014-10-26 10:48:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiListFilterFile extends JiListFilter
{
    // TODO: Move this out of construct
    function __construct() {
        $this->rootdir = rtrim(JPATH_SITE, DS);
        $this->rootlvl = count(explode(DS, $this->rootdir));

        // Set Paths
        jimport('joomla.application.component.helper');
        $jparams = JComponentHelper::getParams('com_jimigrator');
        if(version_compare(JVERSION, '2.5.0', 'ge')) {
            $jinput = JFactory::getApplication()->input;
            $this->setPaths($jinput->get('ffpath', '', 'raw'));
            $this->start = $jinput->get('start', 0);
            $this->limit = (int) $jparams->get('list_limit',50);
            $this->reset = $jinput->get('reset', 0);

            $this->params = null;
            $this->searchword = $jinput->get('ffsearchword', null);

            $this->setScope($jinput->get('scope'));
            $this->setName($jinput->get('name'));
        } else {
            $this->setPaths(JRequest::getVar('ffpath'));
            $this->start = JRequest::getVar('start', 0);
            $this->limit = JRequest::getVar('list_limit', 50);
            $this->reset = JRequest::getVar('reset', 0);

            $this->params = null;
            $this->searchword = JRequest::getVar('ffsearchword');

            $this->setScope(JRequest::getVar('scope'));
            $this->setName(JRequest::getVar('name'));
        }
    }

    /**
     * Sets current path/s
     * @param $data
     */
    function setPaths($data) {
        $this->paths = array();
        if(is_array($data)) {
            foreach($data as $datapath) {
                $path = new stdClass();
                $path->relpath = $this->sanitizePath($datapath);
                $path->abspath = ($path->relpath!='')? $this->rootdir.DS.$path->relpath : $this->rootdir;
                $this->paths[] = $path;
            }
        } else {
            $path = new stdClass();
            $path->relpath = $this->sanitizePath($data);
            $path->abspath = ($path->relpath!='')? $this->rootdir.DS.$path->relpath : $this->rootdir;
            $this->paths[] = $path;
        }
    }

    /**
     * Strips path of invalid characters
     * @param $path
     * @return string
     */
    function sanitizePath($path) {
        // Sanitize path
        $path = str_replace(array('..', './'), '', $path);
        $path = preg_replace('/\/\/+/', DS, $path);
        $path = trim($path, DS);
        return $path;
    }

    /**
     * Method to build crumbs and set parent path
     * @param object $path
     * @return object
     */
    function processPath($path) {
        $result = new stdClass();
        $parentpath = '';

        $crumbs = array();
        $subpaths = explode(DS, $path->relpath);
        $total = count($subpaths);
        if($total>0) {
            $relpath = '';
            foreach($subpaths as $key=>$subpath) {
                $relpath.= $subpath;

                $sublvl = count(explode(DS, rtrim($this->rootdir.DS.$path->relpath, DS)));
                // Only add directories deeper than the root directory level
                if($relpath!='' && $sublvl>$this->rootlvl) {
                    $crumb = array('path'=>$relpath, 'name'=>$subpath);
                    $crumbs[] = $crumb;
                }
                // Set parent folder
                if($key==$total-2) $parentpath = $relpath;
                $relpath.= DS;
            }
        }

        $result->currentlevel = count(explode(DS, trim($path->abspath, DS)));
        $result->currentpath = ($path->relpath!='')? $path->relpath.DS : '';
        $result->parentpath = $parentpath;
        $result->crumbs = $crumbs;
        return $result;
    }

    /**
     * Main function used to open file directories
     * @return array|void
     */
    function open() {
        $return = array();
        $return['searchword'] = $this->searchword;

        if($this->reset==1) $this->clearParams();

        // Process Path
        $response = $this->processPath($this->paths[0]);
        $currentlevel = $response->currentlevel;
        $currentpath = $response->currentpath;
        $parentpath = $response->parentpath;
        $return['crumbs'] = $response->crumbs;

        if(is_dir($this->paths[0]->abspath)) {
            $this->itemlist = array();
            $this->sortlist = array();

            if($currentlevel>=$this->rootlvl) {
                $rootitem = array('path'=>$parentpath, 'name'=>'..', 'type'=>'folder', 'root'=>'true');
                $this->itemlist[] = $rootitem;
                $this->sortlist[] = '0';
            }
            if($this->searchword==null) {
                // Regular open directory
                $this->openDirectory($this->rootdir.DS.$currentpath, $currentpath);
            } else {
                // Open directory for searching
                $this->openDirectoryRecursive($this->rootdir.DS.$currentpath, $currentpath);
            }
            array_multisort($this->sortlist, $this->itemlist, SORT_ASC);
            $resetitem = array('path'=>$currentpath, 'name'=>'- Reset Selection -', 'type'=>'system', 'task'=>'reset');
            $this->itemlist[] = $resetitem;
            $this->sortlist[] = '2'.$resetitem['name'];

            $return['items'] = $this->itemlist;
            $return['valid'] = true;
        } else {
            $return['valid'] = false;
        }

        return $return;
    }
    function openDirectory($dir, $currentdir=null) {
        if($dh = opendir($this->rootdir.DS.$currentdir)) {
            while (($filename = readdir($dh)) !== false) {
                if($filename!='..' && $filename!='.') {
                    $filepath = $this->rootdir.DS.$currentdir.$filename;
                    $item = array();
                    $item['path'] = $currentdir.$filename;
                    $item['name'] = $filename;
                    $item['state'] = $this->getState($filepath);
                    if(is_dir($filepath)) {
                        $item['childoverrides'] = $this->getChildStates($filepath, $item['state']);
                        // Directory
                        $item['type'] = 'folder';
                        $sorttype = 0;
                    } elseif(is_file($filepath)) {
                        // File
                        $item['size'] = $this->human_filesize(filesize($filepath));
                        $fileparts = explode('.', $filepath);
                        $item['type'] = end($fileparts);
                        $sorttype = 1;
                    }
                    $this->itemlist[] = $item;
                    $this->sortlist[] = $sorttype.$item['name'];
                }
            }
        }
    }
    function openDirectoryRecursive($dir, $currentdir) {
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach($iterator as $key=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.' && strpos($filename, $this->searchword)!==false) {
                $filepath.= DS.$filename;
                $relpath = substr($filepath, strlen($this->rootdir));
                $item = array();
                $item['path'] = $relpath;
                $item['name'] = $relpath;
                $item['state'] = $this->getState($filepath);
                if(is_dir($filepath)) {
                    // Directory
                    $item['type'] = 'folder';
                    $sorttype = 0;
                } else {
                    // File
                    $item['size'] = $this->human_filesize(filesize($filepath));
                    $fileparts = explode('.', $filename);
                    $item['type'] = end($fileparts);
                    $sorttype = 1;
                }
                $this->itemlist[] = $item;
                $this->sortlist[] = $sorttype.$item['name'];
            }
        }
    }

    /**
     * Gets params for the current field
     * @return array|mixed|null
     */
    function getParams() {
        if($this->params==null) {
            // Setup params directory
            $paramsdir = $this->tmpdir.DS.'params';
            if(!file_exists($paramsdir)) {
                mkdir($paramsdir);
                // Set correct directory permssions
                chmod($paramsdir, 0755);
                // Add index.html
                if(file_exists($this->rootdir.DS.'images'.DS.'index.html')) copy($this->rootdir.DS.'images'.DS.'index.html', $paramsdir.DS.'index.html');
            }
            // Check for existing params
            if(file_exists($this->paramspath)) {
                $params = file_get_contents($this->paramspath);
            } else {
                $params = '';
            }
            $params = json_decode($params, true);
            $this->params = ($params==null || !is_array($params))? array() : $params;
        }
        return $this->params;
    }

    function clearParams() {
        if(file_exists($this->paramspath)) unlink($this->paramspath);
    }

    /**
     * Returns state for a path (Optionally checking parent state inheritance)
     * @param $path
     * @param bool $checkparent
     * @return string
     */
    function getState($path, $checkparent=true) {
        $state = '';
        $this->getParams();
        if(isset($this->params[$this->name][$path])) {
            // Check if file/dir has a state
            $state = $this->params[$this->name][$path];
        } elseif($checkparent) {
            // Check if a parent directory has a state
            $parentdirs = explode(DS, $path);
            $total = count($parentdirs);
            foreach($parentdirs as $key=>$dir) {
                $subpath = implode(DS, array_slice($parentdirs, 0, $total - $key));
                if(isset($this->params[$this->name][$subpath])) {
                    $state = $this->params[$this->name][$subpath];
                    return $state;
                    break;
                }
            }
        }

        return $state;
    }

    /**
     * Returns child states of a directory
     * @param $path
     * @param string $parentstate
     * @return array
     */
    function getChildStates($path, $parentstate='') {
        $included = array();
        $excluded = array();
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach($iterator as $key=>$file) {
            $subpath = $file->getPath();
            $subfilename = $file->getFileName();
            if($subfilename!='..' && $subfilename!='.') {
                $subpath.= DS.$subfilename;
                $state = $this->getState($subpath, false);
                if($state=='include' && $parentstate!='include') {
                    $included[$subpath] = 'include';
                } elseif($state=='exclude' && $parentstate!='exclude') {
                    $excluded[$subpath] = 'exclude';
                }
            }
        }
        $return = array('included'=>count($included), 'excluded'=>count($excluded));
        return $return;
    }

    /**
     * Add a direct inclusion path
     * @return array
     */
    function includePath() {
        $return = array();
        $this->getParams();

        // Add path/s to params
        foreach($this->paths as $path) {
            $this->params[$this->name][$path->abspath] = 'include';
        }

        // Save params
        file_put_contents($this->paramspath, json_encode($this->params));

        $return['valid'] = true;
        return $return;
    }

    /**
     * Add a direct exclusion path
     * @return array
     */
    function excludePath() {
        $return = array();
        $this->getParams();

        // Add path/s to params
        foreach($this->paths as $path) {
            $this->params[$this->name][$path->abspath] = 'exclude';
        }

        // Save params
        file_put_contents($this->paramspath, json_encode($this->params));

        $return['valid'] = true;
        return $return;
    }

    /**
     * Converts bytes to a human readable form
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}