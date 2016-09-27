<?php
/**
 * @version     $Id: view.html.php 011 2013-08-29 11:24:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
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
class JiFormsViewEvent extends JViewLegacy
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
        JToolbarHelper::title(JText::_('COM_JIFORMS_'.($isNew ? 'ADD_EVENT' : 'EDIT_EVENT')), 'jiforms.png');

        if($isNew) {
            JToolbarHelper::apply('event.apply');
            JToolbarHelper::save('event.save');
            JToolbarHelper::save2new('event.save2new');
            JToolbarHelper::cancel('event.cancel');
        } else {
            JToolbarHelper::apply('event.apply');
            JToolbarHelper::save('event.save');
            JToolbarHelper::save2new('event.save2new');
            JToolbarHelper::save2copy('event.save2copy');
            JToolbarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
        }

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->sidebar = JHtmlSidebar::render();
        } elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
            JiFormsHelper::addSubmenu('events');
        }
    }
}