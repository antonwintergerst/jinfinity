<?php
/**
 * @version     $Id: contact-form-success.php 030 2013-11-13 11:55:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Send a thankyou email to the user
$form->email->send("thankyou");
// Send a new enquiry notification email to admin
$form->email->send("newenquiry");
?>