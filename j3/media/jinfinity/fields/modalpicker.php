<?php 
/**
 * @version     $Id: modalpicker.php 072 2013-08-22 15:51:00Z Anton Wintergerst $
 * @package     JiModalPicker Field for Joomla 1.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare( JVERSION, '1.6.0', 'ge' )) {
    // Joomla 1.7+
    class JFormFieldModalPicker extends JFormField
    {
        protected $type = 'modalpicker';

        protected function getInput()
        {
            $this->params = $this->element->attributes();

            $document = JFactory::getDocument();
            if(version_compare( JVERSION, '3.0.0', 'ge' )) {
                $options = array(
                    'slimbox2'=>'Slimbox 2',
                    'shadowbox'=>'Shadowbox',
                    'fancybox'=>'fancyBox'
                );
            } else {
                // Joomla 2.5 Legacy
                $options = array(
                    'slimbox2'=>'Slimbox 2',
                    'shadowbox'=>'Shadowbox',
                    'squeezebox'=>'SqueezeBox',
                    'fancybox'=>'fancyBox'
                );

                // MooTools Modals
                JHtml::_('behavior.framework', true);
                // SqueezeBox
                $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/squeezebox/squeezebox.css');
                $document->addScript(JURI::root(true).'/media/jinfinity/modals/squeezebox/squeezebox.js');
                $document->addScriptDeclaration('window.addEvent("domready", function() {SqueezeBox.assign($$("a[rel=squeezebox]"));});');
            }

            // jQuery Modals
            if(version_compare( JVERSION, '3.0.0', 'ge' )) {
                JHtml::_('jquery.framework');
            } else {
                // Joomla 2.5 Legacy
                $document->addScript(JURI::root(true).'/media/jinfinity/js/jquery.min.js');
                $document->addScript(JURI::root(true).'/media/jinfinity/js/jquery.noconflict.js');
            }
            // Slimbox2
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/slimbox2/js/slimbox2.js');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/slimbox2/css/slimbox2.css');
            // Shadowbox
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/shadowbox/shadowbox.js');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/shadowbox/shadowbox.css');
            // fancyBox
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/fancybox/jquery.fancybox.pack.js?v=2.1.5');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/fancybox/jquery.fancybox.css?v=2.1.5');

            $document->addScript(JURI::root(true).'/media/jinfinity/js/modalpickerJ25.js');
            $jsparams = array(
                'attrinput'=>$this->get('attrinput')
            );
            $document->addScriptDeclaration("
            jQuery(document).ready(function() {
                jQuery('#jform_params_modaltype').jimodalpicker('".json_encode($jsparams)."');
            });");
            ob_start(); ?>
            <div style="float: left;">
                <div>
                    <select id="jform_params_modaltype" name="jform[params][modaltype]">
                        <?php foreach($options as $value=>$label): ?>
                            <?php $selected = ($this->value==$value)? ' selected="selected"': ''; ?>
                            <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="modalpreview">
                    <a id="modalpreviewlink" href="<?php echo JURI::root(true).'/media/jinfinity/images/preview.jpg'; ?>" target="_blank">
                        <img src="<?php echo JURI::root(true).'/media/jinfinity/images/preview_thumb.jpg'; ?>" alt="" />
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
} else {
    // Joomla 1.5 Legacy
    jimport('joomla.html.parameter.element');
    class JElementModalPicker extends JElement {

        var $_name = 'modalpicker';

        function fetchElement($name, $value, &$node, $control_name){
            $document = JFactory::getDocument();
            $options = array(
                'slimbox2'=>'Slimbox 2',
                'shadowbox'=>'Shadowbox',
                'squeezebox'=>'SqueezeBox',
                'fancybox'=>'fancyBox'
            );

            // MooTools Modals
            $document->addScript(JURI::root(true).'/media/jinfinity/js/mootools-core.js');
            $document->addScript(JURI::root(true).'/media/jinfinity/js/mootools-more.js');

            // SqueezeBox
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/squeezebox/squeezebox.js');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/squeezebox/squeezebox.css');
            $document->addCustomTag('<script type="text/javascript">window.addEvent("domready", function() {SqueezeBox.assign($$("a[rel=squeezebox]"));});</script>');

            // jQuery Modals
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/jquery.min.js');
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/jquery.noconflict.js');

            $document->addScript(JURI::root(true).'/media/jinfinity/js/modalpickerJ15.js');
            // Slimbox2
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/slimbox2/js/slimbox2.js');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/slimbox2/css/slimbox2.css');
            // Shadowbox
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/shadowbox/shadowbox.js');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/shadowbox/shadowbox.css');
            // fancyBox
            $document->addScript(JURI::root(true).'/media/jinfinity/modals/fancybox/jquery.fancybox.pack.js?v=2.1.4');
            $document->addStyleSheet(JURI::root(true).'/media/jinfinity/modals/fancybox/jquery.fancybox.css?v=2.1.4');
            ob_start(); ?>
            <div style="float: left;">
                <div>
                    <select id="paramsmodaltype" name="params[modaltype]" class="inputbox">
                        <?php foreach($options as $value=>$label): ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="modalpreview">
                    <a id="modalpreviewlink" href="<?php echo JURI::root(true).'/media/jinfinity/images/preview.jpg'; ?>" target="_blank">
                        <img src="<?php echo JURI::root(true).'/media/jinfinity/images/preview_thumb.jpg'; ?>" alt="" />
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function fetchTooltip($label, $description, &$node, $control_name, $name){
            return NULL;
        }
    }
}