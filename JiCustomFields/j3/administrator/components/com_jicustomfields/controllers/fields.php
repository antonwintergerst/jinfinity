<?php 
/**
 * @version     $Id: fields.php 051 2014-03-23 19:36:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsControllerFields extends JControllerAdmin
{
    function __construct()
    {
        parent::__construct();
		// Register Extra tasks
		$this->registerTask('fields.apply', 'apply');
    }
    function apply()
    {
        $model = $this->getModel('fields');
        $response = $model->store();
        
        $link = 'index.php?option=com_jicustomfields&view=fields';
        $this->setRedirect($link, $response->msg);
    }
    function remove()
    {
        $model = $this->getModel('fields');
        $response = $model->delete();
        
        $link = 'index.php?option=com_jicustomfields&view=fields';
        $this->setRedirect($link, $response->msg);
    }
    public function getModel($name = 'Field', $prefix = 'JiCustomFieldsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}