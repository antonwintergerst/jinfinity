<?php
/**
 * @version     $Id: upload.ajax.php 022 2014-10-24 11:47:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
class JiCustomFieldsControllerUpload extends JControllerLegacy
{
	public function __construct()
	{
		parent::__construct();
	}
    public function display()
	{
		// Get the Application Object.
        $app = JFactory::getApplication();
		
		// Set Page Header
		header('Content-Type: text/html;charset=UTF-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Wed, 1 Jun 1988 00:00:00 GMT");
		
		// Get Data
		$model = $this->getModel('upload');
		$data = $model->upload();
		
		// Echo Data
		echo $data;
		
		// Close the Application.
        $app->close(); 
	}
    public function resize()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();
        
        // Set Page Header
        header('Content-Type: text/html;charset=UTF-8');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Wed, 1 Jun 1988 00:00:00 GMT");
        
        // Get Data
        $model = $this->getModel('upload');
        $data = $model->resize();
        
        // Echo Data
        echo $data;
        
        // Close the Application.
        $app->close(); 
    }
}