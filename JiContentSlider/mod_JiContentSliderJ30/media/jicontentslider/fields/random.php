<?php 
/**
 * @version     $Id: random.php 056 2014-03-14 13:57:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/modules/mod_jicontentslider/helper.php');
if(version_compare( JVERSION, '1.6.0', 'ge' )) {
    // Joomla 1.7+
    class JFormFieldRandom extends JFormField
    {
        protected $type = 'random';
    
        protected function getInput()
        {
            $helper = new JiContentSliderHelper();
            $value = ($this->value!=null)? $this->value : $helper->randomString();
            
            $html = '<input id="'.$this->id.'" type="text" value="'.$value.'" name="'.$this->name.'" />';
            return $html;
        }
    }
} else {
    // Joomla 1.5 Legacy
    jimport('joomla.html.parameter.element');
    class JElementRandom extends JElement {
    
        var $_name = 'random';
    
        function fetchElement($name, $value, &$node, $control_name) {
            $helper = new JiContentSliderHelper();
            $value = ($value!=null)? $value : $helper->randomString();
            
            $html = '<input id="'.$control_name.'" type="text" value="'.$value.'" name="'.$control_name.'" />';
            return $html;
        }
    
        function fetchTooltip($label, $description, &$node, $control_name, $name) {
            
            return NULL;
        }
    }
}