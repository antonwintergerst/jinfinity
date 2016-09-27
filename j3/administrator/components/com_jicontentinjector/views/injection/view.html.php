<?php
/*
 * @version     $Id: view.html.php 010 2013-06-05 18:19:00Z Anton Wintergerst $
 * @package     Jinfinity Content Injector for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    class JiContentInjectorViewInjection extends JViewLegacy
    {
        protected $form;
        protected $item;
        protected $state;

        public function display($tpl = null)
        {
            $this->form = $this->get('Form');
            $this->item = $this->get('Item');
            $this->state = $this->get('State');

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }

            $this->addToolbar();
            parent::display($tpl);
        }
        protected function addToolbar()
        {
            JFactory::getApplication()->input->set('hidemainmenu', true);

            $isNew = ($this->item->id == 0);;
            JToolbarHelper::title(JText::_('COM_JICONTENTINJECTOR_'.($isNew ? 'ADD_INJECTION' : 'EDIT_INJECTION')), 'jicontentinjector.png');

            if($isNew) {
                JToolbarHelper::apply('injection.apply');
                JToolbarHelper::save('injection.save');
                JToolbarHelper::save2new('injection.save2new');
                JToolbarHelper::cancel('injection.cancel');
            } else {
                JToolbarHelper::apply('injection.apply');
                JToolbarHelper::save('injection.save');
                JToolbarHelper::save2new('injection.save2new');
                JToolbarHelper::save2copy('injection.save2copy');
                JToolbarHelper::cancel('injection.cancel', 'JTOOLBAR_CLOSE');
            }

            $this->sidebar = JHtmlSidebar::render();
            //JiContentInjectorHelper::addSubmenu('injections');
            JToolbarHelper::title(JText::_('COM_JICONTENTINJECTOR_INJECTION'), 'jicontentinjector.png');
        }
    }
} else {
    class JiContentInjectorViewInjection extends JView
    {
        protected $form;
        protected $item;
        protected $state;

        function display($tpl = null)
        {
            $this->form = $this->get('Form');
            $this->item = $this->get('Item');
            $this->state = $this->get('State');

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }

            $this->addToolbar();
            parent::display($tpl);
        }
        protected function addToolbar()
        {
            JFactory::getApplication()->input->set('hidemainmenu', true);

            $isNew = ($this->item->id == 0);;
            JToolbarHelper::title(JText::_('COM_JICONTENTINJECTOR_'.($isNew ? 'ADD_INJECTION' : 'EDIT_INJECTION')), 'jicontentinjector.png');

            if($isNew) {
                JToolbarHelper::apply('injection.apply');
                JToolbarHelper::save('injection.save');
                JToolbarHelper::save2new('injection.save2new');
                JToolbarHelper::cancel('injection.cancel');
            } else {
                JToolbarHelper::apply('injection.apply');
                JToolbarHelper::save('injection.save');
                JToolbarHelper::save2new('injection.save2new');
                JToolbarHelper::save2copy('injection.save2copy');
                JToolbarHelper::cancel('injection.cancel', 'JTOOLBAR_CLOSE');
            }
            if(version_compare(JVERSION, '1.6.0', 'ge')) {
                JiContentInjectorHelper::addSubmenu('injections');
            }
            JToolbarHelper::title(JText::_('COM_JICONTENTINJECTOR_INJECTION'), 'jicontentinjector.png');
        }
    }
}