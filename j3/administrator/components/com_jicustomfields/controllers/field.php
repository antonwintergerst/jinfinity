<?php
/**
 * @version     $Id: field.php 010 2014-03-29 11:39:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsControllerField extends JControllerForm
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
        $model = $this->getModel('Field', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_jicustomfields&view=fields' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}