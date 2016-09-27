<?php
/**
 * @version     $Id: jisecure.php 015 2014-02-28 10:39:00Z Anton Wintergerst $
 * @package     JiSecure Plugin for Joomla 3.0+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

class plgSystemJiSecure extends JPlugin
{
    function onAfterDispatch()
    {
        // Load Plugin Params
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            $plugin = JPluginHelper::getPlugin('system', 'jisecure');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            $params = $this->params;
        }
        $app = JFactory::getApplication();
        $appname = $app->getName();
        if($appname=='admin' || $appname=='administrator') {
            $checked = (int) $app->getUserState('plg_jisecure.keychecked', 0);
            if($checked==0) {
                $jinput = $app->input;
                $key = $params->get('adminkey', 'boss');
                if($jinput->get($key)!==null) {
                    $app->setUserState('plg_jisecure.keychecked', 1);
                    $app->redirect(JURI::root().'administrator');
                } else {
                    $url = $jinput->get('redirecturl');
                    $app->redirect(JURI::root().$url);
                }
            }
        }
    }
}