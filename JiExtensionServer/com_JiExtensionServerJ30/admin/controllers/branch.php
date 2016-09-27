<?php
/**
 * @version     $Id: branch.php 015 2013-06-18 10:25:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

class JiExtensionServerControllerBranch extends JControllerForm
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
        //print_r($this->input->post->get('batch', array(), 'array')); die;
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        $model = $this->getModel('Branch', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_jiextensionserver&view=branches' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}