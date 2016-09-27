<?php 
/**
 * @version     $Id: jiframework.php 110 2014-12-05 10:05:00Z Anton Wintergerst $
 * @package     JiFramework System Plugin for Joomla! 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Load JiLoader Class and FunctionsS
require_once(JPATH_SITE.DS.'plugins'.DS.'system'.DS.'jiframework'.DS.'helpers'.DS.'jiloader.php');

class plgSystemJiFramework extends JPlugin 
{
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
}