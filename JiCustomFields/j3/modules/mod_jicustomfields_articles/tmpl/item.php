<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: item.php 051 2014-10-26 14:53:00Z Anton Wintergerst $
 * @package     JiCustomFields Articles Module for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Create a shortcut for params.
$params = $item->params;
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$canEdit = $item->params->get('access-edit');
$info    = $params->get('info_block_position', 0);
?>
<?php if ($item->state == 0 || strtotime($item->publish_up) > strtotime(JFactory::getDate())
    || ((strtotime($item->publish_down) < strtotime(JFactory::getDate())) && $item->publish_down != '0000-00-00 00:00:00' )) : ?>
    <div class="system-unpublished">
<?php endif; ?>

<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>

<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
    <?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $item, 'print' => false)); ?>
<?php endif; ?>

<?php if ($params->get('show_tags') && !empty($item->tags->itemTags)) : ?>
    <?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
<?php endif; ?>

<?php // Todo Not that elegant would be nice to group the params ?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
    || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') ); ?>

<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
    <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
<?php endif; ?>

<?php echo JLayoutHelper::render('joomla.content.intro_image', $item); ?>


<?php if (!$params->get('show_intro')) : ?>
    <?php echo $item->event->afterDisplayTitle; ?>
<?php endif; ?>
<?php echo $item->event->beforeDisplayContent; ?> <?php echo $item->introtext; ?>

<?php if ($useDefList && ($info == 1 || $info == 2)) : ?>
    <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
<?php  endif; ?>

<?php if ($params->get('show_readmore') && $item->readmore) :
    if ($params->get('access-view')) :
        $link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
    else :
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $itemId = $active->id;
        $link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
        $returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
        $link = new JUri($link1);
        $link->setVar('return', base64_encode($returnURL));
    endif; ?>

    <?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $item, 'params' => $params, 'link' => $link)); ?>

<?php endif; ?>

<?php if ($item->state == 0 || strtotime($item->publish_up) > strtotime(JFactory::getDate())
    || ((strtotime($item->publish_down) < strtotime(JFactory::getDate())) && $item->publish_down != '0000-00-00 00:00:00' )) : ?>
    </div>
<?php endif; ?>

<?php echo $item->event->afterDisplayContent; ?>