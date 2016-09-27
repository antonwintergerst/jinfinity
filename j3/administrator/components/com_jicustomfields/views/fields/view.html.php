<?php
/**
 * @version     $Id: view.html.php 053 2014-05-06 10:16:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {
    }
}
class JiCustomFieldsViewFields extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null)
    {
        if ($this->getLayout()!=='modal')
        {
            if(version_compare(JVERSION, '1.6.0', 'ge')) JiCustomFieldsHelper::addSubmenu('fields');
        }

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

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

        JToolbarHelper::title(JText::_('JICUSTOMFIELDS_FIELDS'), 'jicustomfields.png');

        JToolbarHelper::addNew('field.add');
        JToolbarHelper::editList('field.edit');
        JToolbarHelper::publish('fields.publish', 'JTOOLBAR_PUBLISH', true);
        JToolbarHelper::unpublish('fields.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::archiveList('fields.archive');
        if($this->state->get('filter.published') == -2) {
            JToolbarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('fields.trash');
        }

        // TODO: Add a batch button
        /*if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');
            $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
                        <i class=\"icon-checkbox-partial\" title=\"$title\"></i>
                        $title</button>";
            $bar->appendButton('Custom', $dhtml, 'batch');
        }*/

        JToolbarHelper::preferences('com_jicustomfields', '400');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_jicustomfields&view=fields');

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
            'title' => JText::_('JICUSTOMFIELDS_TITLE'),
            'id' => JText::_('JGRID_HEADING_ID')
        );
    }
}