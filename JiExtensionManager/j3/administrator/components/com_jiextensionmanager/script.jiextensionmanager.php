<?php 
/**
 * @version     $Id: script.jiextensionmanager.php 017 2014-12-19 13:02:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiExtensionManagerInstallerScript
{
    public function postflight($type, $parent)
    {
        // remove front-end parts
        jimport('joomla.filesystem.folder');
        $src = JPATH_SITE.'/components/com_jimigrator';
        if(JFolder::exists($src)) JFolder::delete($src);
    }
    public function uninstall($parent)
    {
    }
    public function update($type) {
    }
}