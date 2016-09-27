<?php
/**
 * @version     $Id: view.html.php 015 2013-06-18 10:45:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
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
class JiExtensionServerViewBranch extends JViewLegacy
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
        JToolbarHelper::title(JText::_('COM_JIEXTENSIONSERVER_'.($isNew ? 'ADD_BRANCH' : 'EDIT_BRANCH')), 'jiextensionserver.png');

        if($isNew) {
            JToolbarHelper::apply('branch.apply');
            JToolbarHelper::save('branch.save');
            JToolbarHelper::save2new('branch.save2new');
            JToolbarHelper::cancel('branch.cancel');
        } else {
            JToolbarHelper::apply('branch.apply');
            JToolbarHelper::save('branch.save');
            JToolbarHelper::save2new('branch.save2new');
            JToolbarHelper::save2copy('branch.save2copy');
            JToolbarHelper::cancel('branch.cancel', 'JTOOLBAR_CLOSE');
        }
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->sidebar = JHtmlSidebar::render();
        }
        JToolbarHelper::title(JText::_('COM_JIEXTENSIONSERVER_BRANCH'), 'jiextensionserver.png');
    }
}