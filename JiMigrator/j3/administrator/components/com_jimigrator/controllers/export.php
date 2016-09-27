<?php 
/**
 * @version     $Id: export.php 030 2013-08-01 10:56:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5-2.5
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerExport extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
    }
    function export()
    {
        $model = $this->getModel('export');
        $response = $model->export();
        
        // Get the Application Object.
        $app = JFactory::getApplication();
        echo $response;
        // Close the Application
        $app->close();
    }
    function doexport()
    {
        $model = $this->getModel('export');
        $response = $model->doexport();

        // Get the Application Object.
        $app = JFactory::getApplication();
        echo $response;
        // Close the Application
        $app->close();
    }
    function download()
    {
        $model = $this->getModel('export');
        $model->downloadFile(JRequest::getVar('dlfile'));
    }
    function delete()
    {
        $model = $this->getModel('export');
        $model->deleteFile(JRequest::getVar('dlfile'));
    }
}