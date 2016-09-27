<?php 
/**
 * @version     $Id: preview.php 101 2014-03-14 13:57:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare( JVERSION, '1.6.0', 'ge' )) {
    // Joomla 1.7+
    class JFormFieldPreview extends JFormField
    {
        protected $type = 'preview';
    
        protected function getInput()
        {
            $document = JFactory::getDocument();
            // Load Stylesheets
            $document->addStyleSheet(JURI::root().'media/jicontentslider/css/jislider.css');
            // Load Scripts
            JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');
            JHtml::_('jquery.framework');
            $document->addScript(JURI::root().'media/jicontentslider/js/jquery.touchswipe.min.js');
            $document->addScript(JURI::root().'media/jicontentslider/js/jquery.jislider.js');
            ob_start(); ?>
            <script type="text/javascript">
                if(typeof jQuery!='undefined') {
                    jQuery(document).ready(function() {
                        function changeType(e) {
                            var sourcetype = jQuery('#jform_params_sourcetype').val();

                            if(sourcetype=='article') {
                                jQuery('#jform_params_sourcearticle').parent().show();
                            } else {
                                jQuery('#jform_params_sourcearticle').parent().hide();
                            }
                            if(sourcetype=='category') {
                                jQuery('#jform_params_sourcecategory').parent().show();
                            } else {
                                jQuery('#jform_params_sourcecategory').parent().hide();
                            }
                            if(sourcetype=='directory') {
                                jQuery('#jform_params_sourcedirectory').parent().show();
                            } else {
                                jQuery('#jform_params_sourcedirectory').parent().hide();
                            }
                            if(sourcetype=='xml') {
                                jQuery('#jform_params_sourcexml').parent().show();
                            } else {
                                jQuery('#jform_params_sourcexml').parent().hide();
                            }
                        }
                        function updatePreview(e) {
                            if(e!=null) {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                            var source = '';
                            switch(jQuery('#jform_params_sourcetype').val()) {
                                case 'article':
                                    source = jQuery('#jform_params_sourcearticle').val();
                                    break;
                                case 'category':
                                    source = jQuery('#jform_params_sourcecategory').val();
                                break;
                                case 'directory':
                                    source = jQuery('#jform_params_sourcedirectory').val();
                                break;
                                case 'xml':
                                    source = jQuery('#jform_params_sourcexml').val();
                                break
                            }

                            jQuery.ajax({dataType:'json', url:'<?php echo JURI::root().'modules/mod_jicontentslider/admin/json.php'; ?>',
                                type:'post',
                                data:{
                                    'links':jQuery('#jform_params_links').val(),
                                    'captions':jQuery('#jform_params_captions').val(),
                                    'discs':jQuery('#jform_params_discs').val(),
                                    'uniqueclass': 'preview',
                                    'sourcetype':jQuery('#jform_params_sourcetype').val(),
                                    'source':source,
                                    'width':jQuery('#jform_params_width').val(),
                                    'height':jQuery('#jform_params_height').val(),
                                    'padding':jQuery('#jform_params_padding').val(),
                                    'autosizing':jQuery('#jform_params_autosizing').val(),
                                    'verticalAlign':jQuery('#jform_params_verticalAlign').val(),
                                    'horizontalAlign':jQuery('#jform_params_horizontalAlign').val(),
                                    'numberslides':jQuery('#jform_params_numberslides').val(),
                                    'speed':jQuery('#jform_params_speed').val(),
                                    'delay':jQuery('#jform_params_delay').val(),
                                    'autoplay':jQuery('#jform_params_autoplay').val(),
                                    'responsive':jQuery('#jform_params_responsive').val(),
                                    'sli_thumbs_resize':jQuery('#jform_params_sli_thumbs_resize').val()
                                },
                                cache:false,
                                async:false
                            }).done(function(response) {
                                if(response!=null) {
                                    rebuildPreview(response);
                                }
                            });
                        }
                        function rebuildPreview(data) {
                            jQuery('.slidesmask.sliderpreview').remove();
                            jQuery('#discspreview').children().remove();
                            if(data.items!=null) {
                                var s = 1;
                                var k = 1;
                                var p = 1;
                                for(var i=0; i<data.items.length; i++) {
                                    // Create Images
                                    var item = data.items[i];
                                    jQuery(item.images).each(function(index, image){
                                        var slide = jQuery(document.createElement('div')).attr({'class':'slide slide'+(i+1)});
                                        var img = jQuery(document.createElement('img')).attr({'class':'slideimg', 'src':image.path, 'alt':image.title});
                                        if((data.jsparams.links==true || data.jsparams.links=='true') && image.link!=null) {
                                            var link = jQuery(document.createElement('a')).attr({'href':image.link, 'title':image.title});
                                            jQuery(link).append(img);
                                            jQuery(slide).append(link);
                                        } else {
                                            jQuery(slide).append(img);
                                        }

                                        jQuery('#containerpreview').prepend(slide);

                                        // Create Discs
                                        var link = jQuery(document.createElement('a')).attr({'href':'#', 'class':'jislidergotobtn sliderpreview icon', 'rel':p, 'title':'GoTo Slide '+p});
                                        var span = jQuery(document.createElement('span')).html(p);
                                        jQuery(link).append(span);
                                        jQuery('#discspreview').append(link);
                                        p++;
                                    });
                                }
                            }
                            jQuery.removeData('.preview', 'jislider');
                            jQuery(data.jsparams).jislider();
                        }
                        var updatePreviewHandler = function(e) {updatePreview(e);};
                        var changeTypeHandler = function(e) {changeType(e);};
                        
                        var sourceinput = jQuery('#<?php echo $this->id; ?>');
                        jQuery('#jform_params_sourcecategory').on('change', updatePreviewHandler);
                        jQuery('#jform_params_sourcedirectory').on('change', updatePreviewHandler);
                        jQuery('#jform_params_sourcexml').on('change', updatePreviewHandler);
                        jQuery('#jform_params_sourcetype').on('change', changeTypeHandler);
                        // Set Source Type
                        changeType();
                        // Build on tab becoming active
                        /*jQuery('.nav a[href="#options"]').on('click', function(e) {
                            updatePreview();
                        });*/
                        // Rebuild on container becoming visible
                        var scanner = null;
                        if(!jQuery('.jislideradmin').is(":visible")) {
                            scanner = setInterval(function() {
                                if(jQuery('.jislideradmin').is(":visible")) {
                                    updatePreview();
                                    clearInterval(scanner);
                                }
                            }, 250);
                        }
                        // Set Update Buttons
                        jQuery('.previewbtn').on('click', updatePreviewHandler);
                    });
                }
            </script>
            <div class="jislideradmin">
                <div class="previewfield">
                    <div id="containerpreview" class="jislider containerpreview sliderpreview">
                        <div id="discspreview" class="discs"></div>
                        <div class="prevnav nav">
                            <div class="paddelbox paddel">
                                <a href="#" class="jisliderprevbtn paddelbtn sliderpreview">Prev</a>
                            </div>
                        </div>
                        <div class="nextnav nav">
                            <div class="paddelbox paddel">
                                <a href="#" class="jislidernextbtn paddelbtn sliderpreview">Next</a>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="previewbtn" title="Update the live preview of this module">Preview</a>
                    <p>(Experimental. Results may vary.)</p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        protected function getLabel()
        {
            return null;
        }
    }
} else {
    // Joomla 1.5 Legacy
    jimport('joomla.html.parameter.element');
    class JElementPreview extends JElement {
    
        var $_name = 'preview';
    
        function fetchElement($name, $value, &$node, $control_name){
        }
    
        function fetchTooltip($label, $description, &$node, $control_name, $name){
            return NULL;
        }
    }
}