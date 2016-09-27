<?php 
/*
 * @version     $Id: controller.php 020 2013-04-08 10:38:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
if(version_compare(JVERSION, '3.0.0', 'ge')) {
	class JiMigratorController extends JControllerLegacy
	{
	    public function display($cachable = false, $urlparams = false)
	    {
	        parent::display();
            
            return $this;
	    }
	}
} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
	// <2.5 Legacy
	jimport('joomla.application.component.controller');
	class JiMigratorController extends JController
	{
	    public function display($cachable = false, $urlparams = false)
	    {
	        parent::display();
	    }
	}
} else {
	// <1.5 Legacy
	jimport('joomla.application.component.controller');
	class JiMigratorController extends JController
	{
	    function display()
	    {
	        parent::display();
	    }
	}
}