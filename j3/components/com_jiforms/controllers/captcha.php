<?php
/**
 * @version     $Id: captcha.php 021 2014-11-05 11:47:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

class JiFormsControllerCaptcha extends JControllerForm
{
    public function image() {
        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'captcha.php');
        $captcha = new JiFormsCaptcha();

        $app = JFactory::getApplication();

        $result = $captcha->getCaptcha();

        $app->close();
        exit;
    }
    public function code() {
        // set headers
        header('Content-Type: text/plain');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");

        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'captcha.php');
        $captcha = new JiFormsCaptcha();

        $app = JFactory::getApplication();

        echo $captcha->getCode();

        $app->close();
        exit;
    }
}