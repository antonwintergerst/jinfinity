<?php
/**
 * @version     $Id: fields.ajax.php 052 2014-06-09 10:56:00Z Anton Wintergerst $
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
class JiCustomFieldsControllerFields extends JControllerLegacy
{
    public function __construct()
    {
        parent::__construct();
    }

    public function renderinputs()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();
        $jinput = $app->input;
        // Set Page Header
        header('Content-Type: text/html;charset=UTF-8');

        // Get Data
        $model = $this->getModel('fields');

        $item = new stdClass();
        $item->id = $jinput->get('id', 0);
        $item->catid = $jinput->get('catid', 0);

        // Get Field Values for article
        $values = $model->getValues($item, 'item');
        // Load fieldlist
        $jifields = $model->getJiFields($item, $values);

        // Render HTML
        $model->renderInputLayout($jifields, $item);

        // Close the Application.
        $app->close();
    }

    public function renderinput()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: text/html;charset=UTF-8');
        // Get Data
        $model = $this->getModel('fields');
        $response = $model->renderInput();
        // Render HTML
        echo $response;
        // Close the Application.
        $app->close(); 
    }

    public function renderinputoption()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: text/html;charset=UTF-8');
        // Get Data
        $model = $this->getModel('fields');
        $response = $model->renderInputOption();
        // Render HTML
        echo $response;
        // Close the Application.
        $app->close(); 
    }
}