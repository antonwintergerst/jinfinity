<?php
/**
 * @version     $Id: header.php 110 2013-07-03 09:27:00Z Anton Wintergerst $
 * @package     Jinfinity Header Field Type for Joomla! 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
defined('_JEXEC') or die;

if(version_compare(JVERSION, '1.6.0', 'ge')) {
    class JFormFieldHeader extends JFormField {
        public $type = 'Header';

        protected function getLabel() {
            return '';
        }

        protected function getInput() {
            $this->params = $this->element->attributes();

            $title = $this->get('label');
            $description = $this->get('description');
            $xml = $this->get('xml');

            if($title) $title = JText::_($title);

            if($xml) {
                $xml = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . '/' . $xml);
                $version = 0;
                if($xml && isset($xml['version'])) {
                    $version = $xml['version'];
                }
                if($version) {
                    if($title) {
                        $title .= ' v';
                    } else {
                        $title = JText::_('Version').' ';
                    }
                    $title .= $version;
                }
            }
            $html = array();

            if($title) {
                $html[] = '<h4>'.$title.'</h4>';
            }
            if($description) {
                $html[] = $description;
            }
            return '</div><div>'.implode('', $html);
        }
        private function get($var, $default = '')
        {
            return (isset($this->params[$var]) && (string) $this->params[$var] != '') ? (string) $this->params[$var] : $default;
        }
    }
} else {
    class JiFieldHeader
    {
        function getInput($params)
        {

            $this->params = $params;

            $title = $this->get('label');
            $description = $this->get('description');
            $xml = $this->get('xml');
            $lang_file = $this->get('language_file');

            if ($lang_file) {
                jimport('joomla.filesystem.file');

                // Include extra language file
                $language = JFactory::getLanguage();
                $lang = str_replace('_', '-', $language->getTag());

                $inc = '';
                $lang_path = 'language/' . $lang . '/' . $lang . '.' . $lang_file . '.inc.php';
                if (JFile::exists(JPATH_ADMINISTRATOR . '/' . $lang_path)) {
                    $inc = JPATH_ADMINISTRATOR . '/' . $lang_path;
                } else if (JFile::exists(JPATH_SITE . '/' . $lang_path)) {
                    $inc = JPATH_SITE . '/' . $lang_path;
                }
                if (!$inc && $lang != 'en-GB') {
                    $lang = 'en-GB';
                    $lang_path = 'language/' . $lang . '/' . $lang . '.' . $lang_file . '.inc.php';
                    if (JFile::exists(JPATH_ADMINISTRATOR . '/' . $lang_path)) {
                        $inc = JPATH_ADMINISTRATOR . '/' . $lang_path;
                    } else if (JFile::exists(JPATH_SITE . '/' . $lang_path)) {
                        $inc = JPATH_SITE . '/' . $lang_path;
                    }
                }
                if ($inc) {
                    include $inc;
                }
            }

            if ($title) {
                $title = JText::_($title);
            }

            if ($description) {
                if ($description['0'] != '<') {
                    $description = '<p>' . $description . '</p>';
                }
            }

            if ($xml) {
                $xml = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . '/' . trim($xml, '/'));
                $version = 0;
                if ($xml && isset($xml['version'])) {
                    $version = $xml['version'];
                }
                if($version) {
                    if ($title) {
                        $title .= ' v';
                    } else {
                        $title = JText::_('Version') . ' ';
                    }
                    $title .= $version;
                }
            }

            $html = array();

            $html[] = '<div>';

            if ($title) {
                $html[] = '<h4 style="margin: 0px;">'.$title.'</h4>';
            }
            if ($description) {
                $html[] = $description;
            }

            $html[] = '<div style="clear: both;"></div>';
            $html[] = '</div>';

            return implode('', $html);
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
}