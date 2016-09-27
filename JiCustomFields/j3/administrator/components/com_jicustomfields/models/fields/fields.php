<?php
/**
 * @version     $Id: fields.php 013 2014-06-19 09:33:00Z Anton Wintergerst $
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

class JFormFieldFields extends JFormFieldList
{

    protected $type = 'Fields';

    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query = 'SELECT `id`, `title` FROM #__jifields';
        //$query.= ' WHERE `type` IN ("tags", "select", "multiselect", "radio", "currency", "area")';
        $query.= ' ORDER BY `title` ASC';
        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();
        $options[] = JHTML::_('select.option',  0, 'All '.$this->type);
        $options[] = JHTML::_('select.option',  '', 'None');
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->id, $result->title);
            }
        }
        return $options;
    }
}