<?php
/**
 * @version     $Id: validator.php 011 2013-11-13 11:25:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

class JiFormsControllerValidator extends JControllerForm
{
    public function validate() {
        $jinput = JFactory::getApplication()->input;
        $objects = $jinput->get('objects', null, 'raw');
        $fields = json_decode($objects);

        require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'validator.php');

        $validator = new JiFormsValidator();
        $result = new stdClass();
        $result->valid = true;
        $result->msgs = array();
        if($fields!=null) {
            foreach($fields as $field) {
                $label = (isset($field->label))? $field->label : null;
                $response = $validator->validateField($field->value, $field->class, $label);
                if(!$response->valid) {
                    $result->valid = false;
                }
                $result->msgs[] = array('valid'=>$response->valid,'msg'=>$response->msg,'id'=>$field->id);
            }
        }

        $app = JFactory::getApplication();
        header('Content-Type: application/json;charset=UTF-8');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
        echo json_encode($result);
        $app->close();
        exit;
    }
}