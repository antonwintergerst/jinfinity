<?php
/**
 * @version     $Id: currency.php 084 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldCurrency extends JiCustomField {
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
        <div class="jitable row-fluid optionstable">
        	<ul class="jitrow row-fluid nodrop">
        		<li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_CURRENCY').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
        	</ul>
        	<ul class="jitrow span6 nodrop">
                <li class="jitd optioncurrencyprefix-lbl">
                    <label for="fieldcurrencyprefix<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_CURRENCYPREFIX'); ?></label>
                </li>
                <li class="jitd optioncurrencyprefix">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fieldcurrencyprefix<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][currencyprefix]" value="<?php echo $params->get('currencyprefix', '$'); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optioncurrencysuffix-lbl">
                    <label for="fieldcurrencysuffix<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_CURRENCYSUFFIX'); ?></label>
                </li>
                <li class="jitd optioncurrencysuffix">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fieldcurrencysuffix<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][currencysuffix]" value="<?php echo $params->get('currencysuffix', ''); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd optiondecimalplaces-lbl">
                    <label for="fielddecimalplaces<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_DECIMALPLACES'); ?></label>
                </li>
                <li class="jitd optiondecimalplaces">
                    <div class="text input">
                        <input class="inputbox ovalueinput" type="text" id="fielddecimalplaces<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][decimalplaces]" value="<?php echo $params->get('decimalplaces', '2'); ?>" />
                    </div>
                </li>
            </ul>
        </div>
        <?php $html = ob_get_clean();
        return $html;
	}
    public function prepareStore() {
        preg_match('#[-+]?(\d*[.])?\d+#', $this->get('value'), $matches);
        $this->value = isset($matches[0])? $matches[0] : '';
        parent::prepareStore();
    }
	public function renderOutput() {
		$params = $this->get('params');
        $value = $this->get('value');

        // Check for value
        preg_match('#[-+]?(\d*[.])?\d+#', $value, $matches);

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(!isset($matches[0]) && $params->get('hideempty', '0')==1) return $html;

        // Find integer and decimal
        $value = isset($matches[0])? $matches[0] : 0;
        $value = number_format((float)$value, $params->get('decimalplaces', 2), '.', '');

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();

        require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
        $item = $this->get('item');
        $catid = isset($item->catid)? $item->catid : null;
        $link = JiCustomFieldsHelperRoute::getSearchRoute($catid);

        $valuehtml = '';
        if($currencyprefix = $params->get('currencyprefix', '')) $valuehtml.= '<span class="jifieldcurrencyprefix">'.$currencyprefix.'</span>';
        $valuehtml.= '<span class="jifieldvalue">'.$value.'</span>';
        if($currencysuffix = $params->get('currencysuffix', '')) $valuehtml.= '<span class="jifieldcurrencysuffix">'.$currencysuffix.'</span>';

        if($params->get('linkedvalues', 1)==1) {
            $html.= '<a class="jifieldlink" href="'.JRoute::_($link.'&fs['.$this->get('id').']='.htmlspecialchars($value)).'" title="View more articles like this.">'.$valuehtml.'</a>';
        } else {
            $html.= $valuehtml;
        }

        $html.= $this->get('suffix', '');
		return $html;
	}
}