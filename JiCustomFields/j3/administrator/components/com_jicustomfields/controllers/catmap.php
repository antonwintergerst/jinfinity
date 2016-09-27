<?php
/**
 * @version     $Id: catmap.php 001 2014-05-12 09:20:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsControllerCatMap extends JControllerForm
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function batch($model = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Set the model
        $model = $this->getModel('CatMap', '', array());

        // Preset the redirect
        $this->setRedirect(JRoute::_('index.php?option=com_jicustomfields&view=catmaps' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}