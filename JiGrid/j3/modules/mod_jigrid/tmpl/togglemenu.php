<?php
/**
 * @version     $Id: togglemenu.php 039 2014-12-19 13:59:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if($params->get('load_jquery', true)) JHtml::_('jquery.framework');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('media/mod_jigrid/css/jitogglemenu.css');
    JHTML::script('media/mod_jigrid/js/jquery.jitogglemenu.js');
} else {
    JHTML::_('stylesheet', 'jitogglemenu.css', 'media/mod_jigrid/css/');
    JHTML::_('script', 'jquery.jitogglemenu.js', 'media/mod_jigrid/js/');
}
?>
<div class="jitogglemenubar <?php echo 'has'.$params->get('btntype', 'text'); ?>">
    <a class="jitogglemenubtn btn btn-navbar" href="#">
        <?php if($params->get('btntype', 'text')=='text' || $params->get('btntype', 'text')=='texticon'): ?>
            <span class="btntext largetext"><?php echo $params->get('btntext', 'Menu'); ?></span>
        <?php endif; ?>
        <?php if($params->get('btntype', 'text')=='icon' || $params->get('btntype', 'text')=='texticon'): ?>
            <span class="btnicon">
                <?php if($params->get('btnicon', 'icon-bar')=='icon-bar'): ?>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </a>
    <div class="jitoggleheader largetext">
        <?php
        $document = JFactory::getDocument();
        $renderer = $document->loadRenderer('modules');
        echo $renderer->render($params->get('headerposition', 'toggleheader'), array('style' =>'raw'), null);
        ?>
    </div>
</div>
<ul class="nav jitogglemenu <?php echo $class_sfx;?>"<?php
$tag = '';
if ($params->get('tag_id') != null)
{
    $tag = $params->get('tag_id').'';
    echo ' id="'.$tag.'"';
}
?>>
<?php
foreach ($data->list as $i => &$item) :
    $class = 'item-'.$item->id;
    if ($item->id == $data->active_id) {
        $class .= ' current';
    }

    if (in_array($item->id, $data->path)) {
        $class .= ' active';
    }
    elseif ($item->type == 'alias') {
        $aliasToId = $item->params->get('aliasoptions');
        if (count($data->path) > 0 && $aliasToId == $data->path[count($data->path) - 1]) {
            $class .= ' active';
        }
        elseif (in_array($aliasToId, $data->path)) {
            $class .= ' alias-parent-active';
        }
    }

    if ($item->type == 'separator') {
        $class .= ' divider';
    }

    if ($item->deeper) {
        $class .= ' deeper';
    }

    if ($item->parent) {
        $class .= ' parent';
    }

    if (!empty($class)) {
        $class = ' class="'.trim($class) .'"';
    }

    echo '<li'.$class.'>';

    // Render the menu item.
    switch ($item->type) :
        case 'separator':
        case 'url':
        case 'component':
        case 'heading':
            require JModuleHelper::getLayoutPath('mod_jigrid', 'togglemenu_'.$item->type);
            break;

        default:
            require JModuleHelper::getLayoutPath('mod_jigrid', 'togglemenu_url');
            break;
    endswitch;

    // The next item is deeper.
    if ($item->deeper) {
        echo '<div class="nav-childouter"><div class="nav-childinner"><ul class="nav-child unstyled small">';
    }
    // The next item is shallower.
    elseif ($item->shallower) {
        echo '</li>';
        echo str_repeat('</ul></div></div></li>', $item->level_diff);
    }
    // The next item is on the same level.
    else {
        echo '</li>';
    }
endforeach;
?></ul>