<?php 
/**
 * @version     $Id: jisocialwidgets.php 106 2014-10-27 17:18:00Z Anton Wintergerst $
 * @package     JiSocialWidgets Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
// Load Helper Class
require_once(__DIR__.'/helper.php');

abstract class JiSocialWidgetsLoader
{
    public static $loaded = array();
    public static $buffer = '';
    public static function load($api, $params)
    {
        if(in_array($api, self::$loaded)) return;
        self::$loaded[] = $api;

        $document = JFactory::getDocument();
        $buffer = self::$buffer.$document->getBuffer('component');

        if($api=='jisocialwidgets') {
            $document->addStyleSheet( JURI::root(true). '/plugins/content/jisocialwidgets/assets/socialwidgets.css' );
        } elseif($api=='facebook') {
            preg_match_all('#{<div(.*?)id="fb-root"(.*?)>(.*?)</div>#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $fbapi = $params->get('facebook_api', '<!--JiSocialWidgets Facebook--><div id="fb-root">&nbsp;</div>
                <script>(function(d, s, id) {
                  var js, fjs = d.getElementsByTagName(s)[0];
                  if (d.getElementById(id)) return;
                  js = d.createElement(s); js.id = id;
                  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
                  fjs.parentNode.insertBefore(js, fjs);
                }(document, \'script\', \'facebook-jssdk\'));</script>');
                self::$buffer.= $fbapi;
            }
        } elseif($api=='facebook_og') {
            preg_match_all('#JiSocialWidgets FacebookOG#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $document->addCustomTag('<!--JiSocialWidgets FacebookOG-->'.$params->get('facebook_og'));
            }
        } elseif($api=='twitter') {
            preg_match_all('#JiSocialWidgets Twitter#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $twapi = $params->get('twitter_api', '<!--JiSocialWidgets Twitter--><script>
                    !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs"
                    );</script>');
                self::$buffer.= $twapi;
                $updatebuffer = true;
            }
        } elseif($api=='googleplus') {
            preg_match_all('#JiSocialWidgets GooglePlus#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $gpapi = $params->get('googleplus_api', '<!--JiSocialWidgets GooglePlus--><script type="text/javascript">
                      (function() {
                        var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
                        po.src = \'https://apis.google.com/js/plusone.js\';
                        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
                      })();
                    </script>');
                self::$buffer.= $gpapi;
            }
        } elseif($api=='linkedin') {
            preg_match_all('#JiSocialWidgets LinkedIn#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $inapi = $params->get('linkedin_api', '<!--JiSocialWidgets LinkedIn--><script type="text/javascript">
                      (function() {
                        var IN = document.createElement(\'script\'); IN.type = \'text/javascript\'; IN.async = true;
                        IN.src = \'http://platform.linkedin.com/in.js\';
                        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(IN, s);
                      })();
                    </script>');
                self::$buffer.= $inapi;
            }
        } elseif($api=='pinterest') {
            preg_match_all('#JiSocialWidgets Pinterest#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $pinapi = $params->get('pinterest_api', '<!--JiSocialWidgets Pinterest--><script type="text/javascript">
                    (function (w, d, load) {
                     var script,
                     first = d.getElementsByTagName(\'SCRIPT\')[0],
                     n = load.length,
                     i = 0,
                     go = function () {
                       for (i = 0; i < n; i = i + 1) {
                         script = d.createElement(\'SCRIPT\');
                         script.type = \'text/javascript\';
                         script.async = true;
                         script.src = load[i];
                         first.parentNode.insertBefore(script, first);
                       }
                     }
                     if (w.attachEvent) {
                       w.attachEvent(\'onload\', go);
                     } else {
                       w.addEventListener(\'load\', go, false);
                     }
                    }(window, document,
                     [\'//assets.pinterest.com/js/pinit.js\']
                    ));
                    </script>');
                self::$buffer.= $pinapi;
            }
        } elseif($api=='disqus') {
            preg_match_all('#JiSocialWidgets Disqus#s', $buffer, $matches);
            if(count($matches[0])==0) {
                $dqapi = $params->get('disqus_api', '<!--JiSocialWidgets Disqus--><script type="text/javascript">
                        if(typeof disqus_shortname!="undefined") {
                            (function() {
                                var dsq = document.createElement(\'script\');dsq.type = \'text/javascript\'; dsq.async = true;
                                dsq.src = \'http://\' + disqus_shortname + \'.disqus.com/embed.js\';
                                (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(dsq);
                            })();
                        }
                    </script>');
                self::$buffer.= $dqapi;
            }
        }
    }

    public static function renderBuffer()
    {
        if(strlen(self::$buffer)>0) {
            $document = JFactory::getDocument();
            $document->setBuffer('<!-- Begin JiSocialWidgets -->'.self::$buffer.'<!-- End JiSocialWidgets -->'.$document->getBuffer('component'),'component');

            self::$buffer = '';
        }
    }
}
class plgContentJiSocialWidgets extends JPlugin {
    public function __construct(& $subject, $config) {
        parent::__construct( $subject );
    }
    public function getParams()
    {
        if(isset($this->params)) return $this->params;
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('content', 'jisocialwidgets');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $plugin = &JPluginHelper::getPlugin('content', 'jisocialwidgets');
            $params = new JParameter($plugin->params);
        }
        $this->params = $params;
        return $params;
    }
    // Joomla 1.5 Compatibility
    public function onBeforeDisplayContent(& $article, & $params, $limitstart) {
        // Get the Application
        $app = JFactory::getApplication();
        if($app->isSite()) {
            
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $context = null;
            if($option=='com_content' && ($view=='category' || $view=='featured' || $view=='search')) {
                $context = 'com_content.category';
            } elseif($option=='com_content' && $view=='article') {
                $context = 'com_content.article';
            }
            $this->socialWidgets($context, $article);
        }
    }
    // Joomla 1.7+ Compatibility
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0) {
        $this->context = $context;
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
            $this->socialWidgets($context, $article);
        }
    }
    private function socialWidgets($context, $article) {
        $params = $this->getParams();
        $assignment = $params->get('assignment','all');
        
        // Process plugin
        if(isset($article->text)) {
            // Other plugins may have already set the article text
            $text = $article->text;
        } else {
            $text = '';
            if(isset($article->introtext)) $text.= $article->introtext;
            if(isset($article->fulltext)) $text.= $article->fulltext;
        }
        
        $replaceMethod = $params->get('replacemethod', 'safe');
        $regex = "#{socialwidgets(.*?)}(.*?){/socialwidgets}#s";
        if($replaceMethod=='safe') {
            $text = $this->safeReplacer($regex, $text, array(&$this,'addHTML'), '{/socialwidgets}');
        } else {
            preg_match_all($regex, $text, $matches);
            $count = count($matches[0]);
            if($count) {
                // Replace curly brackets
                $text = preg_replace_callback($regex, array(&$this,'addHTML'), $text);
            } elseif($assignment=='all') {
                $text.= plgContentJiSocialWidgets::addHTML(null);
            }
        }
        
        $article->text = $text;
    }
    protected function safeReplacer($regex, $text, $replacer, $filter=null) {
        $params = $this->getParams();
        // Convert to Dom
        if(!$params->get('debug', 0)) libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML('<div>'.$text.'</div>');
        // Perform Xpath Query
        $xpath = new DOMXPath($dom);
        $query = ($filter!=null)? '//*[text()[contains(.,"'.$filter.'")]]':'';
        $nodes = $xpath->query($query);
        if($nodes!=null && $nodes->length>0) {
            $nodestomove = array();
            $nodestoupdate = array();
            foreach($nodes as $node) {
                //$nodetext = $node->wholeText;
                $tempdoc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $tempdoc->appendChild($tempdoc->importNode($cloned,TRUE));
                $nodetext = $tempdoc->saveHTML();
                // Find curly code matches
                preg_match_all($regex, $nodetext, $matches);
                if(count($matches[0])>0) {
                    $replacement = '';
                    $newnodes = '';
                    foreach($matches[0] as $m=>$dummy) {
                        // To preserve placement we need to split the matches into single matches
                        $singlematch = array();
                        foreach($matches as $k=>$dummypart) {
                            $singlematch[] = $matches[$k][$m];
                        }
                        $replacement = call_user_func($replacer, $singlematch);
                        $newnodes.= $replacement;
                        $regex2 = '#'.str_replace('|', '\|', $matches[0][$m]).'#s';
                        $nodetext = preg_replace($regex2, $replacement, $nodetext);
                    }
                    // Repair invalid html
                    $tag = $node->tagName;
                    $prefix = '';
                    $suffix = '';
                    if(in_array($tag, array('a','b','em','big','i','p','strong','span','tt'))) {
                        $newNode = $dom->createDocumentFragment();
                        $newNode->appendXML($newnodes);
                        
                        $nodeObject = new stdClass();
                        $nodeObject->node = $newNode;
                        $nodeObject->oldnode = $node;
                        $nodestomove[] = $nodeObject;
                        
                        $nodetext = '';
                    }
                    // Update nodetext
                    $nodeObject = new stdClass();
                    $nodeObject->node = $node;
                    $nodeObject->nodetext = $nodetext;
                    $nodestoupdate[] = $nodeObject;
                }
            }
            foreach($nodestomove as $nodeObject) {
                $nodeObject->oldnode->parentNode->insertBefore($nodeObject->node, $nodeObject->oldnode->nextSibling);
            }
            foreach($nodestoupdate as $nodeObject) {
                $newNode = $dom->createDocumentFragment();
                $newNode->appendXML($nodeObject->nodetext);
                $nodeObject->node->parentNode->replaceChild($newNode, $nodeObject->node);
            }
            $text = mb_substr($dom->saveXML($xpath->query('//body')->item(0)), 6, -7, "UTF-8");
        }
        $text = JiSocialWidgetsLoader::$buffer.$text;
        JiSocialWidgetsLoader::$buffer = '';
        return $text;
    }
    protected function addHTML($matches) {
        $params = $this->getParams();

        // add jisocialwidgets css
        JiSocialWidgetsLoader::load('jisocialwidgets', $params);
        
        $options = array();
        if($matches[1]!=null) {
            $inputs = explode('|', $matches[1]);
            if($inputs!=null) {
                foreach($inputs as $input) {
                    $parts = explode('=', $input);
                    $var = trim($parts[0]);
                    $val = str_replace($var.'=', '', $input);
                    if($var!='') {
                        // Facebook Widgets
                        if(in_array($var, array('like', 'subscribe', 'comments', 'page'))) {
                            // Add Facebook API
                            if($params->get('facebook_loadapi', true)) {
                                JiSocialWidgetsLoader::load('facebook', $params);
                            }
                            // Add Facebook Open Graph
                            if($params->get('facebook_loadog', true) && $params->get('facebook_og')!=null) {
                                JiSocialWidgetsLoader::load('facebook_og', $params);
                            }
                        }
                        if(in_array($var, array('tweet', 'timeline'))) {
                            // Add Twitter API
                            if($params->get('twitter_loadapi', true)) {
                                JiSocialWidgetsLoader::load('twitter', $params);
                            }
                        }
                        if(in_array($var, array('plus1', 'plus'))) {
                            if($params->get('googeplus_loadapi', true)) {
                                JiSocialWidgetsLoader::load('googleplus', $params);
                            }
                        }
                        if(in_array($var, array('inshare', 'inmember'))) {
                            // Add LinkedIn API
                            if($params->get('linkedin_loadapi', true)) {
                                JiSocialWidgetsLoader::load('linkedin', $params);
                            }
                        }
                        if(in_array($var, array('pinit', 'pinterest'))) {
                            // Add Pinterest API
                            if($params->get('pinterest_loadapi', true)) {
                                JiSocialWidgetsLoader::load('pinterest', $params);
                            }
                        }
                        if(in_array($var, array('disqus'))) {
                            // Add Disqus API
                            if($params->get('disqus_loadapi', true)) {
                                JiSocialWidgetsLoader::load('disqus', $params);
                            }
                        }
                        if($var=='like') {
                            $options['like']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'like_')) {
                            $options['like'][$var] = $val;
                        } elseif($var=='subscribe') {
                            $options['subscribe']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'subscribe_')) {
                            $options['subscribe'][$var] = $val;
                        } elseif($var=='comments') {
                            $options['comments']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'comments_')) {
                            $options['comments'][$var] = $val;
                        } elseif($var=='page') {
                            $options['page']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'page_')) {
                            $options['page'][$var] = $val;
                        }
                        // Twitter Widgets

                        elseif($var=='tweet') {
                            $options['tweet']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'tweet_')) {
                            $options['tweet'][$var] = $val;
                        } elseif($var=='timeline') {
                            $options['timeline']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'timeline_')) {
                            $options['timeline'][$var] = $val;
                        }
                        // Google Plus

                        elseif($var=='plus1') {
                            $options['plus1']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'plus1_')) {
                            $options['plus1'][$var] = $val;
                        } elseif($var=='plus') {
                            $options['plus']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'plus_')) {
                            $options['plus'][$var] = $val;
                        }
                        // Linked In

                        elseif($var=='inshare') {
                            $options['inshare']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'inshare_')) {
                            $options['inshare'][$var] = $val;
                        } elseif($var=='inmember') {
                            $options['inmember']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'inmember_')) {
                            $options['inmember'][$var] = $val;
                        }
                        // Pinterest Widgets

                        elseif($var=='pinit') {
                            $options['pinit']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'pinit_')) {
                            $options['pinit'][$var] = $val;
                        } elseif($var=='pinterest') {
                            $options['pinterest']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'pinterest_')) {
                            $options['pinterest'][$var] = $val;
                        }
                        // Disqus Widgets

                        elseif($var=='disqus') {
                            $options['disqus']['enabled'] = ($val!='hide')? 'show':$val;
                        } elseif(strstr($var, 'disqus_')) {
                            $options['disqus'][$var] = $val;
                        // Other
                        } else {
                            $options[$var] = $val;
                        }
                    }
                }
            }
        } else {
            if($params->get('like','show')=='show' || $params->get('page','hide')=='show') {
                // Add Facebook API
                if($params->get('facebook_loadapi', true)) {
                    JiSocialWidgetsLoader::load('facebook', $params);
                }
                // Add Facebook Open Graph
                if($params->get('facebook_loadog', true) && $params->get('facebook_og')!=null) {
                    JiSocialWidgetsLoader::load('facebook_og', $params);
                }
            }
            $options['like'] = $params->get('like','show');
            $options['page'] = $params->get('page','hide');

            if($params->get('tweet','show')=='show') {
                // Add Twitter API
                if($params->get('twitter_loadapi', true)) {
                    JiSocialWidgetsLoader::load('twitter', $params);
                }
            }
            $options['tweet'] = $params->get('tweet','show');
        }
        // Display social share widgets
        return $html = plgJiSocialWidgetsHelper::getHTML($params, $options);
    }
}