<?php 
/**
 * @version     $Id: helper.php 105 2013-07-22 10:38:00Z Anton Wintergerst $
 * @package     JiImageGallery Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.environment.uri');
class plgJiImageGalleryHelper {
	public function dirDRT($rootdir='') {
		$rootdir = str_replace('DS', DS, $rootdir);
        $rootdir = trim($rootdir, DS);
		$rootdir = str_replace('JPATH_SITE', rtrim(JPATH_SITE, DS), $rootdir);
		return $rootdir;
	}
	public function pathDRT($rootpath='') {
		$rootpath = str_replace('DS', '/', $rootpath);
		$rootpath = trim($rootpath, '/');
		$rootpath = str_replace('JURI::root()', rtrim(JURI::root(), '/'), $rootpath);
		return $rootpath;
	}
    private function getParams() {
        // Load Plugin Params
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('content', 'jiimagegallery');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('content', 'jiimagegallery');
            $params = new JParameter($plugin->params);
        }
        return $params;
    }
    public function getHTML($folder) {
    	if($folder!=null) {
            $folder = trim($folder);
            $folder = trim($folder, '/');
        }
        $params = $this->getParams();
        $this->params = $params;
        $pcontext = 'gal';
        
        // Set root directory and path
        if($params->get('advancedroot', 0)==1) {
            $this->rootdir = $this->dirDRT($params->get('dir_root', JPATH_SITE));
            $this->rootpath = $this->pathDRT($params->get('path_root', JURI::root()));
            if(strstr($this->rootdir, '/')!=false) $this->rootdir = '/'.$this->rootdir;
        } else {
            $sourcedirectory = trim($params->get('sourcedirectory', 'images'), DS);
            $this->rootdir = JPATH_SITE.DS.$sourcedirectory;
            $this->rootpath = JURI::root().$sourcedirectory;
        }
        $this->rootdir = rtrim($this->rootdir, '/');
        
        $foldername = ($folder!=null)? strtolower(preg_replace('/[^a-zA-Z0-9\']/', '', $folder)): 'root';
        // Set thumbnail paths
        $this->thumbsdir = JPATH_SITE.DS.'images'.DS.'jithumbs';
		$this->thumbspath = JURI::root().'images/jithumbs';
		
        $this->indexfile = JPATH_SITE.'/images/index.html';
		// Check for JiFramework
        if(!function_exists('jiimport')) return JText::_('PLG_JIIMAGEGALLERY_WARNING_JIFRAMEWORK');
        jiimport('jiimageprocessor');
        $JiImageProcessor = new JiImageProcessor();
        // Check if gallery directory exists
        if(!JFolder::exists($this->rootdir)) {
            $html = ($this->debug==1)? '<div>Warning: JiImageGallery folder does not exist!</div>' : '';
            return $html;
        }
        // Create jithumbs directory if needed
        if(!JFolder::exists($this->thumbsdir)) JFolder::create($this->thumbsdir);
		if($folder!=null && !JFolder::exists($this->thumbsdir.DS.$folder)) JFolder::create($this->thumbsdir.DS.$folder);
        // Ensure index.html exists in each folder
        if(JFile::exists($this->indexfile)) {
            if(!JFile::exists($this->rootdir.DS.'index.html')) JFile::copy($this->indexfile, $this->rootdir.DS.'index.html');
            if(!JFile::exists($this->thumbsdir.DS.'index.html')) JFile::copy($this->indexfile, $this->thumbsdir.DS.'index.html');
			if($folder!=null && !JFile::exists($this->thumbsdir.DS.$folder.DS.'index.html')) JFile::copy($this->indexfile, $this->thumbsdir.DS.$folder.DS.'index.html');
        }
        if($folder!=null && $folder!='') {
			$this->rootdir = $this->rootdir.DS.$folder;
			$this->rootpath = $this->rootpath.'/'.$folder;
			$this->thumbsdir = $this->thumbsdir.DS.$folder;
			$this->thumbspath = $this->thumbspath.'/'.$folder;
            // Check if gallery directory exists
            if(!JFolder::exists($this->rootdir)) {
                $html = ($this->debug==1)? '<div>Warning: JiImageGallery folder does not exist!</div>' : '';
                return $html;
            }
		}
        $allowedExtensions = explode(',', $params->get('images_types', "gif,jpg,jpeg,png"));
        $max = $params->get('max');
        $icount = 0;
        // Build array of files
        $files = array();
        if($handle = opendir($this->rootdir)) {
            while(($file = readdir($handle))!==false) {
                $fparts = explode(".", $file);
                $type = end($fparts);
                if(in_array(strtolower($type), $allowedExtensions)) {
                    $files[$icount] = $file;
                    // Build order
                    $icount++;
                }
            }
            $total = count($files);
			
            if($total) {
                // Set Order
                $ordering = $params->get('ordering', 'alpha');
                if($ordering=='alpha') {
                    sort($files);
                } elseif($ordering=='ralpha') {
                    sort($files);
                    $files = array_reverse($files);
                }
                $icount = 0;
                $html = '<div class="jiimagegallery '.$foldername.'">';
                foreach($files as $file) {
                    // Create Thumbnails
                    $name = current(explode(".", $file));
                    // Show image thumbnail
                    $imageMade = true;
                    if(!JFile::exists($this->thumbsdir.DS.$name.'_'.$pcontext.'.jpg') || $params->get('thumbs_cache', 1)==0) {
                        $imageMade = $JiImageProcessor->resizeImage($this->rootdir.DS.$file, $this->thumbsdir.DS.$name.'_'.$pcontext.'.jpg', $this->params, $type, $pcontext);
                    }
					
                    if($imageMade) {
                        if($max==null || $icount<$max) {
                            $class = 'thumb '.$pcontext.'thumb'.$icount.' '.preg_replace('/[^a-zA-Z0-9\']/', '', $name);
                            if($icount==0) $class.= ' first';
                            if($icount+1==$max || $icount+1==$total) $class.= ' last';
                            
                            $attrs = $params->get($pcontext.'_thumbs_linkattr', 'rel="slimbox-%f"');
                            if($attrs!=null) {
                                $attrs = ' '.$attrs;
                                $attrs = str_replace('%c', ($icount-1), $attrs);
                                $attrs = str_replace('%f', $foldername, $attrs);
                            }
                            $target = $params->get($pcontext.'_thumbs_linktarget', '_blank');
                    
                            $html.= '<div class="'.$class.'">';
                            $html.= '<a href="'.$this->rootpath.'/'.$file.'" title="'.$name.'" target="'.$target.'"'.$attrs.'>';
                            $html.= '<img class="jiimg" src="'.$this->thumbspath.'/'.$name.'_'.$pcontext.'.jpg" alt="'.$name.'" />';
                            $html.= '</a>';
                            $html.= '</div>';
                            $icount++;
                        }
                    }
                }
            }
            closedir($handle);
        }
        $html .= '</div>';
        
        return $html;
    }
}