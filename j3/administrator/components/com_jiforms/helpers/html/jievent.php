<?php
/**
 * @version     $Id: jievent.php 012 2013-09-03 08:44:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

defined('JPATH_BASE') or die;

abstract class JHtmlJiEvent
{
    protected static $items = array();

    public static function options($config = array('filter.published' => array(0, 1)))
    {
        $hash = md5(serialize($config));

        if (!isset(self::$items[$hash]))
        {
            $config = (array) $config;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('e.title, e.alias');
            $query->from('#__jiforms_events AS e');

            // Filter on the published state
            if (isset($config['filter.published']))
            {
                if (is_numeric($config['filter.published']))
                {
                    $query->where('e.state = ' . (int) $config['filter.published']);
                }
                elseif (is_array($config['filter.published']))
                {
                    JArrayHelper::toInteger($config['filter.published']);
                    $query->where('e.state IN (' . implode(',', $config['filter.published']) . ')');
                }
            }

            $query->order('e.title');

            $db->setQuery($query);
            $items = $db->loadObjectList('alias');

            // Assemble the list options.
            self::$items[$hash] = array();
            // Add system events
            $systemevents = array(
                'beforeload'=>'COM_JIFORMS_EVENT_BEFORELOAD',
                'onload'=>'COM_JIFORMS_EVENT_ONLOAD',
                'onsubmit'=>'COM_JIFORMS_EVENT_ONSUBMIT',
                'validsuccess'=>'COM_JIFORMS_EVENT_VALIDATESUCCESS',
                'validfail'=>'COM_JIFORMS_EVENT_VALIDATEFAIL',
                'aftersubmit'=>'COM_JIFORMS_EVENT_AFTERSUBMIT'
            );
            foreach($systemevents as $alias=>$title) {
                $event = new stdClass();
                $event->alias = $alias;
                $event->title = JText::_(strtoupper($title));
                $items[$event->alias] = $event;
            }

            foreach ($items as &$item)
            {
                self::$items[$hash][] = JHtml::_('select.option', $item->alias, $item->title);
            }
        }

        return self::$items[$hash];
    }
}