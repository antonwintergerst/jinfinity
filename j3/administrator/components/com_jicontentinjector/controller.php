<?php
/*
 * @version     $Id: controller.php 010 2013-06-05 18:19:00Z Anton Wintergerst $
 * @package     Jinfinity Content Injector for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
if(version_compare(JVERSION, '3.0.0', 'ge')) {
	class JiContentInjectorController extends JControllerLegacy
	{
        /**
         * @var		string	The default view.
         */
        protected $default_view = 'injections';

        /**
         * Method to display a view.
         *
         * @param	boolean			If true, the view output will be cached
         * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
         *
         * @return	JController		This object to support chaining.
         *
         */
	    public function display($cachable = false, $urlparams = false)
	    {
            $view = $this->input->get('view', 'injections');
            $layout = $this->input->get('layout', 'injections');
            $id = $this->input->getInt('id');

            // Check for edit form.
            if ($view == 'injection' && $layout == 'edit' && !$this->checkEditId('com_jicontentinjector.edit.injection', $id)) {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_('index.php?option=com_jicontentinjector&view=injections', false));

                return false;
            }

            parent::display();

            return $this;
	    }
	}
} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
	// <2.5 Legacy
	jimport('joomla.application.component.controller');
	class JiContentInjectorController extends JController
	{
        /**
         * @var		string	The default view.
         */
        protected $default_view = 'injections';

        /**
         * Method to display a view.
         *
         * @param	boolean			If true, the view output will be cached
         * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
         *
         * @return	JController		This object to support chaining.
         *
         */
	    public function display($cachable = false, $urlparams = false)
	    {
            $view = $this->input->get('view', 'injections');
            $layout = $this->input->get('layout', 'injections');
            $id = $this->input->getInt('id');

            // Check for edit form.
            if ($view == 'injection' && $layout == 'edit' && !$this->checkEditId('com_jicontentinjector.edit.injection', $id)) {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                $this->setMessage($this->getError(), 'error');
                $this->setRedirect(JRoute::_('index.php?option=com_jicontentinjector&view=injections', false));

                return false;
            }

            parent::display();

            return $this;
	    }
	}
}