<?php
/**
 * @version     $Id: jiforms.php 045 2014-11-05 11:47:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemJiForms extends JPlugin
{
    public function onContentBeforeDisplay($context, &$item, &$params, $limitstart = 0)
    {
        // Get the Application
        $app = JFactory::getApplication();
        if($app->isSite()) {
            $jinput = $app->input;
            $option = $jinput->get('option');
            $view = $jinput->get('view');

            if($option=='com_content' && ($view=='category' || $view=='featured' || $view=='search')) {
                $context = 'com_content.category';
            } elseif(($option=='com_content' || $option=='com_jinfinity') && $view=='article') {
                $context = 'com_content.article';
            } else {
                $context = 'other';
            }
            $this->jiForms($context, $item);
        }
    }
    public function getParams() {
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('system', 'jiforms');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $params = $this->params;
        }
        return $params;
    }
    private function jiForms($context, &$item) {
        $lang = JFactory::getLanguage();
        $lang->load('com_jiforms');

        $params = $this->getParams();

        // Process plugin
        $textkeys = array('text','fulltext','introtext');
        foreach($textkeys as $textkey) {
            if(isset($item->{$textkey})) {
                $text = $item->{$textkey};
                $regex = "#{".$params->get('curlyvar', 'jiforms')."(.*?)}(.*?){/".$params->get('curlyvar', 'jiforms')."}#s";
                preg_match_all($regex, $text, $matches);
                $count = count($matches[0]);

                if($count) {
                    foreach($matches as $match) {
                        // Remove surrounding paragraphs
                        $text = preg_replace('#<p(.*)>+\s*('.preg_quote($match[0]).')\s*</p>+#i', $match[0], $text);
                    }
                    // Replace curly brackets with media browser
                    $text = preg_replace_callback($regex, array(&$this,'addHTML'), $text);
                    $item->{$textkey} = $text;
                }
            }
        }
    }
    protected function addHTML($match) {
        if(!isset($match[2])) {
            $html = '';
            return $html;
        }
        // Get URL Vars
        $formalias = $match[2];

        $app = JFactory::getApplication();
        $jinput = $app->input;

        require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'models'.DS.'form.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Form', 'JiFormsModel', array('ignore_request'=>true));
        } else {
            $model = JModel::getInstance('Form', 'JiFormsModel', array('ignore_request'=>true));
        }
        $id = $model->getId($formalias);

        $event = $jinput->get('event', 'beforeload');
        $model->setState('form.id', $id);
        $model->setState('component', false);
        $response = $model->eventHandler($event);
        if($response) {
            $this->form = $model->getFormOnload($id);
            $template = $app->getTemplate();
            $stylelayout = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_jiforms'.DS.'form'.DS.'default.php';
            $corelayout = JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'views'.DS.'form'.DS.'tmpl'.DS.'default.php';

            ob_start();
            if(file_exists($stylelayout)) {
                require_once($stylelayout);
            } else {
                require_once($corelayout);
            }
            $html = ob_get_clean();
        } else {
            $html = '';
        }

        return $html;
    }
}