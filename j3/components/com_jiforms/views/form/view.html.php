<?php
/**
 * @version     $Id: view.html.php 035 2013-10-01 15:26:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

if(!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {
    }
}
class JiFormsViewForm extends JViewLegacy
{
    public function display($tpl = null)
    {
        $jinput = JFactory::getApplication()->input;
        $event = $jinput->get('event', 'beforeload');
        $model = $this->getModel('form');
        $response = $model->eventHandler($event);
        if($response) {
            $this->form = $this->get('FormOnload');

            parent::display($tpl);
        }
    }
}