<?php
/**
 * @version     $Id: controller.php 012 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
class JiExtensionManagerController extends JControllerLegacy
{
    /**
     * @var		string	The default view.
     */
    protected $default_view = 'extensions';

    public function display($cachable = false, $urlparams = false)
    {
        parent::display();
        return $this;
    }
}