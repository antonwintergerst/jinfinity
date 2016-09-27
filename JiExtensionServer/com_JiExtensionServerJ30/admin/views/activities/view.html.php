<?php
/**
 * @version     $Id: view.html.php 016 2014-01-04 11:36:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.x
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
class JiExtensionServerViewActivities extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null)
    {
        JiExtensionServerHelper::addSubmenu('activities');

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

        JToolbarHelper::title(JText::_('COM_JIEXTENSIONSERVER_ACTIVITY'), 'jiextensionserver.png');

        JToolbarHelper::preferences('com_jiextensionserver', '400');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_jiextensionserver&view=activities');

            JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html');
            JHtmlSidebar::addFilter(
                JText::_('JOPTION_SELECT_EXTENSION'),
                'filter_eid',
                JHtml::_('select.options', JHtml::_('jiextension.options'), 'value', 'text', $this->state->get('filter.eid'), true)
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