<?php
/**
 * @version     $Id: view.html.php 013 2013-11-13 13:27:00Z Anton Wintergerst $
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
class JiFormsViewForms extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null)
    {
        if($this->getLayout()!=='modal' && version_compare(JVERSION, '1.6.0', 'ge')) {
            JiFormsHelper::addSubmenu('forms');
        }

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

        JToolbarHelper::title(JText::_('COM_JIFORMS_FORMS'), 'jiforms.png');

        JToolbarHelper::addNew('form.add');
        JToolbarHelper::editList('form.edit');
        JToolbarHelper::publish('forms.publish', 'JTOOLBAR_PUBLISH', true);
        JToolbarHelper::unpublish('forms.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::archiveList('forms.archive');
        JToolbarHelper::checkin('forms.checkin');
        if($this->state->get('filter.published') == -2) {
            JToolbarHelper::deleteList('', 'forms.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('forms.trash');
        }
        // Add a batch button
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');
            $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
                        <i class=\"icon-checkbox-partial\" title=\"$title\"></i>
                        $title</button>";
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        JToolbarHelper::preferences('com_jiforms', '400');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_jiforms&view=forms');

            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_PUBLISHED'),
                'filter_published',
                JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
            );

            $this->sidebar = JHtmlSidebar::render();
        }
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
            'title' => JText::_('COM_JIFORMS_TITLE_LABEL'),
            'publish_up' => JText::_('COM_JIFORMS_PUBLISHUP_LABEL'),
            'publish_down' => JText::_('COM_JIFORMS_PUBLISHDOWN_LABEL'),
            'id' => JText::_('JGRID_HEADING_ID')
        );
    }
}