<?php 
/**
 * @version     $Id: files.php 087 2013-10-08 11:09:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class FilesImporter extends JiImporter {
    /* >>> PRO >>> */
    public function process($bypass=true) {
        parent::process($bypass);

        if($this->start==0) {
            $this->setStatus(array('msg'=>'Starting Files Import'));
        } else {
            $this->setStatus(array('msg'=>'Resuming Files Import'));
        }

        $this->source = $this->sourcedir.'/sitefiles/';
        $this->dest = JPATH_SITE.'/';
        $this->processor();
    }

    /**
     * Import all directories and files
     */
    public function processor() {
        // TODO: split this into chunks
        $source = $this->source;
        $dest = $this->dest;
        if(is_dir($source)) {
            if($dh=opendir($source)) {
                // Loop through all the files
                while(($file=readdir($dh))!==false) {
                    // Skip parent and root directories
                    if(($file!==".") && ($file!=="..")) {
                    	if(is_dir($source.$file)) {
                    		$this->copyall($source.$file, $dest.$file);
                    	} else {
	                        $this->setStatus(array('msg'=>'Copying '.$source.$file.' to '.$dest.$file));
	                        if(!$this->debug) {
	                        	if(!copy($source.$file, $dest.$file)) {
	                        		$this->setStatus(array('msg'=>'Error! Unable to copy file/directory'));
	                        	}
							}
						}
                    }
                }
				closedir($dh);
            }
        } else {
        	$this->setStatus(array('msg'=>'Warning! Unable to find source directory'));
        }
        call_user_func_array($this->complete, array(null));
    }
	function copyall($source, $dest) {
    	$dir = opendir($source);
		if(!file_exists($dest)) {
			$this->setStatus(array('msg'=>'Creating directory '.$dest));
		    if(!$this->debug) mkdir($dest);
		}
	    while(false!==($file = readdir($dir))) { 
	        if(($file!='.') && ($file!='..')) { 
	            if(is_dir($source.'/'.$file)) { 
	                $this->copyall($source.'/'.$file, $dest.'/'.$file); 
	            } else {
	            	$this->setStatus(array('msg'=>'Copying '.$source.'/'.$file.' to '.$dest.'/'.$file));
					if(!$this->debug) {
			            if(!copy($source.'/'.$file, $dest.'/'.$file)) {
		            		$this->setStatus(array('msg'=>'Error! Unable to copy file/directory'));
		            	}
	            	}
				}
	        } 
	    } 
    }
    /* <<< PRO <<< */
}