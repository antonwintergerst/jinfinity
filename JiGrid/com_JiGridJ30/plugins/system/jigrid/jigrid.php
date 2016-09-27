<?php
/**
 * @version     $Id: jigrid.php 010 2014-03-24 14:51:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$path = JPATH_SITE.DS.'media'.DS.'jigrid'.DS.'layouttools.php';
if(file_exists($path)) require_once($path);

class plgSystemJiGrid extends JPlugin
{
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
}