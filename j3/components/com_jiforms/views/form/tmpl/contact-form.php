<?php
/**
 * @version     $Id: contact-form.php 034 2014-11-05 11:47:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!isset($form) && isset($this->form)) $form = $this->form;
?>
<form action="<?php echo JRoute::_($form->url.'&event=submit'); ?>" name="enquiryform" id="jiform_enquiryform" method="post" class="jiform enquiryform">
    <div class="jifield textfield name">
        <label for="name">Name</label>
        <div class="fieldinner">
            <input id="name" class="validate required alphaplus" type="text" value="<?php echo $form->data->get('name'); ?>" name="name">
        </div>
    </div>
    <div class="jifield textfield email">
        <label for="email">Email</label>
        <div class="fieldinner">
            <input id="email" class="validate required email" type="text" value="<?php echo $form->data->get('email'); ?>" name="email">
        </div>
    </div>
    <div class="jifield textfield location">
        <label for="location">Location</label>
        <div class="fieldinner">
            <input id="location" class="validate required location" type="text" value="<?php echo $form->data->get('location'); ?>" name="location">
        </div>
    </div>
    <div class="jifield textfield message">
        <label for="message">Message</label>
        <div class="fieldinner">
            <textarea id="message" cols="45" rows="12" class="validate required" name="message"><?php echo $form->data->get('message'); ?></textarea>
        </div>
    </div>
    <?php echo $form->captcha->html(); ?>
    <div class="jifield submit">
        <div class="fieldinner">
            <input id="submitbtn" name="submitbtn" value="Send Enquiry" type="submit">
        </div>
    </div>
</form>