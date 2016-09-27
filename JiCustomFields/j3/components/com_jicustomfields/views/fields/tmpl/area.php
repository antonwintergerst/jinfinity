<?php
/**
 * @version     $Id: area.php 084 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldArea extends JiCustomField {
    public function renderInput() {
        $value = $this->get('value');
		ob_start(); ?>
		<div class="text input <?php echo $this->type; ?>">
            <input class="inputbox" type="text" id="<?php echo $this->get('inputid'); ?>" name="<?php echo $this->get('inputname'); ?>[value]" value="<?php echo $value; ?>" />
        </div>
        <?php $html = ob_get_clean();
        return $html;
	}
    public function renderInputParams() {
		$params = $this->get('params');
		ob_start(); ?>
        <div class="jitable optionstable">
        	<ul class="jitrow row-fluid nodrop">
        		<li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_AREA').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
        	</ul>
        	<ul class="jitrow row-fluid nodrop">
                <li class="jitd span5 optionareasuffix-lbl">
                    <label for="fieldareasuffix<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_AREASUFFIX'); ?></label>
                </li>
            </ul>
            <ul class="jitrow row-fluid nodrop">
                <li class="jitd span7 optionareasuffix">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fieldareasuffix<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][areasuffix]" value="<?php echo $params->get('areasuffix', '&sup2;'); ?>" />
                    </div>
                </li>
            </ul>
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

        require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
        $item = $this->get('item');
        $catid = isset($item->catid)? $item->catid : null;
        $link = JiCustomFieldsHelperRoute::getSearchRoute($catid);

        $valuehtml = '<span class="jifieldvalue">'.$value.'</span>';
        $valuehtml.= '<span class="jifieldareasuffix">'.$params->get('areasuffix', '').'</span>';
        if($params->get('linkedvalues', 1)==1) {
            $html.= '<a class="jifieldlink" href="'.JRoute::_($link.'&fs['.$this->get('id').']='.htmlspecialchars($value)).'" title="View more articles like this.">'.$valuehtml.'</a>';
        } else {
            $html.= $valuehtml;
        }

		$html.= $this->get('suffix', '');
		return $html;
	}
}