<?php
/**
 * @version     $Id: typechanger.php 035 2013-07-17 16:21:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');

class JFormFieldTypeChanger extends JFormField
{
    protected $type = 'TypeChanger';

    public function getAttribute($key, $default=null) {
        if($this->element) {
            $attribs = $this->element->attributes();
            $value = isset($attribs[$key])? (string) $attribs[$key] : $default;
        } else {
            $value = $default;
        }
        return $value;
    }
    public function getLabel()
    {
        return;
    }
    public function getInput()
    {
        JHtml::_('jquery.framework');
        JHtml::script('media/jigrid/js/jquery.jitypechanger.js');
        $types = json_encode(explode(',',$this->getAttribute('types')));
        $selector = $this->getAttribute('selector');
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    jQuery('#<?php echo $selector; ?>').jitypechanger({'types':jQuery.parseJSON('<?php echo $types; ?>')});
                });
            }
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
}