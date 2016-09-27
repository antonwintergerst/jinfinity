<?php 
/**
 * @version     $Id: JSON listfilter.php 091 2013-10-25 12:32:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerListFilter extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
        // Get URL variables
        $filtertype = JRequest::getVar('filtertype');
        $scope = JRequest::getVar('scope');
        $name = JRequest::getVar('name');
        // Rebuild correct ListFilter
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'field.php');
        $fieldhelper = new JiMigratorFieldHelper();
        $fieldhelper->setPaths(JPATH_SITE.'/administrator/components/com_jimigrator/jifields');
		$data = new stdClass();
		$data->name = $name;
		$data->scope = $scope;
		$data->type = $filtertype.'filter';
        $JiField = $fieldhelper->loadType($data);
        $this->listfilter = $JiField->getFilter();
    }
    function open()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: application/json;charset=UTF-8');     
        
        $response = $this->listfilter->open();
        
        echo json_encode($response);
        
        // Close the Application.
        $app->close(); 
    }
    function includepath()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: application/json;charset=UTF-8');     
        
        $response = $this->listfilter->includePath();
        
        echo json_encode($response);
        
        // Close the Application.
        $app->close(); 
    }
    function excludepath()
    {
        // Get the Application Object.
        $app = JFactory::getApplication();      
        // Set Page Header
        header('Content-Type: application/json;charset=UTF-8');     
        
        $response = $this->listfilter->excludePath();
        
        echo json_encode($response);
        
        // Close the Application.
        $app->close(); 
    }
}