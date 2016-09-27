<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: route.php 018 2014-11-13 23:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
// #Jinfinity
abstract class JiCustomFieldsHelperRoute
{
    protected static $lookup = array();

    protected static $lang_lookup = array();

    /**
     * @param   integer  The route of the content item
     */
    public static function getArticleRoute($id, $catid = 0, $language = 0)
    {
        $needles = array(
            'article'  => array((int) $id)
        );

        // Create the link
        // #Jinfinity
        //$link = 'index.php?option=com_content&view=article&id=' . $id;
        $link = 'index.php?option=com_jicustomfields&view=article&id=' . $id;

        if ((int) $catid > 1)
        {
            $categories = JCategories::getInstance('Content');
            $category   = $categories->get((int) $catid);

            if ($category)
            {
                $needles['category']   = array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&catid=' . $catid;
            }
        }

        if ($language && $language != "*" && JLanguageMultilang::isEnabled())
        {
            self::buildLanguageLookup();

            if (isset(self::$lang_lookup[$language]))
            {
                $link .= '&lang=' . self::$lang_lookup[$language];
                $needles['language'] = $language;
            }
        }

        if ($item = self::_findItem($needles))
        {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof JCategoryNode)
        {
            $id       = $catid->id;
            $category = $catid;
        }
        else
        {
            $id       = (int) $catid;
            $category = JCategories::getInstance('Content')->get($id);
        }

        if ($id < 1 || !($category instanceof JCategoryNode))
        {
            $link = '';
        }
        else
        {
            $needles               = array();
            // #Jinfinity
            //$link                  = 'index.php?option=com_content&view=category&id=' . $id;
            $link                  = 'index.php?option=com_jicustomfields&view=category&id=' . $id;

            $catids                = array_reverse($category->getPath());
            $needles['category']   = $catids;
            $needles['categories'] = $catids;

            if ($language && $language != "*" && JLanguageMultilang::isEnabled())
            {
                self::buildLanguageLookup();

                if(isset(self::$lang_lookup[$language]))
                {
                    $link .= '&lang=' . self::$lang_lookup[$language];
                    $needles['language'] = $language;
                }
            }

            if ($item = self::_findItem($needles))
            {
                $link .= '&Itemid=' . $item;
            }
        }

        return $link;
    }

    // #Jinfinity
    public static function getSearchRoute($catid=0, $language = 0)
    {
        // assume catid to be uncategorised
        //if($catid==0) $catid = 2;

        $needles               = array();
        $needles['view'] = 'search';
        $link                  = 'index.php?option=com_jicustomfields&view=search';

        if ($language && $language != "*" && JLanguageMultilang::isEnabled())
        {
            self::buildLanguageLookup();

            if(isset(self::$lang_lookup[$language]))
            {
                $link .= '&lang=' . self::$lang_lookup[$language];
                $needles['language'] = $language;
            }
        }

        $db  = JFactory::getDBO();
        $db->setQuery('SELECT `id`, `params` FROM #__menu WHERE `published`=1 AND `link` LIKE '. $db->Quote($link));
        $items = ($db->getErrorNum())? 0 : $db->loadObjectList();
        $itemId = '';
        $exactmatch = false;
        if(count($items)==1) {
            // only one menu item, use that
            $itemId = $items[0]->id;
        } else if(count($items)>1) {
            foreach($items as &$item) {
                $params = new JRegistry($item->params);

                $item->filter = $params->get('filter_category', array());

                if(count($item->filter)==1) {
                    // filter has a single item
                    if((int)$item->filter[0]==0) {
                        // filter is all categories
                        $itemId = (int)$item->id;
                    } else if((int)$item->filter[0]==$catid) {
                        // filter is a single category
                        $itemId = (int)$item->id;
                        $exactmatch = true;
                    }
                } else if(in_array($catid, $item->filter)) {
                    $itemId = (int)$item->id;
                }
                if($exactmatch) break;
            }
        }
        // try again for parent categories
        if($catid!=0 && count($items)>0 && !$exactmatch) {
            foreach($items as &$item) {
                if(isset($item->filter) && count($item->filter)==1 && (int)$item->filter[0]!=0) {
                    // filter has a single item
                    $query = $db->getQuery(true);
                    $query->select('c.id');
                    $query->from('#__categories AS p');
                    $query->join('left', '#__categories AS c ON (c.lft>p.lft AND c.id='.(int)$catid.')');
                    $query->where('p.id='.(int)$item->filter[0]);

                    $db->setQuery($query);
                    $ischild = $db->loadResult();
                    if($ischild) {
                        $itemId = (int)$item->id;
                        $exactmatch = true;
                    }
                }
                if($exactmatch) break;
            }
        }

        if($itemId!='') $link .= '&Itemid=' . $itemId;

        /*if ($item = self::_findItem($needles))
        {
            $link .= '&Itemid=' . $item;
        }*/

        return $link;
    }

    public static function getFormRoute($id)
    {
        // Create the link
        if ($id)
        {
            $link = 'index.php?option=com_content&task=article.edit&a_id=' . $id;
        }
        else
        {
            $link = 'index.php?option=com_content&task=article.edit&a_id=0';
        }

        return $link;
    }

    protected static function buildLanguageLookup()
    {
        if (count(self::$lang_lookup) == 0)
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('a.sef AS sef')
                ->select('a.lang_code AS lang_code')
                ->from('#__languages AS a');

            $db->setQuery($query);
            $langs = $db->loadObjectList();

            foreach ($langs as $lang)
            {
                self::$lang_lookup[$lang->lang_code] = $lang->sef;
            }
        }
    }

    protected static function _findItem($needles = null)
    {
        $app      = JFactory::getApplication();
        $menus    = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language]))
        {
            self::$lookup[$language] = array();

            // #Jinfinity
            //$component  = JComponentHelper::getComponent('com_content');
            $component  = JComponentHelper::getComponent('com_jicustomfields');

            $attributes = array('component_id');
            $values     = array($component->id);

            if ($language != '*')
            {
                $attributes[] = 'language';
                $values[]     = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    $view = $item->query['view'];

                    if (!isset(self::$lookup[$language][$view]))
                    {
                        self::$lookup[$language][$view] = array();
                    }

                    if (isset($item->query['id']))
                    {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
                        {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$language][$view]))
                {
                    foreach ($ids as $id)
                    {
                        if (isset(self::$lookup[$language][$view][(int) $id]))
                        {
                            return self::$lookup[$language][$view][(int) $id];
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();

        // #Jinfinity
        if ($active && ($active->component == 'com_content' || $active->component=='com_jicustomfields') && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
        {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }
}
