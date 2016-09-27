<?php
/**
 * @version     $Id: griditems.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controlleradmin');

class JiGridControllerGridItems extends JControllerAdmin
{
    /**
     * Constructor.
     *
     * @param	array	$config	An optional associative array of configuration settings.

     * @return	JiGridItems
     * @see		JController
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    /**
     * Proxy for getModel.
     *
     * @param	string	$name	The name of the model.
     * @param	string	$prefix	The prefix for the PHP class name.
     *
     * @return	JModel
     */
    public function getModel($name = 'GridItem', $prefix = 'JiGridModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return	void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the arrays from the Request
        $pks   = $this->input->post->get('cid', null, 'array');
        $order = $this->input->post->get('order', null, 'array');
        $originalOrder = explode(',', $this->input->getString('original_order_values'));

        // Make sure something has changed
        if (!($order === $originalOrder)) {
            // Get the model
            $model = $this->getModel();
            // Save the ordering
            $return = $model->saveorder($pks, $order);
            if ($return)
            {
                echo "1";
            }
        }
        // Close the application
        JFactory::getApplication()->close();

    }
    /**
     * Rebuild the nested set tree.
     *
     * @return	bool	False on failure or error, true on success.
     * @since	1.6
     */
    public function rebuild()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $this->setRedirect(JRoute::_('index.php?option=com_jigrid&view=griditems', false));

        $model = $this->getModel();

        if ($model->rebuild()) {
            // Rebuild succeeded.
            $this->setMessage(JText::_('COM_JIGRID_REBUILD_SUCCESS'));
            return true;
        } else {
            // Rebuild failed.
            $this->setMessage(JText::_('COM_JIGRID_REBUILD_FAILURE'));
            return false;
        }
    }
}