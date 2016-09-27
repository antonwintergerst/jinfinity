<?php
/**
 * @version     $Id: typefield.php 036 2013-07-17 15:35:00Z Anton Wintergerst $
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

class JFormFieldTypeField extends JFormField
{
    protected $type = 'TypeField';

    public function getAttribute($key, $default=null) {
        if($this->element) {
            $attribs = $this->element->attributes();
            $value = isset($attribs[$key])? (string) $attribs[$key] : $default;
        } else {
            $value = $default;
        }
        return $value;
    }
    private function setAttribute($key, $value) {
        $attribs = $this->element->attributes();
        $attribs[$key] = $value;
    }
    public function getLabel() {
        $replace = $this->getAttribute('replace', 0);
        if($replace==1) {
            return;
        } else {
            $type = $this->getAttribute('rtype');
            $this->setAttribute('type', $type);
            $field = JFormHelper::loadFieldType($type, true);
            $field->setForm($this->form);
            $field->setup($this->element, $this->__get('value'), 'params');
            $html = $field->getLabel();
            return $html;
        }
    }
    public function getInput() {
        $replace = $this->getAttribute('replace', 0);
        $type = $this->getAttribute('rtype');
        if($type==null) return;
        $this->setAttribute('type', $type);
        $field = JFormHelper::loadFieldType($type, true);
        $field->setForm($this->form);
        $field->setup($this->element, $this->__get('value'), 'params');
        if($replace==1) {
            ob_start(); ?>
            <script type="text/javascript">
                if(typeof jQuery!='undefined') {
                    jQuery(document).ready(function() {
                        jQuery('.fid<?php echo $this->__get('id'); ?>').each(function(index, typefield) {
                            var oldParent = jQuery(typefield).parent().parent();
                            jQuery(oldParent).replaceWith(typefield);
                        });
                    });
                }
            </script>
            <div class="control-group typefield fid<?php echo $this->__get('id'); ?> <?php echo $this->getAttribute('controlclass',''); ?>">
                <div class="control-label"><?php echo $field->getLabel(); ?></div>
                <div class="controls"><?php echo $field->getInput(); ?></div>
            </div>
            <?php $html = ob_get_clean();
        } else {
            $html = $field->getInput();
        }
        return $html;
    }
}