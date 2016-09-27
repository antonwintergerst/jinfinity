<?php
/**
 * @version     $Id: controller.php 010 2013-08-26 14:21:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
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
class JiFormsController extends JControllerLegacy
{
    protected $default_view = 'forms';

    public function display($cachable = false, $urlparams = false)
    {
        $view = $this->input->get('view', 'forms');
        $layout = $this->input->get('layout', 'forms');
        $id = $this->input->getInt('id');

        // Check for edit form.
        if ($view == 'form' && $layout == 'edit' && !$this->checkEditId('com_jiforms.edit.form', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_jiforms&view=forms', false));

            return false;
        }

        parent::display();

        return $this;
    }
}