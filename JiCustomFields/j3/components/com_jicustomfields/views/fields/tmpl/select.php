<?php
/**
 * @version     $Id: select.php 093 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldSelect extends JiCustomField {
    /**
     * Returns javascript for field input
     * @return string
     */
    public function renderInputScript() {
        ob_start(); ?>
        <script type="text/javascript">
        	(function(jQuery){
			    var JiFieldSelect = function(container, data)
			    {
			        var self = this;
			        // Set Default Data
					this.data = {
					    'id':'new0',
					    'name':'Select List',
					    'type':'select'
					}
					// Setup Options
					jQuery.each(data, function(index, value) {
					    self.data[index] = value;
					});
			        // Class Functions
			        this.addOption = function(e) {
			            var sender = e.target != null ? e.target : e.srcElement;
			            // Prevent Default Actions
			            e.preventDefault();
			            e.stopPropagation();
			            var btn = jQuery(sender).closest('.jiaddoptionbtn');
			            if(btn!=null) {
			            	// Find next option ID
			            	var optioncount = 0;
			            	var optionstable = jQuery(btn).closest('.optionstable');
			            	var options = jQuery(optionstable).find('.jioption .ovalueinput');
			            	jQuery.each(options, function(index, option) {
			            		var oid = jQuery(option).attr('id').replace('param', '').replace('value', '');
			            		oid = oid.replace('new', '');
			            		if(parseInt(oid)>=optioncount) optioncount = parseInt(oid)+1;
			            	});
			            	var optioncreator = jQuery(btn).closest('.optioncreator');
			            	var valueinput = jQuery(optioncreator).find('.ovalueinput');
			            	var value = jQuery(valueinput).val();
			            	var labelinput = jQuery(optioncreator).find('.olabelinput');
			            	var label = jQuery(labelinput).val();
			            	jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.renderinputoption&format=ajax',
				                type:'post',
				                data:{
				                    'id':self.data.id,
				                    'name':self.data.name,
				                    'type':self.data.type,
				                    'optioncount':optioncount,
                                    'value':value,
                                    'label':label
				                }
			                }).done(function(response) {
				                if(response!=null) {
				                	// Insert new input option
				                	jQuery(response).insertBefore(optioncreator);
				                	// Insert new select option
				                	jQuery('#jifields_'+self.data.id).append('<option value="'+value+'">'+label+'</option>');
				                	jQuery('#jifields_'+self.data.id).trigger('liszt:updated');
				                	// Clear option creator values
				                	jQuery(valueinput).val('');
				                	jQuery(labelinput).val('');
				                	// Increment option creator ids
				                	optioncount++;
				                	jQuery(valueinput).attr({
				                		'id':'param'+optioncount+'value',
				                		'name':'jifields['+self.data.id+'][params][options]['+optioncount+'][value]'
				                	});
				                	jQuery(labelinput).attr({
				                		'id':'param'+optioncount+'label',
				                		'name':'jifields['+self.data.id+'][params][options]['+optioncount+'][label]'
				                	});
				                	jQuery('.optionstable').jisortable({btn:'.jisortoptionbtn', tab:'.jioption'});

                                    // set focus
                                    jQuery(valueinput).focus();
				                }
				            });
			            }
			        };
                    this.removeOption = function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        // prevent default actions
                        e.preventDefault();
                        e.stopPropagation();

                        var btn = jQuery(sender).closest('.jiremoveoptionbtn');
                        if(btn!=null) {
                            jQuery(btn).closest('.jioption').remove();
                        }
                    };
			        // setup Handlers
			        this.addOptionHandler = function(e) {self.addOption(e);};
                    this.removeOptionHandler = function(e) {self.removeOption(e)};

			        // Parent Functions
			        this.prepareInput = function() {
			        	// Setup Option Actions
			        	jQuery(container).find('.jiremoveoptionbtn').on('click', this.removeOptionHandler);
			            jQuery(container).find('.jiaddoptionbtn').on('click', this.addOptionHandler);
			            jQuery('.optionstable').jisortable({btn:'.jisortoptionbtn', tab:'.jioption'});
			        }
			    }
			    jQuery.fn.jifieldselect = function(data) {
			        var element = jQuery(this);
			        if(element.data('jifieldselect')) return element.data('jifieldselect');
			        var jifieldselect = new JiFieldSelect(this, data);
			        element.data('jifieldselect', jifieldselect);
			        return jifieldselect;
			    };
			})(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }

	/**
	 * Returns HTML for field value input
     * @return string
	 */
    public function renderInput() {
    	ob_start(); ?>

    	<div class="select input">
            <select id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" data-placeholder="<?php echo $this->get('title'); ?>" class="chzn-select">
                <?php echo $this->renderOptions(); ?>
            </select>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }

    /**
     * Returns HTML for select options
     * @return string
     */
    public function renderOptions() {
		$html = '';
		$params = $this->get('params');

        // get value for item
        $itemvalue = $this->get('value');
        foreach((array)$params->get('options') as $value=>$label) {
            // @legacy complex array compatibility
            if(is_object($label) || is_array($label)) {
                $option = (array) $label;
                $value = isset($option['value'])? $option['value'] : '';
                $label = isset($option['label'])? $option['label'] : '';
            }

            // add html option
            $selected = ($value==$itemvalue)? ' selected="selected"':'';
            $html.= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
        }
        return $html;
	}

	/**
	 * Returns HTML for field params input
     * @return string
	 */
    public function renderInputParams() {
        $params = $this->get('params');
		$this->optioncount = 0;
        ob_start(); ?>
        <div class="jitable optionstable">
        	<ul class="jitrow row-fluid nodrop">
        		<li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_SELECT').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
        	</ul>
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span5 optionvalue-lbl">
                    <label><?php echo JText::_('JICUSTOMFIELDS_OPTIONVALUE'); ?></label>
                </li>
                <li class="jitd span7 optionlabel-lbl">
                    <label><?php echo JText::_('JICUSTOMFIELDS_OPTION'); ?></label>
                </li>
            </ul>
	        <?php
            foreach((array) $params->get('options') as $value=>$label) {
                // @legacy complex array compatibility
                if(is_object($label) || is_array($label)) {
                    $option = (array) $label;
                    echo $this->renderInputOption($option['value'], $option['label']);
                    continue;
                }

                echo $this->renderInputOption($value, $label);
            }
			?>
			<ul class="jitrow row-fluid optioncreator">
                <li class="jitd span5 optionvalue">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="param<?php echo $this->id.$this->optioncount.'value'; ?>" name="jifields[<?php echo $this->id; ?>][params][options][<?php echo $this->optioncount; ?>][value]" value="" />
                    </div>
                </li>
                <li class="jitd span5 optionlabel">
                    <div class="text input">
                        <input class="inputbox olabelinput" type="text" id="param<?php echo $this->id.$this->optioncount.'label'; ?>" name="jifields[<?php echo $this->id; ?>][params][options][<?php echo $this->optioncount; ?>][label]" value="" />
                    </div>
                </li>
                <li class="jitd span2 optionactions">
                	<div class="jitable">
                		<ul class="jitrow row-fluid nodrop">
                            <li class="jitd span12">
                            	<a class="jibtn icon26 jiaddoptionbtn" href="#" rel="jifield<?php echo $this->id; ?>" title="<?php echo JText::_('JICUSTOMFIELDS_ADDOPTION'); ?>">
                            		<span class="jiicon add"><?php echo JText::_('JICUSTOMFIELDS_ADDOPTION'); ?></span>
                        		</a>
                           	</li>
                        </ul>
                	</div>
                </li>
            </ul>
		</div>
        <?php $html = ob_get_clean();
        return $html;
    }

    /**
     * Returns HTML for field option input
     * @param string $value
     * @param string $label
     * @param int $optioncount
     * @return string
     */
    public function renderInputOption($value=null, $label=null, $optioncount=null) {
        // option must have either a value or a label
        if(!isset($value) && !isset($label)) return;
        // prime data
        if(!isset($value) || !is_string($value)) $value = '';
        if(!isset($label) || !is_string($label)) $label = '';

        // check if optioncount was passed by method caller
        if(!isset($optioncount)) $optioncount = $this->optioncount;
		ob_start(); ?>
		<ul class="jitrow row-fluid jioption jid<?php echo $this->get('id').$optioncount; ?>">
            <li class="jitd span5 optionvalue">
                <div class="text input">
                    <input class="inputbox ovalueinput" type="text" id="param<?php echo $this->id.$optioncount.'value'; ?>" name="jifields[<?php echo $this->id; ?>][params][options][<?php echo $optioncount; ?>][value]" value="<?php echo $value; ?>" />
                </div>
            </li>
            <li class="jitd span5 optionlabel">
                <div class="text input">
                    <input class="inputbox olabelinput" type="text" id="param<?php echo $this->id.$optioncount.'label'; ?>" name="jifields[<?php echo $this->id; ?>][params][options][<?php echo $optioncount; ?>][label]" value="<?php echo $label; ?>" />
                </div>
            </li>
            <li class="jitd span2 optionactions">
            	<div class="jitable">
            		<ul class="jitrow row-fluid nodrop">
                        <li class="jitd span6">
                        	<a class="jibtn icon26 jiremoveoptionbtn" href="#" rel="jifield<?php echo $this->id; ?>" title="<?php echo JText::_('JICUSTOMFIELDS_REMOVEOPTION'); ?>">
                        		<span class="jiicon subtract"><?php echo JText::_('JICUSTOMFIELDS_REMOVEOPTION'); ?></span>
                    		</a>
                       	</li>
                       	<li class="jitd span6">
                       		<a class="jibtn icon26 jisortoptionbtn jid<?php echo $this->get('id').$optioncount; ?>" href="#" title="<?php echo JText::_('JICUSTOMFIELDS_SORT'); ?>">
                       			<span class="jiicon sort"><?php echo JText::_('JICUSTOMFIELDS_SORT'); ?></span>
                   			</a>
                       	</li>
                    </ul>
            	</div>
            </li>
        </ul>
		<?php
        if(isset($this->optioncount)) $this->optioncount++;
		$html = ob_get_clean();
        return $html;
	}

    /**
     * Modify JiField before storing to database
     */
    public function prepareStore() {
        $this->index = true;
        $this->indexdata = $this->get('value');
        $params = $this->get('params');

        // set options
        $options = array();
        foreach($params->get('options', array()) as $value=>$label) {
            // transform complex array from input fields
            if(is_object($label) || is_array($label)) {
                $option = (array) $label;
                $value = isset($option['value'])? $option['value'] : '';
                $label = isset($option['label'])? $option['label'] : '';
            }

            // both value and label cant be null, that would be the option creator
            if(strlen(trim($value))==0 && strlen(trim($label))==0) continue;

            // add option to array
            $options[$value] = $label;
        }
        $params->set('options', $options);

		$this->setParams($params);
    }

    /**
     * Returns HTML of field output
     * @return string
     */
	public function renderOutput() {
		$params = $this->get('params');

        // get item value
        $itemvalue = $this->get('value');

        // get field options
		$options = $this->getOptions();

        // start building HTML string
        $html = '';

        // skip/hide empty, treat null values as empty
        if(($itemvalue==null || !isset($options[$itemvalue])) && $params->get('hideempty', '0')==1) return $html;

        // continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();

        if(isset($options[$itemvalue])) {
            require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
            $item = $this->get('item');
            $catid = isset($item->catid)? $item->catid : null;
            $link = JiCustomFieldsHelperRoute::getSearchRoute($catid);
            $label = $options[$itemvalue];

            $valuehtml = '<span class="jifieldvalue">'.$label.'</span>';
            if($params->get('linkedvalues', 1)==1) {
			    $html.= '<a class="jifieldlink" href="'.JRoute::_($link.'&fs['.$this->id.']='.htmlspecialchars($itemvalue)).'" title="View more articles like '.$label.'">'.$valuehtml.'</a>';
            } else {
                $html.= $valuehtml;
            }
		} else {
            $html.= '<span class="jifieldvalue">&nbsp;</span>';
        }
        $html.= $this->get('suffix', '');
		return $html;
	}
}