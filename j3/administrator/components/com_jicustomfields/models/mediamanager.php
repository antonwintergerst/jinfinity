<?php
/**
 * @version     $Id: mediamanager.php 078 2014-12-19 13:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.environment.uri');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'imageprocessor.php');

class JiCustomFieldsModelMediaManager extends JModelLegacy
{
    public function renderInput() {
        $JiField = $this->getJiField();
        $JiField->prepareInput();
        ob_start();
        require_once(JPATH_SITE.'/administrator/components/com_jicustomfields/views/mediamanager/tmpl/default.php');
        $html = ob_get_clean();
        return $html;
    }
    public function getJiField() {
        $JiFieldHelper = new JiCustomFieldHelper();
        $app = JFactory::getApplication();
        $jinput = $app->input;
        // Build Field Data Object
        $data = new stdClass();
        $data->id = $jinput->get('id', null, 'string');
        $data->type = $jinput->get('type', null, 'string');
        $data->name = $jinput->get('name', 'new', 'string');
        // Load Field
        $JiField = $JiFieldHelper->loadType($data);
        return $JiField;
    }
    public function setup() {
        $this->JiField = $this->getJiField();
        $params = $this->JiField->get('params');
        $filetypes = explode(',', $params->get('types', ''));
        $typedata = array();
        if(is_array($filetypes)) {
            foreach($filetypes as $type) {
                if(trim($type)!='') $typedata[] = trim($type);
            }
        }
        $this->filetypes = $typedata;

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $this->searchword = $jinput->get('ffsearchword', null, 'string');
        $this->mediatype = $jinput->get('mediatype', 'files', 'string');
        // The parent field may not have stored its filetypes data yet, lets set the filetype defaults based off the mediatype var
        if(count($this->filetypes)==0) {
            if($this->mediatype=='files') {
            } else if($this->mediatype=='images') {
                $this->filetypes = array('bmp', 'gif', 'jpeg', 'jpg', 'png', 'folder');
            }
        }

        //$jinput = JFactory::getApplication()->input;
        //$filetypes = $jinput->get('filetypes', null, 'string');
        //$this->filetypes = json_decode($filetypes);
        $this->rootdir = rtrim(str_replace('/', DS, JPATH_SITE), DS).DS.'images';
        $this->rootlvl = count(explode(DS, $this->rootdir));

        // Set thumbnail paths
        $this->thumbspath = JURI::root().'images/jithumbs/';
        $this->thumbsdir = JPATH_SITE.DS.'images'.DS.'jithumbs'.DS;
        $this->indexfile = JPATH_SITE.DS.'images'.DS.'index.html';
        // Set icon paths
        $this->iconpath = JURI::root().'media/jiframework/images/';
        $this->icondir = JPATH_SITE.DS.'media'.DS.'jiframework'.DS.'images';

        // Set Paths
        $this->setPaths($jinput->get('ffpath', '', 'raw'));
        $this->excludeDirectories = explode(',', $params->get('exclude_dirs', "jithumbs,jipreviews,thumbs"));
    }
    public function setPaths($data) {
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
    public function sanitizePath($path) {
        // Sanitize path
        $path = str_replace(array('..', './'), '', $path);
        $path = preg_replace('/\/\/+/', '/', $path);
        $path = trim($path, '/');
        return $path;
    }

    /**
     * Method to build crumbs and set parent path
     * @param object $path
     * @return object
     */
    public function processPath($path) {
        $result = new stdClass();
        $parentpath = '';

        $crumbs = array();
        $subpaths = explode(DS, rtrim($path->relpath, DS));
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

    public function open() {
        $this->setup();
        $params = $this->JiField->get('params');
        $return = array();
        $return['id'] = $this->JiField->get('id');
        $return['type'] = $this->JiField->get('type');
        $return['name'] = $this->JiField->get('name');
        $return['mediatype'] = $this->mediatype;
        $return['searchword'] = $this->searchword;

        $response = $this->processPath($this->paths[0]);
        $currentlevel = $response->currentlevel;
        $currentpath = $response->currentpath;
        $parentpath = $response->parentpath;
        $return['crumbs'] = $response->crumbs;

        if(is_dir($this->paths[0]->abspath)) {
            // directory
            $this->itemlist = array();
            $this->sortlist = array();

            if($currentlevel>$this->rootlvl) {
                $rootitem = array(
                    'path'=>$parentpath,
                    'name'=>'..',
                    'type'=>'folder',
                    'root'=>'true'
                );
                $rootitem['icon'] = $this->getIcon($rootitem['type'], $params->get('iconsize', 128));
                $this->itemlist[] = $rootitem;
                $this->sortlist[] = '0';
            }
            if($this->searchword==null) {
                // regular open directory
                $this->openDirectory($this->rootdir.'/'.$currentpath, $currentpath);
            } else {
                // open directory for searching
                $this->openDirectoryRecursive($this->rootdir.'/'.$currentpath, $currentpath);
            }
            array_multisort($this->sortlist, $this->itemlist, SORT_ASC);
            /*$sysitem = array('path'=>$currentpath, 'name'=>'Upload', 'type'=>'system', 'task'=>'upload');
            $this->itemlist[] = $sysitem;
            $this->sortlist[] = '2'.$sysitem['name'];*/

            $return['items'] = $this->itemlist;
            $return['isdir'] = true;
            $return['valid'] = true;
        } else {
            // file
            $filepath = str_replace(JPATH_SITE, '', $this->paths[0]->abspath);
            $filepath = ltrim($filepath, '/');

            $return['file'] = $filepath;
            $return['valid'] = true;
        }
        $return['ffpath'] = rtrim($currentpath, '/');

        return $return;
    }
    public function openDirectory($dir, $currentdir=null) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $params = $this->JiField->get('params');
        if($dh = opendir($this->rootdir.DS.$currentdir)) {
            while (($filename = readdir($dh)) !== false) {
                if($filename!='..' && $filename!='.') {
                    $filepath = $this->rootdir.DS.$currentdir.$filename;
                    $item = array();
                    $item['path'] = $currentdir.$filename;
                    $item['name'] = $filename;
                    $item['state'] = $this->getState($filepath);
                    if(is_dir($filepath)) {
                        // Directory
                        $item['type'] = 'folder';
                        $sorttype = 0;
                        // exclude set directories
                        if(in_array($filename, $this->excludeDirectories)) continue;
                    } elseif(is_file($filepath)) {
                        // File
                        $fparts = explode(DS, $filepath);
                        $flast = end($fparts);
                        $nparts = explode('.', $flast);
                        $type = end($nparts);
                        $name = rtrim($flast, '.'.$type);
                        $item['size'] = $this->human_filesize(filesize($filepath));
                        $item['type'] = $type;
                        $sorttype = 1;
                    }
                    if(count($this->filetypes)==0 || in_array(strtolower($item['type']), $this->filetypes)) {
                        $iconisset = false;
                        if($this->mediatype=='images' && $item['type']!='folder') {
                            // check for thumbnail
                            $iconsize = $params->get('iconsize', 128);
                            $context = 'mm'.$iconsize;
                            $thumb = $currentdir.$name.'_'.$context.'.jpg';
                            if(!JFile::exists($this->thumbsdir.$thumb) || $params->get('thumbs_cache', 1)==0) {
                                $item['loadicon'] = true;
                            } else {
                                $iconisset = true;
                            }
                            if($iconisset) $item['icon'] = $this->thumbspath.$thumb;
                        }
                        if(!$iconisset) $item['icon'] = $this->getIcon($item['type'], $params->get('iconsize', 128));
                        $this->itemlist[] = $item;
                        $this->sortlist[] = $sorttype.$item['name'];
                    }
                }
            }
        }
    }
    public function openDirectoryRecursive($dir, $currentdir) {
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($iterator as $key=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.' && strpos($filename, $this->searchword)!==false) {
                $filepath.= '/'.$filename;
                $relpath = substr($filepath, strlen($this->rootdir));
                $item = array();
                $item['path'] = $relpath;
                $item['name'] = $relpath;
                $item['state'] = $this->getState($filepath);
                if(is_dir($filepath)) {
                    // Directory
                    $item['type'] = 'folder';
                    $sorttype = 0;
                    // exclude set directories
                    if(in_array($filename, $this->excludeDirectories)) continue;
                } else {
                    // File
                    $item['size'] = $this->human_filesize(filesize($filepath));
                    $fileparts = explode('.', $filename);
                    $item['type'] = end($fileparts);
                    $sorttype = 1;
                }
                if(count($this->filetypes)==0 || in_array($item['type'], $this->filetypes)) {
                    $this->itemlist[] = $item;
                    $this->sortlist[] = $sorttype.$item['name'];
                }
            }
        }
    }
    public function loadIcon()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $this->setup();

        $params = $this->JiField->get('params');
        $filepath = $this->paths[0]->relpath;
        $pathparts = explode(DS, $filepath);
        $file = end($pathparts);
        $fileparts = explode('.', $file);
        $filetype = strtolower(end($fileparts));
        $filename = rtrim($file, '.'.$filetype);
        $currentdir = rtrim(rtrim($filepath, $file), DS);
        if($currentdir!='') $currentdir.= DS;

        $iconsize = $params->get('iconsize', 128);
        $context = 'mm'.$iconsize;
        $resizeparams = new JRegistry();
        $resizeparams->set($context.'_thumbs_width', $iconsize);
        $resizeparams->set($context.'_thumbs_height', $iconsize);
        $resizeparams->set($context.'_thumbs_quality', 75);
        $resizeparams->set($context.'_thumbs_keepratio', 1);
        $resizeparams->set($context.'_thumbs_cropcenter', 0);
        $resizeparams->set($context.'_thumbs_fill', 0);
        $resizeparams->set($context.'_thumbs_xcenter', 1);
        $resizeparams->set($context.'_thumbs_ycenter', 1);

        $iconisset = false;
        if(!JFile::exists($this->thumbsdir.$currentdir.$filename.'.jpg') || $params->get('thumbs_cache', 1)==0) {
            $this->checkDirectories($currentdir, $this->indexfile, $this->thumbsdir);
            $imageProcessor = new JiCustomFieldsImageProcessor();
            $imageMade = $imageProcessor->resizeImage($this->paths[0]->abspath, $this->thumbsdir.$currentdir.$filename.'_'.$context.'.jpg', $resizeparams, $filetype, $context, 1);

            if($imageMade!=false) {
                $iconisset = true;
            } else {
                var_dump($imageProcessor->errormsg); die;
            }
        } else {
            $iconisset = true;
        }

        if($iconisset) {
            $response = $this->thumbspath.$currentdir.$filename.'_'.$context.'.jpg';
        } else {
            $response = $this->getIcon($filetype, $params->get('iconsize', 128));
        }
        $return = new stdClass();
        $return->valid = ($response!=null)? true : false;
        $return->e = $jinput->get('e', null, 'raw');
        $return->img = $response;
        return $return;
    }
    public function getIcon($type, $size=16) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $size = 'mimex'.$size;
        $type = strtolower($type);
        if($type=='folder') {
            $iconfile = $this->iconpath.$size.'/folder.png';
        } elseif(JFile::exists($this->icondir.$size.'/'.$type.'.png')) {
            $iconfile = $this->iconpath.$size.'/'.$type.'.png';
        } else {
            $iconfile = $this->iconpath.$size.'/_blank.png';
        }
        return $iconfile;
    }
    public static function checkDirectories($source, $indexfile=null, $thumbsdir=null) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        // Create Thumbnail Directory & index.html file
        if(!JFolder::exists($thumbsdir)) JFolder::create($thumbsdir);
        if(!JFile::exists($thumbsdir.'/index.html') && JFile::exists($indexfile)) JFile::copy($indexfile, $thumbsdir.'/index.html');
        $subdirs = explode('/', $source);
        $crawlpath = '';
        if($indexfile!=null && $thumbsdir!=null) {
            foreach($subdirs as $subdir) {
                $crawlpath.= rtrim($subdir, '/');
                // Set thumbnail paths
                if(!JFolder::exists($thumbsdir.$crawlpath)) JFolder::create($thumbsdir.$crawlpath);
                if(!JFile::exists($thumbsdir.$crawlpath.'/index.html') && JFile::exists($indexfile)) JFile::copy($indexfile, $thumbsdir.$crawlpath.'/index.html');
                $crawlpath.= '/';
            }
        }
    }
    public function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}