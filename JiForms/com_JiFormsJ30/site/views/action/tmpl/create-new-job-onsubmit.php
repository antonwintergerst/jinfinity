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

// TODO
$app = JFactory::getApplication();
print_r($app->getUserState('com_jiforms.form.data', array())); die;

// add some data to the form for use later such as in the email templates
$form->data->set('baseurl', JURI::root());

// prepare article data
$itemdata = array();
$itemdata['introtext'] = $form->data->get('introtext');
$itemdata['catid'] = $form->data->get('catid');
$itemdata['created'] = date('Y-m-d H:i:s');
$itemdata['modified'] = '00-00-00 00:00:00';
$itemdata['state'] = 1;
$itemdata['publish_up'] = $form->data->get('publish_up');
$itemdata['publish_down'] = $form->data->get('publish_down');
$itemdata['images'] = '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}';
$itemdata['urls'] = '{"urla":null,"urlatext":"","targeta":"","urlb":null,"urlbtext":"","targetb":"","urlc":null,"urlctext":"","targetc":""}';
$itemdata['attribs'] = '{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';
$itemdata['version'] = '1';
$itemdata['access'] = '1';
$itemdata['metadata'] = '{"robots":"","author":"","rights":"","xreference":"","tags":null}';
$itemdata['language'] = '*';

$item = JTable::getInstance('Content', 'JTable');
if((int)$form->data->get('a_id')>0) {
    // load existing article
    $item->load($form->data->get('a_id'));
}

$title = $form->data->get('title');
if(isset($item->title) && $title!=$item->title) {
    // ensure alias is safe and unique
    $alias = JApplicationHelper::stringURLSafe($title);
    $db = JFactory::getDBO();
    $query = 'SELECT `title` FROM #__content WHERE `alias`='.$db->quote($alias).' AND `catid`='.(int)$form->data->get('catid');
    $db->setQuery($query);
    $result = $db->loadObject();
    while($result!=null) {
        $alias = JString::increment($alias, 'dash');
        $query = 'SELECT `title` FROM #__content WHERE `alias`='.$db->quote($alias).' AND `catid`='.(int)$form->data->get('catid');
        $db->setQuery($query);
        $result = $db->loadObject();
    }
    $itemdata['title'] = $title;
    $itemdata['alias'] = $alias;
}

// save article
$item->bind($itemdata);
$item->store();

// save fields
set_jifields($item, 'com_content.article');
?>