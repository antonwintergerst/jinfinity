<?php
/**
 * @version     $Id: mod_jiforms.php 011 2014-02-18 22:53:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modJiFormsHelper {
    public function getForm($params) {
        $formid = (int) $params->get('formid');
        if($formid!=0) {
            $jinput = JFactory::getApplication()->input;

            $event = $jinput->get('event', 'beforeload');
            //$event = 'onload';
            require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'models'.DS.'form.php');
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                $model = JModelLegacy::getInstance('Form', 'JiFormsModel', array('ignore_request'=>true));
            } else {
                $model = JModel::getInstance('Form', 'JiFormsModel', array('ignore_request'=>true));
            }
            $model->setState('form.id', $formid);
            $response = $model->eventHandler($event);

            //if($response) {
                $form = $model->getFormOnload();

            //}
        }
        return $form;
    }
}