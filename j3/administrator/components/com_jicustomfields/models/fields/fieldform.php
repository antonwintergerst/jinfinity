<?php
/**
 * @version     $Id: fieldform.php 011 2014-03-29 11:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldFieldForm extends JFormField
{
    protected $type = 'fieldsform';

    protected function getLabel()
    {
        return null;
    }
    protected function getInput()
    {
        // Load Fields Model
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel');
        } else {
            $model = JModel::getInstance('Fields', 'JiCustomFieldsModel');
        }
        $item = new stdClass();
        if(is_array($this->value)) {
            $value = $this->value;
            $item->id = (isset($value['id']))? (int) $value['id'] : 0;
            $item->catid = (isset($value['catid']))? (int) $value['catid'] : 0;
        } else {
            $item->id = 0;
            $item->catid = 0;
        }
        $this->item = $item;

        $jinput = JFactory::getApplication()->input;
        $fieldid = (int) $jinput->get('id');

        // Get Field Values for article
        $values = $model->getValues($item, 'item');
        // Load jifield
        $jifield = $model->getJiField($fieldid, $item, $values);
        $this->jifield = $jifield;

        ob_start();
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'field'.DS.'tmpl'.DS.'form.php');
        $html = ob_get_clean();

        return $html;
    }
}