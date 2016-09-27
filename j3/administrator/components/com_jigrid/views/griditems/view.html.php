<?php
/**
 * @version     $Id: view.html.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
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
class JiGridViewGridItems extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        // Preprocess the list of items to find ordering divisions.
        foreach ($this->items as &$item)
        {
            $this->ordering[$item->parent_id][] = $item->id;
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

        JToolbarHelper::title(JText::_('COM_JIGRID_GRIDITEMS'), 'jigrid.png');

        JToolbarHelper::addNew('griditem.add');
        JToolbarHelper::editList('griditem.edit');

        JToolbarHelper::publish('griditems.publish', 'JTOOLBAR_PUBLISH', true);
        JToolbarHelper::unpublish('griditems.unpublish', 'JTOOLBAR_UNPUBLISH', true);

        if($this->state->get('filter.published') == -2) {
            JToolbarHelper::deleteList('', 'griditems.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('griditems.trash');
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
        JToolbarHelper::custom('griditems.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);

        JToolbarHelper::preferences('com_jigrid', '400');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_jigrid&view=griditems');

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
            'lft' => JText::_('JGRID_HEADING_ORDERING'),
            'state' => JText::_('JSTATUS'),
            'title' => JText::_('COM_JIGRID_TITLE_LABEL'),
            'alias' => JText::_('COM_JIGRID_ALIAS_LABEL'),
            'type' => JText::_('COM_JIGRID_TYPE_LABEL'),
            'id' => JText::_('JGRID_HEADING_ID')
        );
    }
}