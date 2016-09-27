<?php
/**
 * @version     $Id: view.html.php 015 2013-06-17 20:09:00Z Anton Wintergerst $
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
class JiExtensionServerViewSubversions extends JViewLegacy
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
        if($this->getLayout()!=='modal') {
            $this->addToolbar();
        }

        parent::display($tpl);
    }
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('COM_JIEXTENSIONSERVER_SUBVERSIONS'), 'jiextensionserver.png');

        JToolbarHelper::addNew('subversion.add');
        JToolbarHelper::editList('subversion.edit');
        JToolbarHelper::publish('subversions.publish', 'JTOOLBAR_PUBLISH', true);
        JToolbarHelper::unpublish('subversions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::archiveList('subversions.archive');
        JToolbarHelper::checkin('subversions.checkin');
        if($this->state->get('filter.published') == -2) {
            JToolbarHelper::deleteList('', 'subversions.delete', 'JTOOLBAR_EMPTY_TRASH');
        } else {
            JToolbarHelper::trash('subversions.trash');
        }
        // Add a batch button
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');
            $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
                    <i class=\"icon-checkbox-partial\" title=\"$title\"></i>
                    $title</button>";
            $bar->appendButton('Custom', $dhtml, 'batch');
            $dhtml = '<div class="btn-group hidden-phone scan" id="toolbar-scan">
				<span class="btn btn-small btn-warning" onclick="Joomla.submitbutton(\'subversions.scan\')" rel="tooltip" title="'.JText::_('COM_JIEXTENSIONSERVER_SCAN_DESC').'">
					<i class="icon-refresh"></i> '.JText::_('COM_JIEXTENSIONSERVER_SCAN').'
				</span>
			</div>';
            $bar->appendButton('Custom', $dhtml, 'scan');
        } else {
            JToolBarHelper::custom('subversions.scan', 'refresh.png', 'refresh_f2.png', 'COM_JIEXTENSIONSERVER_SCAN', false);
        }

        JToolbarHelper::preferences('com_jiextensionserver', '400');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_jiextensionserver&view=subversions');

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
            'title' => JText::_('COM_JIEXTENSIONSERVER_TITLE_LABEL'),
            'alias' => JText::_('COM_JIEXTENSIONSERVER_ALIAS_LABEL'),
            'publisher' => JText::_('COM_JIEXTENSIONSERVER_PUBLISHER_LABEL'),
            'downloadhits' => JText::_('COM_JIEXTENSIONSERVER_DOWNLOADHITS_LABEL'),
            'updatehits' => JText::_('COM_JIEXTENSIONSERVER_UPDATEHITS_LABEL'),
            'publish_up' => JText::_('COM_JIEXTENSIONSERVER_PUBLISHUP_LABEL'),
            'publish_down' => JText::_('COM_JIEXTENSIONSERVER_PUBLISHDOWN_LABEL'),
            'id' => JText::_('JGRID_HEADING_ID')
        );
    }
}