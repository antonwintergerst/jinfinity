<?php
/**
 * @version		$Id: upload.php 071 2014-10-24 17:48:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
// Get Images Helper
require_once(JPATH_SITE.'/administrator/components/com_jicustomfields/helpers/images.php');
class JiCustomFieldsModelUpload extends JModelLegacy
{
	public function upload() {
        /*echo '<pre>';
		print_r($_FILES);
        echo '</pre>';
        die;*/

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $destdir = rtrim(JPATH_SITE, DS).DS.'images';
        $subdir = rtrim(str_replace('/', DS, $jinput->get('ffpath', '', 'raw')), DS);
        if($subdir!='') $destdir.= DS.$subdir;
        $inputname = 'jiuploadinput'; // name="jiuploadinput[file]"
        $allowedExtensions = array('gif','jpeg','jpg','png');
        $maxfilesize = 10000000; //10mb

        //echo $destdir; die;
        // check if file was uploaded
		if($_FILES!=null) {
			// single image support
			if(isset($_FILES[$inputname]['name']['file'])) {
				// load joomla file library
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');

                $file = $_FILES[$inputname]['name']['file'];
                $filetype = end(explode('.', $file));
                $filename = rtrim($file, $filetype);

                $allowed = ((in_array(strtolower($filetype), $allowedExtensions))
                    && ($_FILES[$inputname]['size']['file'] < $maxfilesize)
                    && is_uploaded_file($_FILES[$inputname]['tmp_name']['file']));

                if($allowed) {
					// store original file
					$original = $destdir.DS.$file;
					move_uploaded_file($_FILES[$inputname]['tmp_name']['file'], $original);
                    return $original;

					// resize/create thumbnails
					/*JinfinityImagesHelper::ResizeCropped($original, $destdir.DS.$filename.'_256.jpg', 256, 320);
					JinfinityImagesHelper::ResizeCropped($original, $destdir.DS.$filename.'_64.jpg', 64, 64);

					$p1 = $jinput->get('p1', null);
					$p2 = $jinput->get('p2', null);
					if($p1!=null && $p2!=null) {
                        $modified = $destdir.DS.$filename.'_modified.jpg';
						JinfinityImagesHelper::CustomCrop($original, $modified, $p1, $p2);
                        //JinfinityImagesHelper::ResizeKeepAspect($modified, $ffpath.'/'.$filename.'_modified150.jpg', 150, 150);
						JinfinityImagesHelper::ResizeCropped($modified, $destdir.DS.$filename.'_modified150.jpg', 150, 150);
					} else {
					    JinfinityImagesHelper::ResizeCropped($original, $destdir.DS.$filename.'_150.jpg', 150, 150);
					}

                    // return thumbnail
					$image = $destdir.DS.$filename.'_256.jpg';
					return $image;*/
				}
			}
		}
		return 'false';
	}
    public function resize() {
        $image = JRequest::getVar('image', '', 'post');
		// Remove anti-caching string
		$imageparts = explode('?', $image);
		$image = $imageparts[0]; 
		// Get Points
        $p1 = JRequest::getVar('p1', '', 'post');
        $p2 = JRequest::getVar('p2', '', 'post');
        if($image!=null && $p1!=null && $p2!=null) {
            $image = end(explode('/', $image));
            JinfinityImagesHelper::CustomCrop(JPATH_ROOT.'/images/content/'.$image, JPATH_ROOT.'/images/content/'.str_replace('_original.jpg', '_custom.jpg', $image), $p1, $p2);
            JinfinityImagesHelper::ResizeKeepAspect(JPATH_ROOT.'/images/content/'.str_replace('_original.jpg', '_custom.jpg', $image), JPATH_ROOT.'/images/content/'.str_replace('_original.jpg', '_custom150.jpg', $image), 150, 150, 100);
            return JURI::root().'images/content/'.str_replace('_original.jpg', '_custom150.jpg', $image);
        } else {
            return 'false';
        }
    }
}