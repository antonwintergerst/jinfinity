<?php
/**
 * @version     $Id: create-new-job.php 036 2014-11-25 11:13:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!isset($form) && isset($this->form)) $form = $this->form;
$app = JFactory::getApplication();
$app->setUserState('com_jiforms.form.fields', array('title', 'introtext', 'publish_up', 'publish_down', 'a_id', 'catid', 'baseurl'));

// find appropriate category for user
$categories = JHtml::_('category.options', 'com_content', array('filter.published', 1));
$user = JFactory::getUser();
foreach ($categories as $i => $option) {
    if($user->authorise('core.create', 'com_content.category.'.$option->value)!=true) {
        unset($categories[$i]);
    }
}
$catid = (isset($categories[0]))? $categories[0]->value : null;
if($catid==null) {
    // TODO: present notice to inform user of insufficient permissions
    return;
}

// setup date fields
JHtml::_('jquery.ui');
JHtml::_('behavior.calendar');

// setup text editor
$editor = JFactory::getEditor();
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            Calendar.setup({
                // Id of the input field
                inputField: 'publish_up',
                // Format of the input field
                ifFormat: "%Y-%m-%d %H:%M:%S",
                // Trigger for the calendar (button ID)
                button: 'publish_up-btn',
                // Alignment (defaults to "Bl")
                align: "Tl",
                singleClick: true,
                firstDay: 0
            });
            Calendar.setup({
                // Id of the input field
                inputField: 'publish_down',
                // Format of the input field
                ifFormat: "%Y-%m-%d %H:%M:%S",
                // Trigger for the calendar (button ID)
                button: 'publish_down-btn',
                // Alignment (defaults to "Bl")
                align: "Tl",
                singleClick: true,
                firstDay: 0
            });
        });
    }
</script>
<form action="<?php echo JRoute::_($form->url.'&event=submit'); ?>" name="newjobform" id="jiform_newjobform" method="post" class="jiform newjobform">
    <div class="jifield textfield title row-fluid">
        <label for="title" class="span2">Title</label>
        <div class="fieldinner">
            <input id="title" class="validate required alphaplus" type="text" value="<?php echo $form->data->get('title'); ?>" name="title">
        </div>
    </div>
    <div class="jifield textarea text row-fluid">
        <label for="text" class="span2">Description</label>
        <div class="fieldinner span10">
            <?php // render joomla text editor
            echo $editor->display('introtext', $form->data->get('introtext'), '100%', '500', '55', '30', false); ?>
        </div>
    </div>
    <?php // render custom fields
        echo render_jifieldinputs($form->data->get('id'), $catid);
    ?>
    <div class="jifield date publish_up row-fluid">
        <label for="text" class="span2">Available From</label>
        <div class="fieldinner span10">
            <div class="input-medium input-append">
                <input class="inputbox input-medium" type="text" id="publish_up" name="publish_up" value="<?php echo $form->data->get('publish_up', date('Y-m-d H:i:s', strtotime('yesterday'))); ?>" />
                <button type="button" id="publish_up-btn" class="btn">
                    <i class="icon-calendar"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="jifield date publish_down">
        <label for="text" class="span2">Available To</label>
        <div class="fieldinner span10">
            <div class="input-medium input-append">
                <input class="inputbox input-medium" type="text" id="publish_down" name="publish_down" value="<?php echo $form->data->get('publish_down', date('Y-m-d H:i:s', strtotime('today +1 month'))); ?>" />
                <button type="button" id="publish_down-btn" class="btn">
                    <i class="icon-calendar"></i>
                </button>
            </div>
        </div>
    </div>
    <input name="a_id" type="hidden" value="<?php echo $form->data->get('id'); ?>" />
    <input name="catid" type="hidden" value="<?php echo $catid; ?>" />
    <div class="jifield submit">
        <div class="fieldinner">
            <input id="submitbtn" name="submitbtn" value="Save Job" type="submit">
        </div>
    </div>
</form>