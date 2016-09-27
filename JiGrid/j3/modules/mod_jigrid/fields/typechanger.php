<?php
/**
 * @version     $Id: typechanger.php 030 2013-07-17 11:30:00Z Anton Wintergerst $
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
        $attribs = $this->element->attributes();
        $value = isset($attribs[$key])? (string) $attribs[$key] : $default;
        return $value;
    }
    public function getLabel()
    {
        return;
    }
    public function getInput()
    {
        JHtml::_('jquery.framework');
        $types = json_encode(explode(',',$this->getAttribute('types')));
        $selector = $this->getAttribute('selector');
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    var selector = '#<?php echo $selector; ?>';
                    var types = jQuery.parseJSON('<?php echo $types; ?>');
                    function updateTypeOptions(currenttype) {
                        jQuery.each(types, function(index, type) {
                            (currenttype!=type)? jQuery('.hide-'+type).removeClass('hide') : jQuery('.hide-'+type).addClass('hide');
                            if(currenttype==type) {
                                jQuery('.'+type+'-only').removeClass('hide');
                            } else {
                                jQuery('.'+type+'-only').addClass('hide');
                            }
                        });
                    }
                    jQuery(selector).on('change', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        updateTypeOptions(jQuery(sender).val());
                    });
                    updateTypeOptions(jQuery(selector).val());
                    jQuery('.fieldtype').each(function(index, fieldtype) {
                        var oldParent = jQuery(fieldtype).parent().parent();
                        jQuery(oldParent).replaceWith(fieldtype);
                    });
                });
            }
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
}