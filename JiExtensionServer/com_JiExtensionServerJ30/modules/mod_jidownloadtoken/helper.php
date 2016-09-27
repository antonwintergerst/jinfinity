<?php
/**
 * @version     $Id: helper.php 020 2013-06-17 15:50:00Z Anton Wintergerst $
 * @package     JiDownloadToken Module for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiDownloadTokenHelper
{
    public function getToken($params=null) {
        JModelLegacy::addIncludePath(JPATH_SITE.'/administrator/components/com_jiextensionserver/models', 'JiExtensionServerModel');
        $model = JModelLegacy::getInstance('Token', 'JiExtensionServerModel');
        $token = $model->getToken();

        return $token;
    }
}
?>
