<?php
/**
 * @version     $Id: form.php 010 2013-08-26 14:21:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiFormsControllerForm extends JControllerForm
{
    /**
     * Class constructor.
     *
     * @param   array  $config  A named array of configuration variables.
     *
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean	 True if successful, false otherwise and internal error is set.
     *
     */
    public function batch($model = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        $model = $this->getModel('Form', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_jiforms&view=forms' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
    function preview() {
        $app = JFactory::getApplication();
        $model = $this->getModel('Form');
        header('Content-Type: text/html;charset=UTF-8');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
        echo $model->preview();
        $app->close();
        exit;
    }
}