<?php 
/**
 * @version     $Id: modalpicker.php 071 2014-12-18 10:48:00Z Anton Wintergerst $
 * @package     JiSocialWidgets Content Plugin for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldModalPicker extends JFormField
{
    protected $type = 'modalpicker';

    protected function getInput()
    {
        $this->params = $this->element->attributes();

        $document = JFactory::getDocument();
        $options = array(
            'custom'=>'Custom',
            'slimbox2'=>'Slimbox 2',
            'shadowbox'=>'Shadowbox',
            'fancybox'=>'fancyBox'
        );

        // jQuery Modals
        if(version_compare( JVERSION, '3.0.0', 'ge' )) {
            JHtml::_('jquery.framework');
        } else {
            // TODO: Joomla 2.5 Legacy
            $document->addScript(JURI::root().'media/jiframework/js/jquery.min.js');
            $document->addScript(JURI::root().'media/jiframework/js/jquery.noconflict.js');
        }
        $document->addScript(JURI::root().'media/jiframework/js/jquery.jimodalpicker.js');
        // Slimbox2
        $document->addScript(JURI::root().'media/jiframework/modals/slimbox2/js/slimbox2.js');
        $document->addStyleSheet(JURI::root().'media/jiframework/modals/slimbox2/css/slimbox2.css');
        // Shadowbox
        $document->addScript(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.js');
        $document->addStyleSheet(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.css');
        // fancyBox
        $document->addScript(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.pack.js?v=2.1.5');
        $document->addStyleSheet(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.css?v=2.1.5');

        $document->addScriptDeclaration("jQuery(document).ready(function() {
            jQuery('#jform_params_".$this->get('name')."').jimodalpicker({'name':'".$this->get('name')."', 'attrinput':'".$this->get('attrinput')."', 'group':'".$this->get('group')."'});
        });");
        ob_start(); ?>
        <div style="float: left;">
            <div>
                <select id="jform_params_<?php echo $this->get('name'); ?>" name="jform[params][<?php echo $this->get('name'); ?>]">
                    <?php foreach($options as $value=>$label): ?>
                        <?php $selected = ($this->value==$value)? ' selected="selected"': ''; ?>
                        <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="<?php echo $this->get('name'); ?>modalpreview">
                <a id="<?php echo $this->get('name'); ?>modalpreviewlink" href="<?php echo JURI::root().'media/jiframework/modals/preview.jpg'; ?>" target="_blank">
                    <img src="<?php echo JURI::root().'media/jiframework/modals/preview_thumb.jpg'; ?>" alt="" />
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get($var, $default = '')
    {
        return (isset($this->params[$var]) && (string) $this->params[$var] != '') ? (string) $this->params[$var] : $default;
    }
}