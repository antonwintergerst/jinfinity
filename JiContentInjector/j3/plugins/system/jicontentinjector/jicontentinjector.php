<?php
/**
 * @version     $Id: jicontentinjector.php 046 2014-12-19 12:28:00Z Anton Wintergerst $
 * @package     JiContentInjector System Plugin for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
// Load Helper Class


class plgSystemJiContentInjector extends JPlugin {
    function __construct(&$subject, $config)
    {
        $this->_pass = 0;
        parent::__construct($subject, $config);
    }
    public function onAfterRoute()
    {
        $this->_pass = 1;
    }
    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();
        if($app->getName()!='site') return;

        // Get the Application
        $app = JFactory::getApplication();
        if($app->isSite()) {
            // Get the document object
            $document = JFactory::getDocument();
            // Get Document Buffer
            $buffer = $document->getBuffer('component');

            require_once(dirname(__FILE__).'/helper.php');
            $helper = new plgJiContentInjectorHelper();
            $source = new stdClass();
            $source->text = $buffer;
            $buffer = $helper->inject($source, 'body');

            if($buffer) $document->setBuffer($buffer, 'component');
        }
    }
    public function onAfterRender()
    {
        $app = JFactory::getApplication();
        if($app->getName()!='site') return;

        $html = JResponse::getBody();

        if ($html == '')
        {
            return;
        }

        require_once(dirname(__FILE__).'/helper.php');
        $helper = new plgJiContentInjectorHelper();
        $source = new stdClass();
        $source->text = $html;
        $html = $helper->inject($source, 'everywhere');
        if($html) JResponse::setBody($html);
    }
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        if($app->getName()!='site') return;

        if($this->_pass) {
            // Get the Application
            $app = JFactory::getApplication();
            if($app->isSite()) {
                $option = JRequest::getCmd('option');
                $view = JRequest::getCmd('view');
                if($option=='com_content' && ($view=='category' || $view=='featured' || $view=='search')) {
                    $context = 'com_content.category';
                } elseif($option=='com_content' && $view=='article') {
                    $context = 'com_content.article';
                }
                $this->jiContentPrepare($context, $article);
            }
        }
    }
    private function jiContentPrepare($contentcontext, &$item)
    {
        // Load Plugin Params
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('system', 'jicontentinjector');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $plugin = &JPluginHelper::getPlugin('system', 'jicontentinjector');
            $params = new JParameter($plugin->params); 
        }
        
        // Process plugin
        if(isset($item->text)) {
            // Other plugins may have already set the article text
            $text = $item->text;
        } else {
            $text = '';
            if(isset($item->introtext)) $text.= $item->introtext;
            if(isset($item->fulltext)) $text.= $item->fulltext;
        }
        require_once(dirname(__FILE__).'/helper.php');
        $helper = new plgJiContentInjectorHelper();
        $source = new stdClass();
        $source->text = $text;
        if(isset($item->id)) {
            if(isset($item->id)) $source->artid = $item->id;
            if(isset($item->catid)) $source->catid = $item->catid;
            $text = $helper->inject($source, 'content');
            if($text) $item->text = $text;
        }
    }
}