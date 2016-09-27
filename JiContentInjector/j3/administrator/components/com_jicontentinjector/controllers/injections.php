<?php
/*
 * @version     $Id: injections.php 010 2013-06-05 18:19:00Z Anton Wintergerst $
 * @package     Jinfinity Content Injector for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiContentInjectorControllerInjections extends JControllerAdmin
{
    /**
     * Constructor.
     *
     * @param	array	$config	An optional associative array of configuration settings.

     * @return	JiContentInjectorInjections
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
    public function getModel($name = 'Injection', $prefix = 'JiContentInjectorModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return	void
     *
     */
    public function saveOrderAjax()
    {
        $pks = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        JArrayHelper::toInteger($pks);
        JArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return)
        {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }
}