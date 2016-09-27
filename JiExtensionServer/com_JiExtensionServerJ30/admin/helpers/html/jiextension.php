<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Utility class for categories
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.5
 */
abstract class JHtmlJiExtension
{
    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  1.5
     */
    protected static $items = array();

    /**
     * Returns an array of categories for the given extension.
     *
     * @param   string  $extension  The extension option e.g. com_something.
     * @param   array   $config     An array of configuration options. By default, only
     *                              published and unpublished categories are returned.
     *
     * @return  array
     *
     * @since   1.5
     */
    public static function options($config = array('filter.published' => array(0, 1)))
    {
        $hash = md5(serialize($config));

        if (!isset(self::$items[$hash]))
        {
            $config = (array) $config;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, title');
            $query->from('#__jiextensions');

            // Filter on the published state
            if (isset($config['filter.published']))
            {
                if (is_numeric($config['filter.published']))
                {
                    $query->where('state = ' . (int) $config['filter.published']);
                }
                elseif (is_array($config['filter.published']))
                {
                    JArrayHelper::toInteger($config['filter.published']);
                    $query->where('state IN (' . implode(',', $config['filter.published']) . ')');
                }
            }

            $query->order('title');

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            self::$items[$hash] = array();

            foreach ($items as &$item)
            {
                self::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
            }
        }

        return self::$items[$hash];
    }
}
