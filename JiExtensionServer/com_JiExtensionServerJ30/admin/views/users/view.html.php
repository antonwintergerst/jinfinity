<?php
/**
 * @version     $Id: view.html.php 015 2013-06-17 16:39:00Z Anton Wintergerst $
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
require_once(JPATH_SITE.'/administrator/components/com_users/helpers/users.php');
class JiExtensionServerViewUsers extends JViewLegacy
{
    protected $items;

    protected $pagination;

    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->state		= $this->get('State');

        $lang = JFactory::getLanguage();
        $lang->load('com_users', JPATH_ADMINISTRATOR);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Include the component HTML helpers.
        JHtml::addIncludePath(JPATH_SITE.'/administrator/components/com_users/helpers/html');

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar()
    {
        $canDo	= JHelperContent::getActions('com_users');

        JToolbarHelper::title(JText::_('COM_USERS_VIEW_USERS_TITLE'), 'user');

        if ($canDo->get('core.create')) {
            JToolbarHelper::addNew('user.add');
        }
        if ($canDo->get('core.edit')) {
            JToolbarHelper::editList('user.edit');
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::divider();
            JToolbarHelper::publish('users.activate', 'COM_USERS_TOOLBAR_ACTIVATE', true);
            JToolbarHelper::unpublish('users.block', 'COM_USERS_TOOLBAR_BLOCK', true);
            JToolbarHelper::custom('users.unblock', 'unblock.png', 'unblock_f2.png', 'COM_USERS_TOOLBAR_UNBLOCK', true);
            JToolbarHelper::divider();
        }

        if ($canDo->get('core.delete')) {
            JToolbarHelper::deleteList('', 'users.delete');
            JToolbarHelper::divider();
        }

        if ($canDo->get('core.admin')) {
            JToolbarHelper::preferences('com_users');
            JToolbarHelper::divider();
        }

        JToolbarHelper::help('JHELP_USERS_USER_MANAGER');

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtmlSidebar::setAction('index.php?option=com_users&view=users');

            JHtmlSidebar::addFilter(
                JText::_('COM_USERS_FILTER_STATE'),
                'filter_state',
                JHtml::_('select.options', UsersHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'))
            );

            JHtmlSidebar::addFilter(
                JText::_('COM_USERS_FILTER_ACTIVE'),
                'filter_active',
                JHtml::_('select.options', UsersHelper::getActiveOptions(), 'value', 'text', $this->state->get('filter.active'))
            );

            JHtmlSidebar::addFilter(
                JText::_('COM_USERS_FILTER_USERGROUP'),
                'filter_group_id',
                JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'))
            );

            JHtmlSidebar::addFilter(
                JText::_('COM_USERS_OPTION_FILTER_DATE'),
                'filter_range',
                JHtml::_('select.options', Usershelper::getRangeOptions(), 'value', 'text', $this->state->get('filter.range'))
            );
        }
    }
}