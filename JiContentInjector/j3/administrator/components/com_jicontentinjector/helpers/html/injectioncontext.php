<?php
/*
 * @version     $Id: injectioncontext.php 010 2013-06-06 12:18:00Z Anton Wintergerst $
 * @package     Jinfinity Content Injector for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

abstract class JHtmlInjectionContext
{
    protected static $items = array();
    public static function options($extension)
    {
        $hash = md5($extension);

        if (!isset(self::$items[$hash]))
        {

            $items = array();
            $context = new stdClass();
            $context->value = 'content';
            $context->text = JText::_('COM_JICONTENTINJECTOR_CONTEXT_CONTENT');
            $items[] = $context;
            $context = new stdClass();
            $context->value = 'body';
            $context->text = JText::_('COM_JICONTENTINJECTOR_CONTEXT_BODY');
            $items[] = $context;
            $context = new stdClass();
            $context->value = 'everywhere';
            $context->text = JText::_('COM_JICONTENTINJECTOR_CONTEXT_EVERYWHERE');
            $items[] = $context;

            // Assemble the list options.
            self::$items[$hash] = array();

            foreach ($items as &$item)
            {
                self::$items[$hash][] = JHtml::_('select.option', $item->value, $item->text);
            }
        }

        return self::$items[$hash];
    }
}