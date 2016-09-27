<?php 
/**
 * @version     $Id: jicontentslider.php 010 2015-12-02 22:34:00Z Anton Wintergerst $
 * @package     JiContentSlider Content Plugin for Joomla 1.7+
 * @copyright   Copyright (C) 2015 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
// Load Helper Class
require_once(JPATH_SITE.'/plugins/content/jicontentslider/helper.php');

class plgContentJiContentSlider extends JPlugin {
    public function __construct(& $subject, $config) {
        parent::__construct( $subject );
    }

    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        if($app->isSite()) {
            $this->contentSlider($context, $article);
        }
    }

    private function contentSlider($context, $item)
    {
        // return if no valid text source found
        if(!$sources = $this->getText($item)) return;

        $params = $this->getParams();
        
        // process plugin
        $regex = "#{".$params->get('curlyvar', 'slider')."(.*?)}(.*?){/".$params->get('curlyvar', 'slider')."}#s";

        $changed = false;
        foreach($sources as $key=>$text) {
            preg_match_all($regex, $text, $matches);
            $count = count($matches[0]);
            if($count) {
                $text = preg_replace_callback($regex, array(&$this,'addHTML'), $text);
                $item->{$key} = $text;
                $changed = true;
            }
        }
        if($changed) {
            // load scripts and stylesheets
            $document = JFactory::getDocument();

            // add modal libraries
            $this->loadModalLibrary($params);

            $document->addStyleSheet(JURI::root().'media/jicontentslider/css/jislider.css');
            $document->addScript(JURI::root().'media/jicontentslider/js/jquery.touchswipe.min.js');
            $document->addScript(JURI::root().'media/jicontentslider/js/jquery.jislider.js');
        }
    }

    public function addHTML($matches)
    {
        $params = $this->getParams();
        // Load the helper class instance
        $helper = new plgJiContentSliderHelper();
        $helper->debug = $params->get('debug', 0);
        // Just display the content slider
        $html = $helper->getHTML($matches[2]);
        
        return $html;
    }

    private function loadModalLibrary($params)
    {
        $document = JFactory::getDocument();
        $modaltype = $params->get('modaltype');
        // jQuery modals
        if($params->get('load_jquery', 1)==1) {
            if(version_compare( JVERSION, '3.0.0', 'ge' )) {
                JHtml::_('jquery.framework');
            } else {
                // Joomla 2.5 Legacy
                $document->addScript(JURI::root().'media/jiframework/js/jquery.min.js');
                $document->addScript(JURI::root().'media/jiframework/js/jquery.noconflict.js');
            }
        }
        switch($modaltype) {
            case 'slimbox2':
                if($params->get('load_slimbox2', 1)==1) {
                    $document->addStyleSheet(JURI::root().'media/jiframework/modals/slimbox2/css/slimbox2.css');
                    $document->addScript(JURI::root().'media/jiframework/modals/slimbox2/js/slimbox2.js');
                }
                break;
            case 'shadowbox':
                if($params->get('load_shadowbox', 1)==1) {
                    $document->addStyleSheet(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.css');
                    $document->addScript(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.js');
                    $document->addScriptDeclaration('Shadowbox.init();');
                }
                break;
            case 'fancybox':
                if($params->get('load_fancybox', 1)==1) {
                    $document->addStyleSheet(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.css?v=2.1.5');
                    $document->addScript(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.js?v=2.1.5');
                    $document->addScriptDeclaration('
                    jQuery(document).ready(function() {
                        jQuery(".fancybox").fancybox({
                            openEffect	: \'none\',
                            closeEffect	: \'none\'
                        });
                    });');
                }
                break;
            default:
                break;
        }
    }

    private function getParams($pluginType='content', $pluginName='jicontentslider')
    {
        if(isset($this->params[$pluginType.$pluginName])) {
            return $this->params[$pluginType.$pluginName];
        } else {
            if(version_compare( JVERSION, '1.6.0', 'ge' )) {
                // Get plugin params
                $plugin = JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JRegistry($plugin->params);
            } else {
                // Get plugin params
                $plugin = &JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JParameter($plugin->params);
            }
            if(!is_array($this->params)) $this->params = array();
            $this->params[$pluginType.$pluginName] = $params;
            return $params;
        }
    }

    private function getText(&$item)
    {
        $sources = array();
        if(isset($item->text) && strlen($item->text)>0) $sources['text'] = $item->text;
        if(isset($item->introtext) && strlen($item->introtext)>0) $sources['introtext'] = $item->introtext;
        if(isset($item->fulltext) && strlen($item->fulltext)>0) $sources['fulltext'] = $item->fulltext;
        return (count($sources)>0)? $sources : false;
    }
}