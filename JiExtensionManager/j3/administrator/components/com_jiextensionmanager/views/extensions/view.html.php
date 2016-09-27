<?php
/**
 * @version     $Id: view.html.php 032 2014-12-17 14:35:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
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
class JiExtensionManagerViewExtensions extends JViewLegacy
{
    protected $items;
    protected $state;

    function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        if(JFactory::getApplication()->input->get('task') == 'update') {
            $tpl = 'xml';
        } else {
            $this->addToolbar();
        }
        parent::display($tpl);
    }
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');

        JToolbarHelper::title(JText::_('JIEXTENSIONMANAGER'), 'jiextensionmanager.png');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            // Add Custom Buttons
            $dhtml = '<div class="btn-group hidden-phone refresh" id="toolbar-refresh">
                    <span class="refresh btn btn-small"" onclick="jimanager.refresh()" rel="tooltip" title="'.JText::_('JIEXTENSIONMANAGER_REFRESH').'">
                        <i class="icon-refresh"></i>
                    </span>
                </div>';
            $bar->appendButton('Custom', $dhtml, 'install');
            $dhtml = '<div class="btn-group hidden-phone installselected" id="toolbar-installselected">
                    <span class="btn btn-small btn-info hidden-phone" onclick="jimanager.controller(\'installselected\')" rel="tooltip" title="'.JText::_('JIEXTENSIONMANAGER_INSTALL_SELECTED_DESC').'">
                        <i class="icon-box-add"></i> '.JText::_('JIEXTENSIONMANAGER_INSTALL_SELECTED').'
                    </span>
                </div>';
            $bar->appendButton('Custom', $dhtml, 'install');
            $dhtml = '<div class="btn-group hidden-phone updateall" id="toolbar-updateall">
                    <span class="btn btn-small btn-warning" onclick="jimanager.controller(\'updateall\')" rel="tooltip" title="'.JText::_('JIEXTENSIONMANAGER_UPDATE_ALL_DESC').'">
                        <i class="icon-upload"></i> '.JText::_('JIEXTENSIONMANAGER_UPDATE_ALL').'
                    </span>
                </div>';
            $bar->appendButton('Custom', $dhtml, 'updateall');

            JToolbarHelper::preferences('com_jiextensionmanager', '400');
        } else {
            // Joomla 2.5 Legacy
            $this->btnToolbar = '
                <div class="jinfinity row-fluid">
                    <div class="span12">
                        <div id="toolbar" class="btn-toolbar has_install has_update">
                            <div id="toolbar-install" class="btn-group">
                                <div id="toolbar-refresh" class="btn-group hidden-phone refresh">
                                    <span rel="tooltip" onclick="jimanager.refresh()" class="refresh btn btn-small" data-original-title="Refresh"><i class="icon-refresh"></i></span>
                                </div>
                            </div>
                            <div id="toolbar-install" class="btn-group">
                                <div id="toolbar-installselected" class="btn-group hidden-phone installselected">
                                    <span rel="tooltip" onclick="jimanager.controller(\'installselected\')" class="btn btn-small btn-info hidden-phone" data-original-title=""><i class="icon-box-add"></i> Install Selected</span>
                                </div>
                            </div>
                            <div id="toolbar-updateall" class="btn-group">
                                <div id="toolbar-updateall" class="btn-group hidden-phone updateall">
                                    <span rel="tooltip" onclick="jimanager.controller(\'updateall\')" class="btn btn-small btn-warning" data-original-title=""><i class="icon-upload"></i> Update All</span>
                                </div>
                            </div>
                            <div id="toolbar-options" class="btn-group">
                                <a class="modal" rel="{handler: \'iframe\', size: {x: 875, y: 400}, onClose: function() {}}" href="index.php?option=com_config&view=component&component=com_jiextensionmanager&path=&tmpl=component">
                                    <span class="btn btn-small"><i class="icon-options"></i> Options</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>';
        }

        /*JHtmlSidebar::setAction('index.php?option=com_jiextensionmanager&view=extensions');

        JHtmlSidebar::addFilter(
            JText::_('JOPTION_SELECT_PUBLISHED'),
            'filter_published',
            JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
        );

        $this->sidebar = JHtmlSidebar::render();*/
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
            'title' => JText::_('JIEXTENSIONMANAGER_TITLE_LABEL'),
            'type' => JText::_('JIEXTENSIONMANAGER_TYPE_LABEL'),
            'installed' => JText::_('JIEXTENSIONMANAGER_INSTALLED_LABEL')
        );
    }
}