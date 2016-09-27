<?php
/**
 * @version     $Id: griditem.php 010 2014-02-24 21:37:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldGridItem extends JFormFieldList
{
    protected $type = 'GridItem';

    public function jiGetAttribute($key, $default=null) {
        if($this->element) {
            $attribs = $this->element->attributes();
            $value = isset($attribs[$key])? (string) $attribs[$key] : $default;
        } else {
            $value = $default;
        }
        return $value;
    }
    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        $query	= $db->getQuery(true);

        $query->select('`id`, `title`, `level`, `type`');
        $query->from('#__jigrid');
        $query->where('`state` = 1');
        // Filter by type (Handled by disabled options)
        /*$itemtype = $this->jiGetAttribute('itemtype');
        if($itemtype!=null) {
            $query->where('`type`='.$db->quote($itemtype));
        }*/

        $query->group('id, title, level, lft, rgt, parent_id');
        $query->order('lft ASC');

        // Set Query
        $db->setQuery( $query );
        // Load Data
        $items = $db->loadObjectList();

        $options = array();
        if($items!=null) {
            foreach($items as $item) {
                $html = '';
                for($i=0; $i<$item->level; $i++) {
                    $html.= '|—';
                }
                $html.= $item->title;
                $disabled = false;
                // Filter by type
                $itemtype = $this->jiGetAttribute('itemtype');
                if($itemtype!=null && $item->type!=$itemtype) {
                    $disabled = true;
                }

                $options[] = JHTML::_('select.option',  $item->id, $html, 'value', 'text', $disabled);
            }
        }
        return $options;
    }
}