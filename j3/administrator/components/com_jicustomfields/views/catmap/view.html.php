<?php
/**
 * @version     $Id: view.html.php 001 2014-05-12 09:20:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
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
class JiCustomFieldsViewCatMap extends JViewLegacy
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
        JToolbarHelper::title(JText::_('JICUSTOMFIELDS_'.($isNew ? 'ADD_CATMAP' : 'EDIT_CATMAP')), 'jicustomfields.png');

        if($isNew) {
            JToolbarHelper::apply('catmap.apply');
            JToolbarHelper::save('catmap.save');
            JToolbarHelper::save2new('catmap.save2new');
            JToolbarHelper::cancel('catmap.cancel');
        } else {
            JToolbarHelper::apply('catmap.apply');
            JToolbarHelper::save('catmap.save');
            JToolbarHelper::save2new('catmap.save2new');
            JToolbarHelper::save2copy('catmap.save2copy');
            JToolbarHelper::cancel('catmap.cancel', 'JTOOLBAR_CLOSE');
        }

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->sidebar = JHtmlSidebar::render();
        } elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
            JiCustomFieldsHelper::addSubmenu('fields');
        }
    }
}