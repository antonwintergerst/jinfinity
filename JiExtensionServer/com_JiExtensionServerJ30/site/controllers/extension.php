<?php
/**
 * @version     $Id: extension.php 015 2013-06-17 12:02:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

class JiExtensionServerControllerExtension extends JControllerForm
{
    public function download() {
        $model = $this->getModel('Extension');
        $errors = $model->download();
        echo $errors;
        exit;
    }
}