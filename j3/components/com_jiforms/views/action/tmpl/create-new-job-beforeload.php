<?php
/**
 * @version     $Id: contact-form-onsubmit.php 034 2014-11-25 11:13:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$app = JFactory::getApplication();
$jinput = $app->input;
$aid = (int)$jinput->get('a_id');

if($aid>0) {
    // check permission


    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('*');
    $query->from('#__content');
    $query->where('`id`='.$aid);
    $db->setQuery($query);
    $item = $db->loadObject();
    if($item) {
        // set article attributes
        foreach($item as $key=>$value) {
            $form->data->set($key, $value);
        }
    }
}
?>