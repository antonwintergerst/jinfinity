<?php
/**
 * @version     $Id: extensions.php 026 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controlleradmin');

class JiExtensionManagerControllerExtensions extends JControllerAdmin
{
    function refreshserver() {
        $model = $this->getModel('extensions');
        $xml = $model->refreshServer();
        header("Expires: Wed, 1 Jun 1988 09:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        if($xml!='') {
            header("Content-type: text/xml; charset=utf-8");
        }
        echo $xml;
        exit;
    }
}