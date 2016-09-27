<?php
/**
 * @version     $Id: gridattribtext.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('text');

class JFormFieldGridAttribText extends JFormFieldText
{
    protected $type = 'GridAttribText';

    public function get($var) {
        foreach($this->element->attributes() as $key=>$value) {
            if($key==$var) {
                return $value;
                break;
            }
        }
        return null;
    }
}