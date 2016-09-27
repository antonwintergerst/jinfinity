<?php
/**
 * @version     $Id: view.html.php 016 2014-03-04 12:32:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
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
class JiExtensionServerViewToken extends JViewLegacy
{
    protected $form;
    protected $item;
    protected $state;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->setLayout('edit');

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
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $bar = JToolBar::getInstance('toolbar');
            $dhtml = '<div class="btn-group hidden-phone token" id="toolbar-token">
				<span class="btn btn-small btn-warning" onclick="Joomla.submitbutton(\'token.check\')" rel="tooltip" title="'.JText::_('COM_JIEXTENSIONSERVER_CHECKTOKEN_DESC').'">
					<i class="icon-refresh"></i> '.JText::_('COM_JIEXTENSIONSERVER_CHECKTOKEN').'
				</span>
			</div>';
            $bar->appendButton('Custom', $dhtml, 'token');
        } else {
            JToolBarHelper::custom('token.check', 'refresh.png', 'refresh_f2.png', 'COM_JIEXTENSIONSERVER_CHECKTOKEN', false);
        }
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $this->sidebar = JHtmlSidebar::render();
        }
        JToolbarHelper::title(JText::_('COM_JIEXTENSIONSERVER_TOKEN'), 'jiextensionserver.png');
    }
}