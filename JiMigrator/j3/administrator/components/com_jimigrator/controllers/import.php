<?php 
/**
 * @version     $Id: import.php 035 2013-10-08 12:39:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerImport extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
    }
    function clear() {
        $model = $this->getModel('import');
        $model->clear();

        $link = 'index.php?option=com_jimigrator&view=import';
        $this->setRedirect($link);
    }
    function upload()
    {
        $model = $this->getModel('import');
        $response = $model->upload();
        
        $link = 'index.php?option=com_jimigrator&view=import';
        $this->setRedirect($link);
    }
    function import()
    {
        $model = $this->getModel('import');
        $response = $model->import();
        
        // Get the Application Object.
        $app = JFactory::getApplication();
        echo $response;
        // Close the Application
        $app->close();
    }
    function doimport()
    {
        $model = $this->getModel('import');
        $response = $model->doimport();

        // Get the Application Object.
        $app = JFactory::getApplication();
        echo $response;
        // Close the Application
        $app->close();
    }
}