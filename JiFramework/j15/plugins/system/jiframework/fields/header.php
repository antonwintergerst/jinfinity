<?php
/**
 * @version     $Id: header.php 116 2014-12-12 15:08:00Z Anton Wintergerst $
 * @package     Jinfinity Header Field Type for Joomla! 1.5 only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
defined('_JEXEC') or die;

class JiFieldHeader
{
    function getInput($params)
    {
        $this->params = $params;

        $lang = JFactory::getLanguage();
        $lang->load('plg_system_jiframework', JPATH_ADMINISTRATOR, null, false, true);

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root().'media/jiframework/css/admin.css');

        $title = $this->get('label');
        $alias = strtolower($title);
        $description = JText::_($this->get('description'));
        $xml = $this->get('xml');
        $weblink = $this->get('link', JText::_('JI_WEBSITE').'/'.$alias.'?ref=extparams');

        if($title) $title = JText::_($title);

        $licence = '';
        if($xml) {
            $xml = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . '/' . $xml);
            $version = null;
            if($xml && isset($xml['version'])) {
                $version = $xml['version'];
                if(strpos($version, 'PRO')!==false) {
                    $licence = 'PRO';
                } elseif(strpos($version, 'FREE')!==false) {
                    $licence = 'FREE';
                }
                $version = str_replace(array('FREE', 'PRO'), '', $version);
            }
            if($version) {
                $title.= ' <span class="item-version">v'.$version.'</span>';
            }
            if($licence=='FREE') {
                $title.= ' <span class="label label-error item-licence free">'.$licence.'</span>';
            } elseif($licence=='PRO') {
                $title.= ' <span style="label label-success item-licence pro">'.$licence.'</span>';
            }
        }
        $html = array('<div class="jiadmin jiheader">');

        $html[] = '<span class="item-image"><img src="'.JURI::root().'media/'.$alias.'/images/'.$alias.'-icon.png" alt="" /></span>';
        $html[] = '<div class="item-text">';
        // extension title
        if($title) {
            $html[] = '<h4><a href="'.$weblink.'" title="'.JText::_('JI_WEBSITE_HINT').'" target="_blank">'.$title.'</a></h4>';
        }
        $html[] = '</div>';

        $html[] = '<div>';
        // extension description
        if($description) {
            $html[] = $description;
        }
        // extension syntax
        if(JText::_($alias.'_SYNTAX')!=$alias.'_SYNTAX') {
            $html[] = '<h4>'.JText::_('JI_SYNTAX_TITLE').'</h4>';
            $html[] = JText::_($alias.'_SYNTAX');
        }
        // pro upgrade notice
        if($licence=='FREE') {
            $html[] = '<div class="licence-upgrade">'.sprintf(JText::_('JI_PRO_UPGRADE'), JText::_($alias)).'</div>';
        }
        // pro features
        if(JText::_($alias.'_PRO_FEATURES')!=$alias.'_PRO_FEATURES') {
            $html[] = '<h4>'.JText::_('JI_PRO_FEATURES_TITLE').'</h4>';
            $html[] = JText::_($alias.'_PRO_FEATURES');
        }
        $html[] = '</div>';
        $html[] = '</div>';
        return '</div><div>'.implode('', $html);
    }

    private function get($val, $default = '')
    {
        return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
    }
}
jimport('joomla.html.parameter.element');

class JElementHeader extends JElement {
    var $_name = 'header';

    function fetchTooltip($label, $description, &$node, $control_name, $name){
        $this->_jifield = new JiFieldHeader;
        return;
    }
    function fetchElement($name, $value, &$node, $control_name){
        return $this->_jifield->getInput($node->attributes());
    }
}