<?php
/**
 * @version     $Id: tags.php 089 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldTags extends JiCustomField {
    public function renderInputScript() {
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldTags = function(container, data)
                {
                    var self = this;
                    // class functions
                    this.prepareInput = function() {
                        //if(jQuery().jiautocomplete) jQuery('#'+this.data.id).jiautocomplete({input:<?php echo $this->inputid; ?>, url:'<?php echo JURI::base(); ?>index.php?option=com_jicustomfields&task=autocomplete.field&format=json&field=<?php echo $this->id; ?>'});
                    }
                }
                jQuery.fn.jifieldtags = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifieldtags')) return element.data('jifieldtags');
                    var jifieldtags = new JiFieldTags(this, data);
                    element.data('jifieldtags', jifieldtags);
                    return jifieldtags;
                };
            })(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderInput() {
        ob_start(); ?>
        <div class="text input <?php echo $this->type; ?>">
            <input class="inputbox" type="text" id="<?php echo $this->inputid; ?>" name="<?php echo $this->inputname; ?>[value]" value="<?php echo implode(', ', $this->getTags(true)); ?>" />
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function prepareStore() {
    	parent::prepareStore();
        $this->index = true;
		$values = $this->getTags(true);
		$newvalues = array();
		foreach($values as $value) {
			$newvalues[] = urldecode(trim($value));
		}
		$this->value = $newvalues;
        $this->indexdata = $newvalues;
    }
    public function renderOutput() {
        $params = $this->get('params');
        $tags = $this->getTags(true);

        // start building HTML string
        $html = '';

        // skip/hide empty
        if(count($tags)==0 && $params->get('hideempty', 0)==1) return $html;

        // continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();

		if(count($tags)>0) {
            require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
            $item = $this->get('item');
            $catid = isset($item->catid)? $item->catid : null;
            $link = JRoute::_(JiCustomFieldsHelperRoute::getSearchRoute($catid));
            if(strpos($link, '?')===false) $link.= '?';

			$total = count($tags);
			foreach($tags as $key=>$tag) {
                $valuehtml = '';
				$tag = trim($tag);
                $valuehtml.= '<span class="jifieldvalue">'.$tag;
				if($key<$total-1) $valuehtml.= ',';
                $valuehtml.= '</span>';

                if($params->get('linkedvalues', 1)==1 && $params->get('showlink', 1)==1) {
                    $html.= '<a class="jifieldlink" href="'.$link.'&fs['.$this->id.']='.urlencode($tag).'" title="View more articles like '.urldecode($tag).'">'.$valuehtml.'</a>';
                } else {
                    $html.= $valuehtml;
                }
			}
		}
		$html.= $this->get('suffix', '');
		return $html;
	}
    public function prepareInput() {
        $this->value = $this->getTags(true);
        parent::prepareInput();
    }
    /*public function prepareOutput() {
        $this->value = $this->getTags(true);
        parent::prepareOutput();
    }*/
	public function getTags($decode=false) {
        if($this->value==null) {
            $params = JRequest::getVar('params');
            if(isset($params[$this->name])) $this->value = $params[$this->name];
        }
		if(!is_array($this->value)) {
            //if($decode) $this->value = urldecode($this->value);
            if($value = json_decode($this->value)) {
                $this->value = $value;
            } else {
			    $this->value = explode(',', $this->value);
            }
		}
		if($this->value==null) $this->value = array();
		return $this->value;
    }
}