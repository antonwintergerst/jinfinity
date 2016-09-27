<?php
/**
 * @version     $Id: helper.php 010 2013-07-10 18:16:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiGridModHelper
{
    public function getData($params) {
        $data = new stdClass();
        $moduletype = $params->get('moduletype', 'togglemenu');
        switch($moduletype) {
            case 'togglemenu':
            case 'selectmenu':
            default:
                $data->list = $this->getMenuList($params);
                $data->active = $this->getActiveMenu($params);
                $data->active_id = $data->active->id;
                $data->path = $data->active->tree;

                $data->showAll = $params->get('showAllChildren');
                break;
        }
        return $data;
    }
    /**
     * Get a list of the menu items.
     *
     * @param	JRegistry	$params	The module options.
     *
     * @return	array
     * @since	1.5
     */
    public static function getMenuList(&$params)
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu();

        // Get active menu item
        $active = self::getActiveMenu($params);
        $user = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        asort($levels);
        $key = 'menu_items' . $params . implode(',', $levels) . '.' . $active->id;
        $cache = JFactory::getCache('mod_menu', '');
        if (!($items = $cache->get($key)))
        {
            $path    = $active->tree;
            $start   = (int) $params->get('startLevel');
            $end     = (int) $params->get('endLevel');
            $showAll = $params->get('showAllChildren');
            $items   = $menu->getItems('menutype', $params->get('menutype'));

            $lastitem = 0;

            if ($items)
            {
                foreach($items as $i => $item)
                {
                    if (($start && $start > $item->level)
                        || ($end && $item->level > $end)
                        || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
                        || ($start > 1 && !in_array($item->tree[$start - 2], $path)))
                    {
                        unset($items[$i]);
                        continue;
                    }

                    $item->deeper     = false;
                    $item->shallower  = false;
                    $item->level_diff = 0;

                    if (isset($items[$lastitem]))
                    {
                        $items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
                        $items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
                        $items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
                    }

                    $item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

                    $lastitem     = $i;
                    $item->active = false;
                    $item->flink  = $item->link;

                    // Reverted back for CMS version 2.5.6
                    switch ($item->type)
                    {
                        case 'separator':
                        case 'heading':
                            // No further action needed.
                            continue;

                        case 'url':
                            if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
                            {
                                // If this is an internal Joomla link, ensure the Itemid is set.
                                $item->flink = $item->link . '&Itemid=' . $item->id;
                            }
                            break;

                        case 'alias':
                            // If this is an alias use the item id stored in the parameters to make the link.
                            $item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                            break;

                        default:
                            $router = JSite::getRouter();
                            if ($router->getMode() == JROUTER_MODE_SEF)
                            {
                                $item->flink = 'index.php?Itemid=' . $item->id;
                            }
                            else
                            {
                                $item->flink .= '&Itemid=' . $item->id;
                            }
                            break;
                    }

                    if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
                    {
                        $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                    }
                    else
                    {
                        $item->flink = JRoute::_($item->flink);
                    }

                    // We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
                    // when the cause of that is found the argument should be removed
                    $item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
                }

                if (isset($items[$lastitem]))
                {
                    $items[$lastitem]->deeper     = (($start?$start:1) > $items[$lastitem]->level);
                    $items[$lastitem]->shallower  = (($start?$start:1) < $items[$lastitem]->level);
                    $items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start?$start:1));
                }
            }

            $cache->store($items, $key);
        }
        return $items;
    }
    /**
     * Get active menu item.
     *
     * @param	JRegistry	$params	The module options.
     *
     * @return	object
     * @since	3.0
     */
    public static function getActiveMenu(&$params)
    {
        $menu = JFactory::getApplication()->getMenu();

        // Get active menu item from parameters
        if ($params->get('active')) {
            $active = $menu->getItem($params->get('active'));
        } else {
            $active = false;
        }

        // If no active menu, use current or default
        if (!$active) {
            $active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
        }

        return $active;
    }
}
?>
