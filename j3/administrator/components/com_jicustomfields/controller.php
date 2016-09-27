<?php
/**
 * @version     $Id: controller.php 010 2014-03-29 11:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!class_exists('JControllerLegacy')){
    class JControllerLegacy extends JView {
    }
}
class JiCustomFieldsController extends JControllerLegacy
{
    protected $default_view = 'fields';

    public function display($cachable = false, $urlparams = false)
    {
        $view = $this->input->get('view', 'fields');
        $layout = $this->input->get('layout', 'default');
        $id = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'field' && $layout == 'edit' && !$this->checkEditId('com_jicustomfields.edit.field', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_jicustomfields&view=fields', false));

            return false;
        }

        parent::display();

        return $this;
    }
}