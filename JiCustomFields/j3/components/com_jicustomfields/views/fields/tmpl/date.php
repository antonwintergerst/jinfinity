<?php
/**
 * @version     $Id: date.php 083 2014-11-19 10:32:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldDate extends JiCustomField {
    public function renderInputScript()
    {
        JHtml::_('jquery.ui');
        JHtml::_('behavior.calendar');
        //JHTML::script('media/jicustomfields/js/jquery.uidatepicker.js');
        //JHTML::script('media/jicustomfields/js/jquery.uitimepicker.js');
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldDate = function(container, data)
                {
                    var self = this;
                    // Setup Handlers
                    this.prepareInput = function() {
                        Calendar.setup({
                            // Id of the input field
                            inputField: <?php echo $this->get('inputid'); ?>,
                            // Format of the input field
                            ifFormat: "%Y-%m-%d %H:%M:%S",
                            // Trigger for the calendar (button ID)
                            button: '<?php echo $this->get('inputid'); ?>btn',
                            // Alignment (defaults to "Bl")
                            align: "Tl",
                            singleClick: true,
                            firstDay: 0
                        });

                    }
                }
                jQuery.fn.jifielddate = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifielddate')) return element.data('jifielddate');
                    var jifielddate = new JiFieldDate(this, data);
                    element.data('jifielddate', jifielddate);
                    return jifielddate;
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
		<div class="text input <?php echo $this->type; ?>">
            <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>" />
            <button id="<?php echo $this->get('inputid'); ?>btn" class="btn">
				<i class="icon-calendar"></i>
			</button>
        </div>
        <?php $html = ob_get_clean();
        return $html;
	}

    public function renderInputParams()
    {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable row-fluid optionstable">
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_DATE').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optionshowdate-lbl">
                    <label for="fieldshowdate<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_SHOWDATE'); ?></label>
                </li>
                <li class="jitd optionshowdate">
                    <div class="select input">
                        <?php $choices = array(
                            1=>JText::_('JYES'),
                            0=>JText::_('JNO')
                        ); ?>
                        <select id="fieldshowdate<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showdate]" data-placeholder="<?php echo $params->get('showdate'); ?>" class="chzn-select">
                            <?php foreach($choices as $value=>$label): ?>
                                <?php $selected = ($value==$params->get('showdate', 1))? ' selected="selected"':''; ?>
                                <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optiondateformat-lbl">
                    <label for="fielddateformat<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_DATEFORMAT'); ?></label>
                </li>
                <li class="jitd optiondateformat">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fielddateformat<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][dateformat]" value="<?php echo $params->get('dateformat', 'd/m/Y'); ?>" />
                    </div>
                </li>
            </ul>
            <div class="clearfix"></div>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optionshowtime-lbl">
                    <label for="fieldshowtime<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_SHOWTIME'); ?></label>
                </li>
                <li class="jitd optionshowtime">
                    <div class="select input">
                        <?php $choices = array(
                            1=>JText::_('JYES'),
                            0=>JText::_('JNO')
                        ); ?>
                        <select id="fieldshowtime<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showtime]" data-placeholder="<?php echo $params->get('showtime'); ?>" class="chzn-select">
                            <?php foreach($choices as $value=>$label): ?>
                                <?php $selected = ($value==$params->get('showtime', 0))? ' selected="selected"':''; ?>
                                <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optiontimeformat-lbl">
                    <label for="fieldtimeformat<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_TIMEFORMAT'); ?></label>
                </li>
                <li class="jitd optiontimeformat">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fieldtimeformat<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][timeformat]" value="<?php echo $params->get('timeformat', 'g:ia'); ?>" />
                    </div>
                </li>
            </ul>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }

    public function renderOutput()
    {
        $params = $this->get('params');
        $value = $this->get('value');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        $html.= '<span class="jifieldvalue">';
        if($params->get('showdate', 1)) $html.= $this->getDateSpans($value, $params->get('dateformat', 'd/m/Y'));
        if($params->get('showtime', 0)) $html.= $this->getDateSpans($value, $params->get('timeformat', 'g:ia'));
        $html.='</span>';
        $html.= $this->get('suffix', '');

        return $html;
    }

    public function getDateSpans($source, $format='d/m/Y')
    {
        $html = '';
        for($fm=0; $fm<strlen($format); $fm++) {
            $subformat = substr($format, $fm, 1);
            $value = date($subformat, strtotime($source));
            $class = preg_replace('/[^a-zA-Z0-9\']/', 'separator', $subformat);
            $html.= '<span class="fm'.$class.'">'.$value.'</span>';
        }

        return $html;
    }
}