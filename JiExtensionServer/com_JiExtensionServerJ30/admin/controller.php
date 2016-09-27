<?php
/**
 * @version     $Id: controller.php 012 2013-06-14 22:49:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
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
class JiExtensionServerController extends JControllerLegacy
{
    /**
     * @var		string	The default view.
     */
    protected $default_view = 'extensions';

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
        $jinput = JFactory::getApplication()->input;
        $view = $jinput->get('view', 'extensions');
        $layout = $jinput->get('layout', 'extensions');
        $id = $jinput->getInt('id');

        // Check for edit form.
        if ($view == 'extension' && $layout == 'edit' && !$this->checkEditId('com_jiextensionserver.edit.extension', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_jiextensionserver&view=extensions', false));

            return false;
        }

        parent::display();

        return $this;
    }
}