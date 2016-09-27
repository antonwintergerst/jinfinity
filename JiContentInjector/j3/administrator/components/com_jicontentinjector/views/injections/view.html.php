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
    class JiContentInjectorViewInjections extends JViewLegacy
    {
        protected $items;
        protected $pagination;
        protected $state;

        function display($tpl = null)
        {
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->state = $this->get('State');
            $this->contexts = $this->get('Contexts');
            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }
            if($this->getLayout()!=='modal') {
                $this->addToolbar();
            }

            parent::display($tpl);
        }
        protected function addToolbar()
        {
            // Get the toolbar object instance
            $bar = JToolBar::getInstance('toolbar');

            JToolbarHelper::title(JText::_('JICONTENTINJECTOR_INJECTIONS'), 'jicontentinjector.png');

            JToolbarHelper::addNew('injection.add');
            JToolbarHelper::editList('injection.edit');
            JToolbarHelper::publish('injections.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('injections.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::archiveList('injections.archive');
            JToolbarHelper::checkin('injections.checkin');
            if($this->state->get('filter.published') == -2) {
                JToolbarHelper::deleteList('', 'injections.delete', 'JTOOLBAR_EMPTY_TRASH');
            } else {
                JToolbarHelper::trash('injections.trash');
            }
            // Add a batch button
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');
            $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
            $bar->appendButton('Custom', $dhtml, 'batch');

            JHtmlSidebar::setAction('index.php?option=com_jicontentinjector&view=injections');

            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_PUBLISHED'),
                'filter_published',
                JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
            );
            JHtmlSidebar::addFilter(
                JText::_('COM_JICONTENTINJECTOR_SELECTCONTEXT'),
                'filter_context',
                JHtml::_('select.options', $this->contexts, 'value', 'text', $this->state->get('filter.context'), true)
            );

            $this->sidebar = JHtmlSidebar::render();
            //JiContentInjectorHelper::addSubmenu('injections');
        }
        /**
         * Returns an array of fields the table can be sorted by
         *
         * @return  array  Array containing the field name to sort by as the key and display text as value
         *
         */
        protected function getSortFields()
        {
            return array(
                'ordering' => JText::_('JGRID_HEADING_ORDERING'),
                'state' => JText::_('JSTATUS'),
                'title' => JText::_('COM_JICONTENTINJECTOR_TITLE_LABEL'),
                'selector' => JText::_('COM_JICONTENTINJECTOR_SELECTOR_LABEL'),
                'context' => JText::_('COM_JICONTENTINJECTOR_CONTEXT_LABEL'),
                'publish_up' => JText::_('COM_JICONTENTINJECTOR_PUBLISHUP_LABEL'),
                'publish_down' => JText::_('COM_JICONTENTINJECTOR_PUBLISHDOWN_LABEL'),
                'id' => JText::_('JGRID_HEADING_ID')
            );
        }
    }
} else {
    class JiContentInjectorViewInjections extends JView
    {
        protected $items;
        protected $pagination;
        protected $state;

        function display($tpl = null)
        {
            $this->addToolbar();
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->state = $this->get('State');
            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                JError::raiseError(500, implode("\n", $errors));
                return false;
            }

            parent::display($tpl);
        }
        protected function addToolbar()
        {
            if(version_compare(JVERSION, '1.6.0', 'ge')) {
                JiContentInjectorHelper::addSubmenu('injections');
            }
            JToolbarHelper::title(JText::_('COM_JICONTENTINJECTOR_INJECTIONS'), 'jicontentinjector.png');
        }
    }
}