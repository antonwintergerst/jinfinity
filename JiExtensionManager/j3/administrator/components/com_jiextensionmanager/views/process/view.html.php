<?php
/**
 * @version     $Id: view.html.php 026 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

if(!class_exists('JViewLegacy')){
    class JViewLegacy extends JView {
    }
}
class JiExtensionManagerViewProcess extends JViewLegacy
{
    protected $form;
    protected $items;
    protected $state;

    public function display($tpl = null)
    {
        $jinput = JFactory::getApplication()->input;
        $action = $jinput->get('action');
        if ($action) {
            $model = $this->getModel();
            if ($action == 'install') {
                echo $model->install($jinput->get('id'), $jinput->getString('url'));
                die;
            } else if ($action == 'uninstall') {
                echo $model->uninstall($jinput->get('id'));
                die;
            }
        }

        $this->form = $this->get('Form');
        $this->items = $this->get('Items');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }
    protected function addToolbar()
    {
    }
}