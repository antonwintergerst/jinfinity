<?php
/**
 * @version     $Id: dropdownmenu.php 010 2013-09-19 10:09:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if($params->get('load_jquery', true)) JHtml::_('jquery.framework');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('modules/mod_jigrid/assets/css/jidropdownmenu.css');
    JHTML::script('modules/mod_jigrid/assets/js/jquery.jidropdownmenu.js');
} else {
    JHTML::_('stylesheet', 'jidropdownmenu.css', 'modules/mod_jigrid/assets/css/');
    JHTML::_('script', 'jquery.jidropdownmenu.js', 'modules/mod_jigrid/assets/js/');
}
?>
<ul class="nav jidropdownmenu <?php echo $class_sfx;?>"<?php
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
                require JModuleHelper::getLayoutPath('mod_jigrid', 'dropdownmenu_'.$item->type);
                break;

            default:
                require JModuleHelper::getLayoutPath('mod_jigrid', 'dropdownmenu_url');
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