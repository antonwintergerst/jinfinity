<?php 
/**
 * @version     $Id: jimediabrowser.php 066 2014-12-18 16:33:00Z Anton Wintergerst $
 * @package     JiMediaBrowser Content Plugin for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class plgContentJiMediaBrowser extends JPlugin {
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        if($app->isSite()) {
            $this->mediaBrowser($context, $article);
        }
    }

    public function onJiMediaBrowserRequest()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();
        $jinput = $app->input;

        $task = $jinput->get('mbtask', 'open');

        $params = $this->getParams();

        // Load Helper Class
        require_once(JPATH_SITE.'/plugins/content/jimediabrowser/helper.php');
        $helper = new plgJiMediaBrowserHelper();

        if($task=='download') {
            $mbid = $jinput->get('mbid', 'folder');
            $currentfolder = $jinput->get($mbid, '', 'raw');
            $downloadfile = $jinput->get('mbfile', null, 'raw');

            // Set root directory and path
            if($params->get('advancedroot', 0)==1) {
                $path = $helper->dirDRT($params->get('dir_downloads', JPATH_SITE));
                if(strstr($path, '/')!==false) $path = '/'.$path;
            } else {
                $path = trim($params->get('sourcedirectory', 'images'), DS);
                $path = JPATH_SITE.DS.$path;
            }
            $path = rtrim($path, DS).DS;

            if($helper->sanitizePath($currentfolder)!='') {
                $path.= $currentfolder.DS;
            }
            $this->downloadFile($path.$downloadfile, $path, $params);
        } elseif($task=='loadicon' || $task=='loadpreview') {
            $mbid = $jinput->get('mbid', 'folder');
            $helper->currentfolder = $jinput->get($mbid, '', 'raw');
            /*$filename = $jinput->get('mbfile', null, 'raw');

            $path = '';
            if($helper->sanitizePath($currentfolder)!='') {
                $path.= $currentfolder.DS;
            }
            $path.= $filename;*/
            $path = $jinput->get('mbpath', null, 'raw');

            if($helper->sanitizePath($helper->currentfolder)!='') {
                //$path.= $helper->currentfolder.DS;
            }

            $helper->setState();
            if($task=='loadicon') {
                $response = $helper->createThumbnail($path);
            } else {
                $response = $helper->createPreview($path);
            }

            $response->e = $jinput->get('e', null, 'raw');

            // Set Page Header
            header('Content-Type: application/json;charset=UTF-8');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");

            echo json_encode($response);

            // Close the Application.
            $app->close();
            exit;
        } else {
            // Set Page Header
            header('Content-Type: application/json;charset=UTF-8');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");

            // Get Data
            $mbid = $jinput->get('mbid', 'folder');
            $currentfolder = $jinput->get($mbid, '', 'raw');

            $helper->currentfolder = rtrim($currentfolder, '/');

            echo json_encode($helper->open());

            // Close the Application.
            $app->close();
            exit;
        }
    }

    private function mediaBrowser($context, $item)
    {
        // return if no valid text source found
        if(!$sources = $this->getText($item)) return;

        // Load Helper Class
        require_once(JPATH_SITE.'/plugins/content/jimediabrowser/helper.php');

        // Get User
        $user = JFactory::getUser();

        $params = $this->getParams();

        $regex = "#{".$params->get('curlyvar', 'mediabrowser')."(.*?)}(.*?){/".$params->get('curlyvar', 'mediabrowser')."}#s";

        $changed = false;
        foreach($sources as $key=>$text) {
            preg_match_all($regex, $text, $matches);
            $count = count($matches[0]);

            if($count) {
                // Check if user has plugin access
                $useraccess = $params->get('user_access', array('8'));
                if(!is_array($useraccess)) $useraccess = array($useraccess);
                $canaccess = 0;
                if(in_array(0, $useraccess)) {
                    $canaccess = 1;
                } else {
                    foreach($user->groups as $group) {
                        if(in_array($group, $useraccess)) $canaccess = 1;
                    }
                }
                if($canaccess==1) {
                    // Replace curly brackets with media browser
                    $text = preg_replace_callback($regex, array(&$this,'addHTML'), $text);
                } else {
                    // Remove curly brackets and deny their existence (Thats classified!)
                    $text = preg_replace($regex, '', $text);
                }
                $item->{$key} = $text;
                $changed = true;
            }
        }
    }

    protected function addHTML($matches)
    {
        // Load Helper Class
        require_once(JPATH_SITE.'/plugins/content/jimediabrowser/helper.php');

        // Get User
        $user = JFactory::getUser();
        // Get Params
        $params = $this->getParams();

        // Get URL Vars
        $rootfolder = $matches[2];

        $app = JFactory::getApplication();
        $jinput = $app->input;

        $downloadfile = $jinput->get('mbfile', null, 'raw');
		
		// Load the helper class instance
        $helper = new plgJiMediaBrowserHelper();
        $mbid = $jinput->get('mbid', 'folder');
        if($downloadfile!=null) {
            $currentfolder = $jinput->get($mbid, '', 'raw');

            // This is a download link, try to get the file from the downloads directory
            if($params->get('advancedroot', 0)==1) {
                $path = $helper->dirDRT($params->get('dir_downloads', JPATH_SITE)).DS;
                if(strstr($this->rootdir, '/')!=false) $path = '/'.$path;
            } else {
                $sourcedirectory = trim($params->get('sourcedirectory', 'images'), DS);
                $path = JPATH_SITE.DS.$sourcedirectory.DS;
            }
            if($currentfolder!='') {
                $path.= $currentfolder.DS;
            }
            $this->downloadFile($path.$downloadfile, $path, $params);
            $html = '';
        } else {
            $currentfolder = $jinput->get($mbid, '', 'raw');
            // Just display the media browser
            $html = $helper->getHTML($rootfolder);
        }
        return $html;
    }

    protected function downloadFile($filename, $path, $params)
    {
    	// Clean data
    	$filename = htmlspecialchars_decode($filename);
		$path = htmlspecialchars_decode($path);
        $filename = mb_convert_encoding($filename, "UTF-8", "auto");
        // Get User
        $user = JFactory::getUser();
        $deniedurl = $params->get('denied_url', $_SERVER['HTTP_REFERER']);
        
        $type = end(explode('.', $filename));
        // Check if file exists
        if(!file_exists($filename)) $this->downloadFileFailed($deniedurl, $filename);
        // Check if file has allowed extension type
        $includeExtensions = explode(',', $params->get('include_exts', "folder,gif,jpg,jpeg,png,doc,docx,xls,xlsx,rar,txt,pdf,ppt,pptx,one,xps,zip"));
        if(!in_array(strtolower($type), $includeExtensions)) $this->downloadFileFailed($deniedurl, $filename);
        // Check if user has directory access
        $diraccess = $params->get('dir_access');
        if($diraccess!=null) {
            $diraccess = preg_split('/$\R?^/m', $diraccess);
            if(is_array($diraccess)) {
                $regex = "#(.*?) {(.*?)}#s";
                foreach($diraccess as $dir) {
                    preg_match($regex, $dir, $matches);
                    if(isset($matches[1]) && isset($matches[2])) {
                        $allowedPath = trim(str_replace('root', '', $matches[1]), DS);
                        if($allowedPath==trim($path, DS)) {
                            $allowedGroups = explode(',', $matches[2]);
                            $canaccess = 0;
                            if(in_array(0, $allowedGroups)) {
                                $canaccess = 1;
                            } else {
                                foreach($user->groups as $group) {
                                    if(in_array($group, $allowedGroups)) {
                                        $canaccess = 1;
                                    }
                                }
                            }
                            if($canaccess==0) $this->downloadFileFailed($deniedurl, $filename);
                        }
                    }
                }
            }
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
		
        $this->readfile_chunked($filename);
        // Exit before anything is added to the binary
        exit;
    }

    protected function downloadFileFailed($deniedurl, $filename)
    {
        header("Expires: Mon, 26 Jul 12012 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Location: ".$deniedurl);
        // Close the script
        exit;
    }
	/*
	 * Standard read file (only good for small files) [Depreciated]
	 */
    function readfile_plain($filename)
    {
    	$handle = fopen($filename, 'rb');
        fpassthru($handle);
        fclose($handle);
    }
    /*
	 * Splits up file reading for large files
	 */
	function readfile_chunked($filename, $retbytes=true)
    {
	   $chunksize = 1*(1024*1024); // how many bytes per chunk
	   $buffer = '';
	   $cnt =0;
	   // $handle = fopen($filename, 'rb');
	   $handle = fopen($filename, 'rb');
	   if ($handle === false) {
	       return false;
	   }
	   while (!feof($handle)) {
	       $buffer = fread($handle, $chunksize);
	       echo $buffer;
	       ob_flush();
	       flush();
	       if ($retbytes) {
	           $cnt += strlen($buffer);
	       }
	   }
	       $status = fclose($handle);
	   if ($retbytes && $status) {
	       return $cnt; // return num. bytes delivered like readfile() does.
	   }
	   return $status;
	}

    private function getParams($pluginType='content', $pluginName='jimediabrowser')
    {
        if(isset($this->params[$pluginType.$pluginName])) {
            return $this->params[$pluginType.$pluginName];
        } else {
            if(version_compare( JVERSION, '1.6.0', 'ge' )) {
                // Get plugin params
                $plugin = JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JRegistry($plugin->params);
            } else {
                // Get plugin params
                $plugin = &JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JParameter($plugin->params);
            }
            if(!is_array($this->params)) $this->params = array();
            $this->params[$pluginType.$pluginName] = $params;
            return $params;
        }
    }

    private function getText(&$item)
    {
        $sources = array();
        if(isset($item->text) && strlen($item->text)>0) $sources['text'] = $item->text;
        if(isset($item->introtext) && strlen($item->introtext)>0) $sources['introtext'] = $item->introtext;
        if(isset($item->fulltext) && strlen($item->fulltext)>0) $sources['fulltext'] = $item->fulltext;
        return (count($sources)>0)? $sources : false;
    }
}