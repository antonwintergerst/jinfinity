<?php
/**
 * @version     $Id: mediamanager.json.php 053 2014-10-24 17:48:00Z Anton Wintergerst $
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
class JiCustomFieldsControllerMediaManager extends JControllerLegacy
{
    public function open()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();
        // Set Page Header
        header('Content-Type: application/json;charset=UTF-8');

        // Get Data
        $model = $this->getModel('mediamanager');
        $response = $model->open();
        echo json_encode($response);

        // Close the Application.
        $app->close();
    }
    public function loadicon()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();
        // Set Page Header
        header('Content-Type: application/json;charset=UTF-8');

        // Get Data
        $model = $this->getModel('mediamanager');
        $response = $model->loadicon();
        echo json_encode($response);

        // Close the Application.
        $app->close();
    }
}