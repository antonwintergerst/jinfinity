<?php
/**
 * @version     $Id: image.php 087 2014-11-25 14:00:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldImage extends JiCustomField {
    protected static $initialised = false;

    public function renderInputScript()
    {
        /*if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHTML::stylesheet('media/jicustomfields/css/jimediamanager.css');
            JHTML::script('media/jicustomfields/js/jquery.jimediamanager.js');
        } else {
            JHTML::_('stylesheet', 'jimediamanager.css', 'media/jicustomfields/css/');
            JHTML::_('script', 'jquery.jimediamanager.js', 'media/jicustomfields/js/');
        }*/
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldImage = function(container, data)
                {
                    var self = this;
                    // set default data
                    this.data = {
                        'id':'new0',
                        'name':'Image',
                        'type':'image'
                    }
                    // setup Options
                    jQuery.each(data, function(index, value) {
                        self.data[index] = value;
                    });
                    // class Functions
                    this.updateName = function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        if(sender!=null) {
                            var fieldid = sender.id;
                            var newname = jQuery(sender).val();
                            fieldid = 'jifields_'+fieldid.replace('fieldname', '')+'-lbl';
                            var fieldlabel = jQuery(sender).closest('.formfield').find('#'+fieldid);
                            jQuery(fieldlabel).html(newname);
                        }
                    };
                    // Setup Handlers
                    this.changeNameHandler = function(e) {self.updateName(e);};
                    this.prepareInput = function() {
                        jQuery('#jifields_'+self.data.id).jimediamanager({
                            label: 'Select an Image',
                            fileinput: '#jifields_'+self.data.id,
                            url: '<?php echo JURI::base().'index.php?option=com_jicustomfields&task=mediamanager.renderinput&format=ajax'; ?>',
                            rootpath: '<?php echo JURI::root(); ?>',
                            id: data.id,
                            mediatype: 'images'
                        });
                    };
                }
                jQuery.fn.jifieldimage = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifieldimage')) return element.data('jifieldimage');
                    var jifieldimage = new JiFieldImage(this, data);
                    element.data('jifieldimage', jifieldimage);
                    return jifieldimage;
                };
            })(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function renderInput()
    {
        $value = $this->get('value');
        ob_start(); ?>
        <div class="controls">
            <div class="input-prepend input-append">
                <div class="media-preview add-on">
                    <span><i class="icon-eye"></i></span>
                </div>
                <input class="input-small" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>">
                <a href="#" rel="picker" title="Select" class="btn">Select</a>
                <a href="#" rel="clear" class="btn hasTooltip" data-original-title="Clear">
                    <i class="icon-remove"></i>
                </a>
            </div>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function renderInputParams()
    {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable optionstable">
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_IMAGE').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
            </ul>
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span5 imagetypes-lbl">
                    <label for="fieldimagetypes<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_IMAGETYPES'); ?></label>
                </li>
            </ul>
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span7 imagetypes">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fieldimagetypes<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][types]" value="<?php echo $params->get('types', "bmp, gif, jpeg, jpg, folder"); ?>" />
                    </div>
                </li>
            </ul>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function prepareStore()
    {
        parent::prepareStore();
        $this->value = ltrim($this->value, '\\');
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
            if($value!=null && strlen($value)>0) {
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