<?php
/**
 * @version     $Id: helper.php 065 2014-12-19 13:00:00Z Anton Wintergerst $
 * @package     JiMediaBrowser Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.environment.uri');

class plgJiMediaBrowserHelper {
    public function get($var, $default=null) {
        switch($var) {
            case 'id':
                return $this->getId();
                break;
            case 'name':
                return $this->getName();
                break;
            case 'params':
                return $this->getParams();
                break;
            case 'currenturl':
                return $this->getCurrentURL();
                break;
            case 'debug':
                return $this->getDebug();
                break;
            default:
                if(isset($this->{$var})) {
                    return $this->{$var};
                } else {
                    return $default;
                }
                break;
        }
    }
    protected function getDebug() {
        if(isset($this->debug)) {
            return $this->debug;
        } else {
            return 0;
        }
    }
    protected function getId() {
        if($this->get('currentfolder')!=null) {
            $foldervar = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "",$this->get('currentfolder')));
        } else {
            $foldervar = 'folder';
        }
        return $foldervar;
    }
    protected function getName() {
        if(isset($this->name)) {
            return $this->name;
        } else {
            return 'jimb';
        }
    }
    protected function getCurrentURL() {
        $uri = JURI::getInstance();
        $uri->delVar('mbtask');
        $uri->delVar($this->get('id'));
        $url = $uri->toString(array('query', 'fragment'));
        return $url;
    }
    protected function getParams() {
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('content', 'jimediabrowser');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $plugin = &JPluginHelper::getPlugin('content', 'jimediabrowser');
            $params = new JParameter($plugin->params);
        }
        return $params;
    }
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
    function sanitizePath($path) {
        // Sanitize path
        $path = str_replace(array('..', './'), '', $path);
        $path = preg_replace('/\/\/+/', '/', $path);
        $path = trim($path, '/');
        return $path;
    }
    public function setState() {
        $params = $this->getParams();
        $app = JFactory::getApplication();
        $jinput = $app->input;

        // Set Paramaters
        $this->includeExtensions = explode(',', $params->get('include_exts', "7z,avi,bmp,doc,docx,eps,fla,gif,jpg,jpeg,midi,mkv,mov,mp4,mpeg,mpg,one,otf,pdf,png,ppt,pptx,psd,pub,rar,tiff,txt,xls,xlsx,wav,wma,wmv,xps,zip"));
        $this->imageExtensions = explode(',', $params->get('image_exts', "gif,jpg,jpeg,png"));
        $this->convertExtensions = explode(',', $params->get('convert_exts', "pdf"));
        $this->excludeDirectories = explode(',', $params->get('exclude_dirs', "jithumbs,jipreviews,thumbs"));

        // Set icon path
        $this->iconpath = 'media/jiframework/images';
        $this->icondir = JPATH_SITE.DS.'media'.DS.'jiframework'.DS.'images';
        $this->indexfile = JPATH_SITE.DS.'images'.DS.'index.html';
        // Set thumbnail paths
        $this->thumbspath = 'images/jithumbs';
        $this->thumbsdir = JPATH_SITE.DS.'images'.DS.'jithumbs';
        // Set preview paths
        $this->previewspath = 'images/jipreviews';
        $this->previewsdir = JPATH_SITE.DS.'images'.DS.'jipreviews';

        $this->limit = $params->get('limit', 10);
        $this->start = $jinput->get('mbstart', 0);
        $this->searchword = $jinput->get('mbsearchword', null, 'raw');

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
        $this->rootdir = trim($this->rootdir, DS);
        if(substr(JPATH_SITE, 0, 1)==DS) $this->rootdir = DS.$this->rootdir;
        $this->rootlvl = count(explode(DS, trim($this->rootdir, DS)));

        // Set subpath
        $mbpath = $jinput->get('mbpath', null, 'raw');
        $mbid = $this->get('id');
        $urlcurrentfolder = $jinput->get($mbid, null, 'raw');

        if($mbpath=='_home') $mbpath = '/';

        if($mbpath!=null) {
            $this->currentsubpath = $mbpath;
        } elseif($urlcurrentfolder!=null) {
            $this->currentsubpath = rtrim(html_entity_decode($urlcurrentfolder), '/');
        }

        $relpath = '';
        $abspath = $this->rootdir;
        if($this->get('currentfolder')!=null) {
            $this->rootlvl = count(explode(DS, trim($this->rootdir, DS).DS.trim($this->get('currentfolder'), DS)));
            $abspath.= DS.trim($this->sanitizePath($this->get('currentfolder')), DS);
        }
        if($this->get('currentsubpath')!=null) {
            $relpath = $this->get('currentsubpath');
        }
        $currentpath = new stdClass();
        $currentpath->relpath = $this->sanitizePath($relpath);
        $currentpath->abspath = ($currentpath->relpath!='')? $abspath.DS.$currentpath->relpath : $abspath;
        $this->currentpath = $currentpath;
        if($this->get('debug')==1) {
            print_r('<div>JiMediaBrowser Debug: rootdir: '.$this->get('rootdir').' [EXISTS='.file_exists($this->rootdir).']</div>');
            print_r('<div>JiMediaBrowser Debug: rootpath: '.$this->get('rootpath').'</div>');
            print_r('<div>JiMediaBrowser Debug: currentfolder: '.$this->get('currentfolder').'</div>');
            print_r('<div>JiMediaBrowser Debug: currentpath: '.$currentpath->abspath.' [EXISTS='.file_exists($currentpath->abspath).']</div>');
            print_r('<div>JiMediaBrowser Debug: rootlvl: '.$this->rootlvl.'</div>');
        }
    }
    public function open() {
        $params = $this->getParams();
        $this->setState();
        $currentpath = $this->currentpath;

        $return = array();
        $return['id'] = $this->get('id');
        $return['name'] = $this->get('name');
        $return['folder'] = $this->get('currentfolder');
        if(isset($this->mediatype)) $return['mediatype'] = $this->mediatype;
        $return['searchword'] = $this->searchword;

        // Set url for browser history
        $currenturl = $this->get('currenturl');
        $activepath = ($currentpath->relpath!='')? str_replace('/', '%2F',htmlspecialchars($currentpath->relpath)) : '_home';
        $return['path'] = $activepath;
        if($currentpath->relpath!='') {
            if(strstr($currenturl, '?')===false) $currenturl.= '?';
            $return['url'] = $currenturl.'&'.$this->get('id').'='.$activepath;
        } else {
            $return['url'] = $currenturl;
        }


        // Build Breadcrumbs
        $crumbs = array();

        $subfolders = explode(DS, trim($currentpath->relpath, DS));
        $parentfolder = '_home';
        if(count($subfolders)>0) {
            $relpath = '';
            foreach($subfolders as $key=>$subfolder) {
                $relpath.= $subfolder;
                $sublvl = count(explode('/', rtrim($currentpath->abspath, '/')));
                // Only add directories higher than the root directory level
                if($sublvl>$this->rootlvl+1) {
                    $crumb = array('path'=>$relpath, 'name'=>$subfolder);
                    $crumbs[] = $crumb;
                }
                // Set parent folder
                if($key==count($subfolders)-2) $parentfolder = $relpath;
                $relpath.= '/';
            }
        }
        $return['crumbs'] = $crumbs;
        $this->currentlevel = count(explode(DS, trim($currentpath->abspath, DS)));
        if($this->get('debug')==1) print_r('<div>JiMediaBrowser Debug: Current Level: '.$this->currentlevel.'</div>');

        if(is_dir($currentpath->abspath)) {
            // Directory
            $this->itemlist = array();
            $this->sortlist = array();

            if($this->currentlevel>$this->rootlvl) {
                $rootitem = array(
                    'path'=>trim($parentfolder, DS),
                    'name'=>'..',
                    'type'=>'folder',
                    'root'=>'true'
                );
                $this->itemlist[] = $rootitem;
                $this->sortlist[] = '0';
            }
            if($this->searchword==null) {
                // Regular open directory
                $this->openDirectory($currentpath->abspath, $currentpath->relpath);
            } else {
                // Open directory for searching
                $path = $this->rootdir;
                if($this->get('currentfolder')!=null) $path.= DS.trim($this->sanitizePath($this->get('currentfolder')), DS);
                $this->searchDirectory($path);
            }
            if($this->get('debug')==1) print_r('<div>JiMediaBrowser Debug: found: '.count($this->itemlist).' items');

            array_multisort($this->sortlist, $this->itemlist, SORT_ASC);

            // Only process those in range
            $items = array();
            $i = 0;
            $hasmore = false;
            foreach($this->itemlist as $item) {
                if($this->limit==0 || ($i>=$this->start && $i<$this->start+$this->limit)) {
                    $this->prepareFileItem($item);

                    if(strtolower($item['type'])=='folder') {
                        // Child previews
                        if(isset($item['children'])) {
                            foreach($item['children'] as &$child) {
                                $this->prepareFileItem($child);
                            }
                        }
                    } else {
                        // Download link for files
                        $url = '?&mbid='.$this->get('id');
                        if($this->get('currentfolder')!=null) {
                            $url.= '&'.$this->get('id').'='.$this->get('currentfolder');
                        }
                        if($item['dir']!='') $url.= DS.$item['dir'];

                        $url.= '&mbfile='.$item['name'].'.'.$item['type'];
                        $item['dllink'] = $url;
                    }

                    $items[] = $item;
                }
                if($this->limit>0 && $i>=$this->start+$this->limit) {
                    $hasmore = true;
                    break;
                }
                $i++;
            }
            if($hasmore) {
                $moreitem = array(
                    'path'=>$relpath,
                    'name'=>'Click to load more...',
                    'type'=>'more',
                    'start'=>($this->start + $this->limit)
                );
                if($this->searchword!=null) $moreitem['searchmore'] = true;
                $items[] = $moreitem;
            }

            $return['items'] = $items;
            $return['isdir'] = true;
            $return['valid'] = true;
        } elseif(is_file($currentpath->abspath)) {
            // File
            $filepath = str_replace(JPATH_SITE, '', $currentpath->abspath);
            $filepath = ltrim($filepath, '/');

            //$return['file'] = $filepath;
            $return['valid'] = true;
        } else {
            //$return['debug'] = $currentpath->abspath;
            $return['valid'] = false;
        }
        return $return;
    }
    public function prepareFileItem(&$item) {
        $params = $this->getParams();
        $currentpath = $this->currentpath;

        $iconisset = false;

        // Create image thumbnails for icons
        if(in_array(strtolower($item['type']), $this->imageExtensions) || in_array(strtolower($item['type']), $this->convertExtensions)) {
            // Get Thumbnail
            $iconsize = $params->get('iconsize', 128);
            $context = 'mb'.$iconsize;

            $fullreldir = '';
            $fullrelpath = '';
            if($this->get('currentfolder')!=null) {
                $fullreldir.= DS.$this->get('currentfolder');
                $fullrelpath.= '/'.$this->get('currentfolder');
            }
            $fullreldir.= DS.trim($item['path'], DS);
            $fullrelpath.= '/'.trim($item['path'], DS);

            $iconpath = str_replace('.'.$item['type'], '_'.$context.'.jpg', $fullrelpath);
            $iconfile = str_replace('.'.$item['type'], '_'.$context.'.jpg', $this->thumbsdir.$fullreldir);
            if(!JFile::exists($iconfile) || $params->get('thumbs_cache', 0)==0) {
                $item['loadicon'] = true;
            } else {
                $iconisset = true;
            }
            if($iconisset) $item['icon'] = $this->thumbspath.$iconpath;


            // Image Previews
            $previewisset = false;
            $context = 'pre';
            $previewpath = str_replace('.'.$item['type'], '_'.$context.'.jpg', $fullrelpath);
            $previewfile = str_replace('.'.$item['type'], '_'.$context.'.jpg', $this->previewsdir.$fullreldir);
            if(JFile::exists($previewfile) && $params->get('images_cache', 0)==1) {
                $item['preview'] = 'images/jipreviews/'.ltrim($previewpath, '/');
            } else {
                $item['loadpreview'] = true;
                //$item['preview'] = $currentpath->relpath.$fullrelpath;
            }
        }
        if(!$iconisset) $item['icon'] = $this->getIcon($item['type'], $params->get('iconsize', 128));

        // PDF Previews
        if($item['type']=='pdf') {

        }
    }
    public function createThumbnail($path) {
        $params = $this->getParams();
        $this->debug = $params->get('debug', 0);

        $return = new stdClass();
        $return->valid = false;
        $return->img = null;

        $nparts = explode('.', $path);
        $type = end($nparts);

        $params = $this->get('params');
        // Get Thumbnail
        $iconsize = $params->get('iconsize', 128);
        $context = 'mb'.$iconsize;

        $fullreldir = '';
        $fullrelpath = '';
        if($this->get('currentfolder')!=null) {
            $fullreldir.= DS.$this->get('currentfolder');
            $fullrelpath.= '/'.$this->get('currentfolder');
        }
        $fullreldir.= DS.trim($path, DS);
        $fullrelpath.= '/'.trim($path, DS);

        $iconpath = str_replace('.'.$type, '_'.$context.'.jpg', $fullrelpath);
        $iconfile = str_replace('.'.$type, '_'.$context.'.jpg', $this->thumbsdir.$fullreldir);
        if($this->get('currentfolder')!=null) {
            $source = $this->rootdir.DS.$this->get('currentfolder').DS.trim($path, DS);
        } else {
            $source = $this->rootdir.DS.trim($path, DS);
        }
        $convert = false;
        if(in_array(strtolower($type), $this->convertExtensions)) {
            if(!JFile::exists($iconfile) || $params->get('thumbs_cache', 0)==0) {
                // check for imagemagick
                exec("convert -version", $out, $rcode);
                if($rcode!==0) {
                    if($this->get('debug')==1) $return->debug = 'ImageMagick could not be found and is required to convert this file.';
                } else {
                    $this->checkDirectories(dirname($fullreldir), $this->indexfile, $this->thumbsdir);

                    $scratch = str_replace('.jpg', '_scratch.jpg', $iconfile);
                    exec('convert "'.$source.'"[0] -colorspace RGB -geometry "1024x1024" "'.$scratch.'"');
                    if(file_exists($scratch)) {
                        $source = $scratch;
                        $convert = true;
                    } elseif($this->get('debug')==1) {
                        $return->debug = 'Failed to convert file: '.$source;
                    }
                }
            }
        }
        $iconisset = false;
        if(in_array(strtolower($type), $this->imageExtensions) || $convert) {
            if(!JFile::exists($iconfile) || $params->get('thumbs_cache', 0)==0 || $convert) {
                $this->checkDirectories(dirname($fullreldir), $this->indexfile, $this->thumbsdir);

                //if($this->get('debug')==1) print_r('<div>JiMediaBrowser Debug: Creating thumbnail for '.$source.'</div>');

                jiimport('jiimageprocessor');
                $JiImageProcessor = new JiImageProcessor();
                $imageMade = $JiImageProcessor->resizeImage($source, $iconfile, $params, $type, 'ico', $this->get('debug')==1);
                if($imageMade!=false) {
                    $iconisset = true;
                } elseif($this->get('debug')==1) {
                    $return->debug = $JiImageProcessor->errormsg;
                }
            } else {
                $iconisset = true;
            }
        }
        if($iconisset) {
            $return->valid = true;
            $return->img = $this->thumbspath.$iconpath;
        }
        return $return;
    }
    public function createPreview($path)
    {
        $params = $this->getParams();
        $this->debug = $params->get('debug', 0);

        $return = new stdClass();
        $return->valid = false;
        $return->img = null;

        $nparts = explode('.', $path);
        $type = end($nparts);

        $params = $this->get('params');
        // Get Preview
        $context = 'pre';

        $fullreldir = '';
        $fullrelpath = '';
        if($this->get('currentfolder')!=null) {
            $fullreldir.= DS.$this->get('currentfolder');
            $fullrelpath.= '/'.$this->get('currentfolder');
        }
        $fullreldir.= DS.trim($path, DS);
        $fullrelpath.= '/'.trim($path, DS);

        $previewpath = str_replace('.'.$type, '_'.$context.'.jpg', $fullrelpath);
        $iconfile = str_replace('.'.$type, '_'.$context.'.jpg', $this->previewsdir.$fullreldir);
        if($this->get('currentfolder')!=null) {
            $source = $this->rootdir.DS.$this->get('currentfolder').DS.trim($path, DS);
        } else {
            $source = $this->rootdir.DS.trim($path, DS);
        }
        $convert = false;
        if(in_array(strtolower($type), $this->convertExtensions)) {
            if(!JFile::exists($iconfile) || $params->get('thumbs_cache', 0)==0) {
                // check for imagemagick
                exec("convert -version", $out, $rcode);
                if($rcode!==0) {
                    if($this->get('debug')==1) $return->debug = 'ImageMagick could not be found and is required to convert this file.';
                } else {
                    $this->checkDirectories(dirname($fullreldir), $this->indexfile, $this->previewsdir);

                    $scratch = str_replace('.jpg', '_scratch.jpg', $iconfile);
                    exec('convert "'.$source.'"[0] -colorspace RGB -geometry "1024x1024" "'.$scratch.'"', $out, $error);

                    if(file_exists($scratch)) {
                        $source = $scratch;
                        $convert = true;
                    } elseif($this->get('debug')==1) {
                        $return->debug = 'Failed to convert file: '.$source.', ecode: '.$error;
                    }
                }
            }
        }
        $iconisset = false;
        if(in_array(strtolower($type), $this->imageExtensions) || $convert) {
            if(!JFile::exists($iconfile) || $params->get('images_cache', 0)==0 || $convert) {
                $this->checkDirectories(dirname($fullreldir), $this->indexfile, $this->previewsdir);
                //if($this->get('debug')==1) print_r('<div>JiMediaBrowser Debug: Creating preview for '.$source.'</div>');

                jiimport('jiimageprocessor');
                $JiImageProcessor = new JiImageProcessor();
                $imageMade = $JiImageProcessor->resizeImage($source, $iconfile, $params, $type, $context, $this->get('debug')==1);
                if($imageMade!=false) {
                    $iconisset = true;
                } elseif($this->get('debug')==1) {
                    $return->debug = $JiImageProcessor->errormsg;
                }
            } else {
                $iconisset = true;
            }
        }
        if($iconisset) {
            $return->valid = true;
            $return->img = $this->previewspath.$previewpath;
        }
        return $return;
    }
    public function openDirectory($abspath, $relpath='') {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $params = $this->get('params');
        if($this->get('debug')==1) print_r('<div>JiMediaBrowser Debug: Opening: '.$abspath.' [EXISTS='.file_exists($abspath).']</div>');

        $total = 0;
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($abspath), RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($iterator as $filename=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.') {
                $filepath.= DS.$filename;
                //$relpath = substr($filepath, strlen($this->rootdir));
                $item = array();
                $item['path'] = $filename;
                // prevent unnecessary slash
                if($relpath) $item['path'] = $relpath.DS.$item['path'];

                $item['dir'] = trim($relpath, DS);
                $item['name'] = $filename;
                $item['level'] = count(explode(DS, trim($file->getPath().DS.$filename, DS)));

                if($item['level']==$this->currentlevel+1) {
                    if(is_dir($filepath)) {
                        // Directory
                        $item['type'] = 'folder';
                        $sorttype = 0;
                        // Exclude set directories
                        if(in_array($filename, $this->excludeDirectories)) continue;
                        // Add some children
                        if($iterator->hasChildren()) {
                            $children = $iterator->getChildren();
                            $subitems = array();
                            $i = 0;
                            foreach($children as $child) {
                                if(count($subitems)<3) {
                                    $subitem = array();
                                    $childfilename = $child->getFileName();
                                    $subitem['path'] = $relpath.DS.$filename.DS.$childfilename;
                                    if($child->isFile()) {
                                        $cparts = explode(".", $childfilename);
                                        $ctype = end($cparts);
                                        $subitem['name'] = basename($childfilename, '.'.$ctype);
                                        $subitem['type'] = $ctype;
                                    } else if($childfilename!='.' && $childfilename!='..') {
                                        $subitem['name'] = $childfilename;
                                        $subitem['type'] = 'folder';
                                    }
                                    if(isset($subitem['type'])) {
                                        if(in_array(strtolower($subitem['type']), $this->includeExtensions)) $subitems[] = $subitem;
                                    }
                                }
                            }
                            $item['children'] = $subitems;
                        }
                    } else {
                        // File
                        $nparts = explode('.', $filename);
                        $type = end($nparts);
                        $item['name'] = basename($filename, '.'.$type);
                        $item['size'] = $this->human_filesize(filesize($filepath));
                        $item['type'] = $type;
                        $sorttype = 1;
                    }
                    if(count($this->includeExtensions)==0 || in_array(strtolower($item['type']), $this->includeExtensions)) {
                        $this->itemlist[] = $item;
                        $this->sortlist[] = $sorttype.$item['name'];
                    }
                }
            }
        }
    }
    public function searchDirectory($path) {
        $iterator =  new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($iterator as $key=>$file) {
            $filepath = $file->getPath();
            $filename = $file->getFileName();
            if($filename!='..' && $filename!='.' && $this->searchword!=null && strpos(strtolower($filename), strtolower($this->searchword))!==false) {
                $filepath.= '/'.$filename;
                $relpath = substr($filepath, strlen($path));
                $item = array();
                $item['path'] = $relpath;
                $item['dir'] = trim(substr($relpath, 0, -strlen($filename)), DS);
                $item['name'] = $relpath;
                if(is_dir($filepath)) {
                    // Directory
                    $item['type'] = 'folder';
                    $sorttype = 0;
                    // Exclude set directories
                    if(in_array($filename, $this->excludeDirectories)) continue;
                    // Add some children
                    if($iterator->hasChildren()) {
                        $children = $iterator->getChildren();
                        $subitems = array();
                        $i = 0;
                        foreach($children as $child) {
                            if(count($subitems)<3) {
                                $subitem = array();
                                $childfilename = $child->getFileName();
                                $subitem['path'] = $relpath.DS.$filename.DS.$childfilename;
                                if($child->isFile()) {
                                    $cparts = explode(".", $childfilename);
                                    $ctype = end($cparts);
                                    $subitem['name'] = basename($childfilename, '.'.$ctype);
                                    $subitem['type'] = $ctype;
                                } else if($childfilename!='.' && $childfilename!='..') {
                                    $subitem['name'] = $childfilename;
                                    $subitem['type'] = 'folder';
                                }
                                if($subitem['type']!=null) {
                                    if(in_array(strtolower($subitem['type']), $this->includeExtensions)) $subitems[] = $subitem;
                                }
                            }
                        }
                        $item['children'] = $subitems;
                    }
                } else {
                    // File
                    $nparts = explode('.', $filename);
                    $type = end($nparts);
                    $item['name'] = basename($filename, '.'.$type);
                    $item['size'] = $this->human_filesize(filesize($filepath));
                    $item['type'] = $type;
                    $sorttype = 1;
                }
                if(count($this->includeExtensions)==0 || in_array(strtolower($item['type']), $this->includeExtensions)) {
                    $this->itemlist[] = $item;
                    $this->sortlist[] = $sorttype.$item['name'];
                }
            }
        }
    }
    public function getIcon($type, $size=16) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $size = 'mimex'.$size;
        $type = strtolower($type);
        if($type=='folder') {
            $iconfile = $this->iconpath.'/'.$size.'/folder.png';
        } elseif(JFile::exists($this->icondir.DS.$size.DS.$type.'.png')) {
            $iconfile = $this->iconpath.'/'.$size.'/'.$type.'.png';
        } else {
            $iconfile = $this->iconpath.'/'.$size.'/_blank.png';
        }
        return $iconfile;
    }
    public static function checkDirectories($source, $indexfile=null, $thumbsdir=null) {

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        // Create Thumbnail Directory & index.html file
        if(!JFolder::exists($thumbsdir)) JFolder::create($thumbsdir);
        if(!JFile::exists($thumbsdir.DS.'index.html') && JFile::exists($indexfile)) JFile::copy($indexfile, $thumbsdir.DS.'index.html');
        $subdirs = explode('/', $source);
        $crawlpath = '';
        if($indexfile!=null && $thumbsdir!=null) {
            foreach($subdirs as $subdir) {
                $crawlpath.= trim($subdir, '/');
                // Set thumbnail paths
                if(!JFolder::exists($thumbsdir.DS.$crawlpath)) JFolder::create($thumbsdir.DS.$crawlpath);
                if(!JFile::exists($thumbsdir.DS.$crawlpath.DS.'index.html') && JFile::exists($indexfile)) JFile::copy($indexfile, $thumbsdir.DS.$crawlpath.DS.'index.html');

                $crawlpath.= '/';
            }
        }
    }
    public function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
    public function getHTML($currentfolder) {
        $lang = JFactory::getLanguage();
        $lang->load('plg_content_jimediabrowser', JPATH_ADMINISTRATOR);

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $params = $this->getParams();
        $this->debug = $params->get('debug', 0);

        // Set currentfolder
        $this->currentfolder = rtrim($currentfolder, '/');

        // Get Data
        $this->data = $this->open();

        // Check for template overrides
        $app = JFactory::getApplication();
        $path = JPATH_THEMES.'/'.$app->getTemplate().'/html/plg_jimediabrowser/default.php';
        if(!file_exists($path)) $path = dirname(__FILE__).'/tmpl/default.php';
        // Render template
        ob_start();
        require($path);
        $html = ob_get_clean();

        // Return html
        return $html;
    }
    /*public static function createPreview($item, $rootdir, $previewsdir, $previewspath, $params) {
        $debug = $params->get('debug', 0);
        if(!JFile::exists($previewsdir.$item->path.$item->name.'.jpg') || $params->get('images_cache', 1)==0) {
            require_once(JPATH_SITE.'/plugins/content/jimediabrowser/helpers/imageprocessor.php');
            $JiImageProcessor = new JiMediaBrowserImageProcessor();

            $source = rtrim($rootdir, '/').'/'.rtrim($item->path, '/').'/'.$item->name.'.jpg';
            if(file_exists($source)) {
                $imageMade = $JiImageProcessor->resizeImage($source, $previewsdir.$item->path.$item->name.'.jpg', $params, $item->type, 'pre', $debug);
                if($imageMade==false) return null;
            } else {
                return null;
            }
        }
        return $previewspath.$item->path.$item->name.'.jpg';
    }*/
}