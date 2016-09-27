<?php
/**
 * @version     $Id: controller.php 010 2013-08-26 14:21:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
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
class JiFormsController extends JControllerLegacy
{
    protected $default_view = 'form';

    public function display($cachable = false, $urlparams = false)
    {
        parent::display();
        return $this;
    }
}