<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: controller.php 050 2013-03-04 15:33:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class JiCustomFieldsController extends JControllerLegacy
{
    public function __construct($config = array())
    {
        $this->input = JFactory::getApplication()->input;

        // Article frontpage Editor pagebreak proxying:
        if ($this->input->get('view') === 'article' && $this->input->get('layout') === 'pagebreak')
        {
            //$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
            // #Jinfinity
            $config['base_path'] = JPATH_SITE.'/administrator/components/com_jicustomfields';
        }
        // Article frontpage Editor article proxying:
        elseif($this->input->get('view') === 'articles' && $this->input->get('layout') === 'modal')
        {
            JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
            //$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
            // #Jinfinity
            $config['base_path'] = JPATH_SITE.'/administrator/components/com_jicustomfields';
        }

        parent::__construct($config);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean         If true, the view output will be cached
     * @param   array           An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController     This object to support chaining.
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $cachable = true;

        // Set the default view name and format from the Request.
        // Note we are using a_id to avoid collisions with the router and the return page.
        // Frontend is a bit messier than the backend.
        $id    = $this->input->getInt('a_id');
        $vName = $this->input->getCmd('view', 'categories');
        $this->input->set('view', $vName);

        $user = JFactory::getUser();

        if ($user->get('id') ||
            ($this->input->getMethod() == 'POST' &&
                (($vName == 'category' && $this->input->get('layout') != 'blog') || $vName == 'archive' )) || $vName=='search') {
            $cachable = false;
        }

        $safeurlparams = array('catid' => 'INT', 'id' => 'INT', 'cid' => 'ARRAY', 'year' => 'INT', 'month' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
            'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD');

        // Check for edit form.
        //if ($vName == 'form' && !$this->checkEditId('com_content.edit.article', $id)) {
        // #Jinfinity
        if(($vName == 'form' && !$this->checkEditId('com_content.edit.article', $id)) && ($vName == 'form' && !$this->checkEditId('com_jicustomfields.edit.article', $id))) {
            // Somehow the person just went to the form - we don't allow that.
            return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
        }

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}