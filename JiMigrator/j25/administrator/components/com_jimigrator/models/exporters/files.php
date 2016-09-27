<?php 
/**
 * @version     $Id: files.php 099 2013-09-18 14:55:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'jiprocessor.php');

class FilesExporter extends JiProcessor {
    /* >>> PRO >>> */
    protected $directinclude;
    protected $inheritedinclude;
    protected $directexclude;
    protected $inheritedexclude;
    function process() {
        $this->setStatus(array('msg'=>'Exporting Files'));
        $files = array();

        if($this->params->get('selective', 0)==1) {
            $filter = $this->params->get('filter', array());
            if(count($filter)>0) {
                $this->setStatus(array('msg'=>'Processing Selective Export'));
                // Selective Export
                $files = $this->selective($filter);
            }
        } else {
            // Complete Export
            $this->setStatus(array('msg'=>'Processing Complete Export'));
            $files = $this->processor();
        }

        if(count($files)>0) {
            $this->setStatus(array('msg'=>'Total directories and files included: '.count($files)));
        } else {
            $this->setStatus(array('msg'=>'No files found!'));
        }

        $data = array(
            'files'=>$files,
            'totalprogress'=>$this->totalprogress,
            'passprogress'=>$this->passprogress
        );
        call_user_func_array($this->complete, array($data));
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
     * Export all directories and files
     * @return array
     */
    function processor() {
        $files = array();
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(JPATH_SITE), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach($iterator as $key=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.') {
                $filepath.= DS.$filename;
                $files[] = $filepath;
            }
        }
        return $files;
    }

    /**
     * Selective Export as set by the file filter
     * @param $filter
     * @return array
     */
    function selective($filter) {
        $this->directinclude = array();
        $this->inheritedinclude = array();
        $this->directexclude = array();
        $this->inheritedexclude = array();
        
        foreach($filter as $file=>$state) {
            if(is_dir($file)) {
                $this->addDirectory($file, $state);
                $this->setStatus(array('msg'=>'Processed directory: '.$file));
            } elseif(is_file($file)) {
                $this->addFile($file, $state);
                $this->setStatus(array('msg'=>'Processed file: '.$file));
            }
        }
        $files = array();
        // Inherited inclusions
        foreach($this->inheritedinclude as $file=>$dummy) {
            $files[$file] = 1;
        }
        // Direct exclusions
        foreach($this->directexclude as $file=>$dummy) {
            unset($files[$file]);
        }
        // Inherited exclusions
        foreach($this->inheritedexclude as $file=>$dummy) {
            unset($files[$file]);
        }
        // Direct inclusions
        foreach($this->directinclude as $file=>$dummy) {
            $files[$file] = 1;
        }
        
        if(count($files)>0) {
            ksort($files);
            $files = array_keys($files);
        }
        return $files;
    }

    /**
     * @param $filepath
     * @param string $mode
     */
    function addFile($filepath, $mode='include') {
        if($mode=='include') {
            $this->directinclude[$filepath] = 1;
        } else {
            $this->directexclude[$filepath] = 1;
        }
    }

    /**
     * @param $dir
     * @param string $mode
     */
    function addDirectory($dir, $mode='include') {
        if($mode=='include') {
            $this->directinclude[$dir] = 1;
        } else {
            $this->directexclude[$dir] = 1;
        }
        
        $iterator =  new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach($iterator as $key=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.') {
                $filepath = $this->sanitizePath($filepath);
                $filepath.= DS.$filename;
                if($mode=='include') {
                    $this->inheritedinclude[$filepath] = 1;
                } else {
                    $this->inheritedexclude[$filepath] = 1;
                }
            }
        }
    }
    /* <<< PRO <<< */
}