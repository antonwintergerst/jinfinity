<?php
/**
 * @version     $Id: textimage.php 089 2014-11-25 14:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldTextImage extends JiCustomField {
    protected static $initialised = false;

    public function renderInputScript() {
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldTextImage = function(container, data)
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
                        if(jQuery('#'+inputid).length==0) return;
                        if(tinyMCE!=null && !tinyMCE.get('#'+inputid)) {
                            //console.log('creating');
                            //tinyMCE.execCommand('mceRemoveControl', true, inputid);
                            //tinyMCE.remove('#'+inputid);

                            if (typeof WFEditor !== 'undefined') {
                                /*WFEditor.init({
                                    token: "wf58e43bbff2f4a9ce8e5823558c8882ba",
                                    etag: "f340f1b1f65b6df5b5e3f94d95b11daf",
                                    base_url: "<?php echo JURI::root(); ?>",
                                    language: "en",
                                    directionality: "ltr",
                                    theme: "advanced",
                                    plugins: "autolink,cleanup,core,code,colorpicker,upload,format,imgmanager_ext,browser,contextmenu,inlinepopups,wordcount,charmap",
                                    language_load: false,
                                    component_id: 10195,
                                    theme_advanced_buttons1: "help,bold,italic,underline,justifycenter,justifyleft,justifyright,|,imgmanager_ext",
                                    theme_advanced_buttons2: "",
                                    theme_advanced_buttons3: "",
                                    theme_advanced_resizing: true,
                                    toggle_label: "[show/hide]",
                                    entities: "160,nbsp,173,shy",
                                    verify_html: false,
                                    invalid_elements: "iframe,object,param,embed,audio,video,source,script,style,applet,body,bgsound,base,basefont,frame,frameset,head,html,id,ilayer,layer,link,meta,name,title,xml",
                                    remove_script_host: false,
                                    imgmanager_ext_upload: {"max_size":4096,"filetypes":["jpeg","jpg","png","gif"]},
                                    file_browser_callback: function(name, url, type, win){tinyMCE.activeEditor.plugins.browser.browse(name, url, type, win);},
                                    compress: {"javascript":0,"css":0}
                                });*/
                            } else {
                                tinyMCE.init({
                                    // General
                                    directionality: "ltr",
                                    language : "en",
                                    mode : "specific_textareas",
                                    autosave_restore_when_empty: false,
                                    skin : "lightgray",
                                    theme : "modern",
                                    schema: "html5",
                                    selector: ".fieldscontainer .type-textimage textarea.mce_editable",
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

                            }
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
                jQuery.fn.jifieldtextimage = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifieldtextimage')) return element.data('jifieldtextimage');
                    var jifieldtextimage = new JiFieldTextImage(this, data);
                    element.data('jifieldtextimage', jifieldtextimage);
                    return jifieldtextimage;
                };
            })(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function renderInput()
    {
        $params = $this->get('params');
        $value = $this->get('value');
        if(isset($value)) $value = '<img src="'.$value.'" alt="Click here to change the image" />';
        ob_start();

        $editor = JFactory::getEditor();
        echo $editor->display($this->get('inputname').'[value]', $value, '100%', '500', '55', '30', false, $this->get('inputid'));

        $html = ob_get_clean();
        return $html;
    }

    public function prepareStore()
    {
        parent::prepareStore();

        $text = $this->get('value');
        $value = '';

        // find first image
        preg_match('#<\img(.*?)src\="(.*?)"(.*?)\>#s', $text, $match);
        if($match && isset($match[2])) {
            $value = $match[2];
        }
        // trim leading slash
        $this->value = ltrim($value, '\\');
    }

    public function renderOutput()
    {
        $params = $this->get('params');
        $value = $this->get('value');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Make image path absolute
        if(strstr('http', $value)===false && strstr('https', $value)===false) {
            if(!empty($value)) {
                $exists = file_exists(JPATH_SITE.'/'.$value);
                if($exists) $value = JURI::root().$value;
            } else {
                $exists = false;
            }
        } else {
            // assume remote images exist
            $exists = true;
        }

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        if($exists) {
            $html.= '<img src="'.$value.'" alt="'.$this->get('title').'" />';
        } else {
            $html.= '<div class="noimage"><span>&nbsp;</span></div>';
        }
        $html.= $this->get('suffix', '');
        return $html;
    }
}