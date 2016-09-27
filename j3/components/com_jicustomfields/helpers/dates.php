<?php
/*
 * @version     $Id: dates.php 015 2012-09-20 11:39:00Z Anton Wintergerst $
 * @package     Jinfinity Framework for Joomla 2.5
 * @copyright   Copyright (C) 2012 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access
defined('_JEXEC') or die;

class JinfinityDatesHelper {
    function getDateSpans($source, $format='d/m/Y') {
        $fdate = date($format, strtotime($source));
        $html = '';
        for($fm=0; $fm<strlen($format); $fm++):
            $subformat = substr($format, $fm, 1);
            $value = date($subformat, strtotime($source));
            $class = preg_replace('/[^a-zA-Z0-9\']/', 'separator', $subformat);
            $html.= '<span class="fm'.$class.'">'.$value.'</span>';
        endfor;
        
        return $html;
    }
}