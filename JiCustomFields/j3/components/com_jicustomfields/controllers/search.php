<?php
/**
 * @version     $Id: search.php 030 2014-10-28 14:32:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsControllerSearch extends JiCustomFieldsController
{
    public function clear()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $app->setUserState('com_jicustomfields.fieldsearch', null);
        $app->setUserState('com_jicustomfields.searchword', null);
        $app->redirect(JRoute::_(JiCustomFieldsHelperRoute::getSearchRoute($jinput->get('catid'))));
    }
}