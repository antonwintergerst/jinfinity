<?php
/**
 * @version     $Id: contact-form-onsubmit.php 031 2014-11-05 11:47:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Add some data to the form for use later such as in the email templates
$form->data->set('baseurl', JURI::root());
?>