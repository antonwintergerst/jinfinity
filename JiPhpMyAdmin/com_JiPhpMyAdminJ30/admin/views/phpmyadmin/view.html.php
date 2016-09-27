<?php
/**
 * @version     $Id: view.html.php 010 2013-07-01 22:30:00Z Anton Wintergerst $
 * @package     JiPhpMyAdmin for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

if(!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {
    }
}
class JiPhpMyAdminViewPhpMyAdmin extends JViewLegacy
{
    function display($tpl = null)
    {
        $this->addToolbar();

        parent::display($tpl);
    }
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('COM_JIPHPMYADMIN'), 'jiphpmyadmin.png');
        //JToolbarHelper::preferences('com_jiphpmyadmin', '400');
    }
}