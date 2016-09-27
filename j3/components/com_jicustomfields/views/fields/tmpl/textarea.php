<?php
/**
 * @version     $Id: textarea.php 088 2014-11-20 15:48:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldTextArea extends JiCustomField {
    public function renderInputScript() {
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldTextArea = function(container, data)
                {
                    var self = this;
                    // class functions
                    this.prepareInput = function(reload)
                    {
                        if(reload) this.reloadInput();
                    }
                    this.reloadInput = function()
                    {
                        // reload editor
                        var inputid = 'jifields_'+this.data.id;
                        if(tinyMCE!=null) {
                            tinyMCE.execCommand('mceRemoveControl', true, inputid);
                            tinyMCE.remove('#'+inputid);

                            tinyMCE.init({
                                // General
                                directionality: "ltr",
                                language : "en",
                                mode : "specific_textareas",
                                autosave_restore_when_empty: false,
                                skin : "lightgray",
                                theme : "modern",
                                schema: "html5",
                                selector: ".fieldscontainer .type-textarea textarea.mce_editable",
                                // Cleanup/Output
                                inline_styles : true,
                                gecko_spellcheck : true,
                                entity_encoding : "raw",
                                valid_elements : "",
                                extended_valid_elements : "hr[id|title|alt|class|width|size|noshade]",
                                force_br_newlines : false, force_p_newlines : true, forced_root_block : 'p',
                                toolbar_items_size: "small",
                                invalid_elements : "script,applet,iframe",
                                // Plugins
                                plugins : "table link image code hr charmap autolink lists importcss",
                                // Toolbar
                                toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | formatselect | bullist numlist",
                                toolbar2: "outdent indent | undo redo | link unlink anchor image code | hr table | subscript superscript | charmap",
                                removed_menuitems: "newdocument",
                                // URL
                                relative_urls : true,
                                remove_script_host : false,
                                document_base_url : "<?php echo JURI::root(); ?>",
                                // Layout
                                content_css : "<?php echo JURI::root(); ?>templates/system/css/editor.css",
                                importcss_append: true,
                                // Advanced Options
                                resize: "both",
                                height : "550",
                                width : ""
                            });
                            tinyMCE.execCommand('mceAddControl', true, inputid);
                        }
                    }
                    this.destroyInput = function()
                    {
                        var inputid = 'jifields_'+this.data.id;
                        if(tinyMCE!=null) {
                            tinyMCE.execCommand('mceRemoveControl', true, inputid);
                            tinyMCE.remove('#'+inputid);
                        }
                    }
                }
                jQuery.fn.jifieldtextarea = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifieldtextarea')) return element.data('jifieldtextarea');
                    var jifieldtextarea = new JiFieldTextArea(this, data);
                    element.data('jifieldtextarea', jifieldtextarea);
                    return jifieldtextarea;
                };
            })(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function renderInput() {
        $params = $this->get('params');
        $value = $this->get('value');
        ob_start(); ?>
        <?php if($params->get('editor', 1)==1):
            $editor = JFactory::getEditor();
            echo $editor->display($this->get('inputname').'[value]', $value, '100%', '500', '55', '30', false, $this->get('inputid')) ;
        else: ?>
            <div class="textarea input <?php echo $this->type; ?>">
                <textarea id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]"><?php echo $value; ?></textarea>
            </div>
        <?php endif;
        $html = ob_get_clean();
        return $html;
    }
    public function renderInputParams() {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable optionstable">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop">
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_TEXTAREA').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd textareaeditor-lbl">
                        <label for="fieldtextareaeditor<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_TEXTAREAEDITOR'); ?></label>
                    </li>
                    <li class="jitd textareaeditor">
                        <div class="select input">
                            <?php $choices = array(
                                '1'=>JText::_('JICUSTOMFIELDS_YES'),
                                '0'=>JText::_('JICUSTOMFIELDS_NO')
                            ); ?>
                            <select id="fieldtextareaeditor<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][editor]" data-placeholder="<?php echo $params->get('editor', 1); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('editor', 1))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderOutput() {
        $params = $this->get('params');
        $value = $this->get('value');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        $html.= html_entity_decode($value);
        $html.= $this->get('suffix', '');
        return $html;
    }
}