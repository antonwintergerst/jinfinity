<?php
/**
 * @version     $Id: jiform.php 010 2013-09-05 09:43:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldJiForm extends JFormFieldList
{
    protected $type = 'JiForm';

    public function getOptions()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query	= $db->getQuery(true);

        $query->select('f.id, f.title');
        $query->from('#__jiforms AS f');

        $query->where('f.state = 1');

        $query->order($db->escape('f.title ASC'));

        // Set Query
        $db->setQuery( $query );
        // Load Data
        $results = $db->loadObjectList();

        $options = array();
        if($results!=null) {
            foreach($results as $result) {
                $options[] = JHTML::_('select.option',  $result->id, $result->title);
            }
        }
        return $options;
    }
}