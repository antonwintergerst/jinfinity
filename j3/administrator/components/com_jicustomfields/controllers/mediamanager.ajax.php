<?php
/**
 * @version     $Id: mediamanager.ajax.php 052 2014-10-24 11:47:00Z Anton Wintergerst $
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
    public function __construct()
    {
        parent::__construct();
    }
    public function renderinput() {
        // Get the Application Object.
        $app = JFactory::getApplication();
        // Set Page Header
        header('Content-Type: text/html;charset=UTF-8');
        // Get Data
        $model = $this->getModel('mediamanager');
        $response = $model->renderInput();
        // Render HTML
        echo $response;
        // Close the Application.
        $app->close();
    }
}