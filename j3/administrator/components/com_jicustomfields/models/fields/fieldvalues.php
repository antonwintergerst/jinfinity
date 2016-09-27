<?php
/**
 * @version     $Id: fields.php 010 2014-04-07 08:47:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldFieldValues extends JFormFieldList
{

    protected $type = 'FieldValues';

    public function getOptions()
    {
        $options = array();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('`id`, `title`');
        $query->from('#__jifields');
        $query->where('`state`=1');
        $query->order('`title` ASC');
        $fields = $db->loadObjectList('id');

        if($fields!=null) {
            foreach($fields as $field) {
                $options[] = JHTML::_('select.option',  'f'.$field->id, 'All '.$field->title.' Values');
                /*$query = $db->getQuery(true);
                $query->select('v.`id`, v.`value`');
                $query->from('#__jifields_values');
                $query->order('`value` ASC');

                $options[] = JHTML::_('select.option',  'f'.$value->id.'v'.$field->value, $result->title);*/
            }
        }
        return $options;
    }
}