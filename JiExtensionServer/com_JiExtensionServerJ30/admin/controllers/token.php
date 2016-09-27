<?php
/**
 * @version     $Id: extension.php 020 2013-06-18 10:02:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

class JiExtensionServerControllerToken extends JControllerForm
{
    /**
     * Class constructor.
     *
     * @param   array  $config  A named array of configuration variables.
     *
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    public function check() {
        $jinput = JFactory::getApplication()->input;
        $model = $this->getModel('Token');

        $data  = JRequest::getVar('jform', array(), 'post', 'array');
        $form = $model->getForm($data, false);
        $validData = $model->validate($form, $data);

        if(isset($validData['dlkey'])) {
            $token = $validData['dlkey'];

            $response = $model->checkToken($token);
            $isValid = ($response)? 1 : 0;
            $jinput->set('jitoken.dlkey', $token);
            $jinput->set('jitoken.uid', $model->uid);
            $jinput->set('jitoken.valid', $isValid);
        }
        parent::display();
    }
}