<?php 
/**
 * @version     $Id: helper.php 010 2015-12-02 23:04:00Z Anton Wintergerst $
 * @package     JIContentSlider Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2015 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.environment.uri');
class plgJiContentSliderHelper {
	public $rootpath;
    public $rootdir;
    function __construct() {
        $this->hasSetPaths = false;
    }
    public function setPaths() {
        if(!$this->hasSetPaths) {
            $this->rootpath = rtrim(str_replace('/modules/mod_jicontentslider/admin', '', JURI::root()), '/');
            $this->rootdir = JPATH_SITE;
            $this->thumbspath = $this->rootpath.'/images/jithumbs';
            $this->thumbsdir = JPATH_SITE.DS.'images'.DS.'jithumbs';
            $this->hasSetPaths = true;
        }
    }
    public function getData($params) {
        $this->setPaths();

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        // Create jithumbs directory if needed
        if (!JFolder::exists(rtrim($this->thumbsdir, '/'))) JFolder::create(rtrim($this->thumbsdir, '/'));
        // Ensure index.html exists in each folder
        $this->indexfile = JPATH_SITE.'/images/index.html';
        if(JFile::exists($this->indexfile)) {
            if(!JFile::exists($this->thumbsdir.'/index.html')) JFile::copy($this->indexfile, $this->thumbsdir.'/index.html');
        }
        $items = array();
        switch($params->get('sourcetype', 'category')) {
            case 'article':
                $source = (int) $params->get('source');

                // Load Article
                require_once(JPATH_SITE.'/components/com_content/helpers/route.php');
                $path = JPATH_SITE.'/components/com_jicustomfields/models/article.php';
                if(file_exists($path)) {
                    require_once($path);
                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                        $model = JModelLegacy::getInstance('Article', 'JiCustomFieldsModel');
                    } else {
                        $model = JModel::getInstance('Article', 'JiCustomFieldsModel');
                    }
                } else {
                    require_once(JPATH_SITE.'/components/com_content/models/article.php');
                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                        $model = JModelLegacy::getInstance('Article', 'ContentModel');
                    } else {
                        $model = JModel::getInstance('Article', 'ContentModel');
                    }
                }

                $article = $model->getItem($source);
                if($article!=null) {
                    if($params->get('trigger_events', 1)==1) {
                        // Load Plugin Triggers
                        JPluginHelper::importPlugin('content');
                        $dispatcher =& JEventDispatcher::getInstance();
                        $aparams = JComponentHelper::getParams('com_content');
                    }
                    $fulltext = $article->introtext;
                    if($params->get('trigger_events', 1)==1) {
                        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_jinfinity.article', &$article, &$aparams, 0));
                    }




                    $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
                    $oldtext = $article->introtext;
                    $text = $article->introtext;

                    // Find images
                    preg_match_all($regex, $fulltext, $matches, PREG_SET_ORDER);
                    $imgtag = '';
                    if($matches!=null) {
                        foreach($matches as $match) {
                            $item = new stdClass();
                            $item->images = array();
                            $item->article = $article;
                            $item->title = $article->title;
                            $item->alias = $article->alias;

                            $item->images = array();

                            $image = new stdClass();
                            //$match = $matches[0];
                            $source = $match[2];
                            $path = $this->createThumbnail($source, $params);
                            $path = str_replace($this->rootpath, '', $path);
                            $path = $this->rootpath.'/'.ltrim($path, '/');
                            $image->path = $path;
                            if($params->get('removeimages', 1)==1) {
                                $text = preg_replace('#<\img(.*?)src\="(.*?)"(.*?)\>#s', '', $text);
                            } else {
                                $text = preg_replace('#'.$match[0].'#s', '', $text);
                            }

                            // Find all IMG attributes
                            if($params->get('link_preserveattribs', 1)==1) {
                                $imageattribs = array();
                                preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                                if($iattribmatches!=null) {
                                    foreach($iattribmatches as $iattribmatch) {
                                        if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = $iattribmatch[2];
                                    }
                                }
                                $image->imageattribs = $imageattribs;
                            }
                            if($params->get('image_preserveattribs', 1)!=1 || !isset($image->imageattribs)) $image->imageattribs = array();
                            $image->imageattribs['src'] = $image->path;
                            if(isset($image->imageattribs['width'])) unset($image->imageattribs['width']);
                            if(isset($image->imageattribs['height'])) unset($image->imageattribs['height']);
                            if(!isset($image->imageattribs['alt'])) $image->imageattribs['alt'] = $item->title;
                            $image->imageattribs['class'] = (isset($image->imageattribs['class']))? 'slideimg '.$image->imageattribs['class'] : 'slideimg';

                            $item->images[] = $image;

                            // Setup Links
                            $item->link = '#';
                            $linksource = $params->get('linksource', 'imagelink');
                            if($params->get('link_preserveattribs', 1)==1 || $linksource=='imagelink') {
                                // Find all Link attributes
                                $linkattribs = array();
                                preg_match_all('#<a\s(.*)>(.*)'.$imgtag.'(.*)<\/a>#siU', $oldtext, $linkmatches, PREG_SET_ORDER);
                                if(isset($linkmatches[0][0])) {
                                    preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $linkmatches[0][1], $lattribmatches, PREG_SET_ORDER);
                                    if($lattribmatches!=null) {
                                        foreach($lattribmatches as $lattribmatch) {
                                            if(isset($lattribmatch[1])) $linkattribs[$lattribmatch[1]] = $lattribmatch[2];
                                        }
                                    }
                                    // Strip link from HTML
                                    $text = preg_replace('#'.$linkmatches[0][0].'#', '', $text);

                                }

                                $item->linkattribs = $linkattribs;
                            }
                            if($params->get('links', 1)==1) {
                                // Set Link
                                if($linksource=='article') {
                                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
                                } elseif($linksource=='imagelink') {
                                    if(isset($item->linkattribs['href'])) $item->link = $item->linkattribs['href'];
                                    if(isset($item->linkattribs['target'])) $item->linktarget = $item->linkattribs['target'];
                                }
                            }

                            // Remove Curly Code
                            if(isset($article->Caption)) {
                                $caption = $article->Caption->renderOutput();
                                if(!empty($caption)) $text = $caption;
                            }

                            if($params->get('caption_nocurly', 1)==1) {
                                $text = preg_replace('#{(.*?)(.*?){/(.*?)}#s', '', $text);
                            }
                            // Strip Tags
                            $captiontext = trim(strip_tags($text, $params->get('caption_excludedtags')));
                            if($captiontext!='') $item->captiontext = $captiontext;
                            if($params->get('caption_striptags', 1)==1) {
                                $text = $captiontext;
                            } else {
                                // Remove empty paragraph tags
                                $text = preg_replace('#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#', '', $text);
                            }
                            // Truncate
                            if($params->get('caption_truncate', 1)==1) {
                                $text = $this->truncate($text, $params->get('caption_length', 220));
                            }

                            if(trim($text)!='') $item->caption = $text;

                            // Prepare link attributes
                            if($params->get('link_preserveattribs', 1)!=1 || !isset($item->linkattribs)) $item->linkattribs = array();
                            if(!isset($item->linkattribs['href'])) $item->linkattribs['href'] = $item->link;
                            if(!isset($item->linkattribs['target']) && isset($item->linktarget)) $item->linkattribs['target'] = $item->linktarget;
                            if(!isset($item->linkattribs['title'])) $item->linkattribs['title'] = (isset($item->captiontext))? $item->captiontext:$item->title;
                            $item->linkattribs['class'] = (isset($item->linkattribs['class']))? $item->linkattribs['class'] : '';

                            if(!($params->get('skipempty', 1) && count($item->images)==0)) $items[] = $item;
                        }
                    }


                }
            break;
            case 'category':
                $source = (int) $params->get('source');

                // Set Filter Vars
                $order = $params->get('orderby_pri');

                $jinput = JFactory::getApplication()->input;
                $jinput->set('limit', $params->get('maxslides', 5));
                if($order!='random') $jinput->set('orderby_pri', $params->get('orderby_pri'));
                $jinput->set('orderby_date', $params->get('order_date'));
                // Load Articles
                require_once(JPATH_SITE.'/components/com_content/helpers/route.php');
                $path = JPATH_SITE.'/components/com_jicustomfields/models/articles.php';
                if(file_exists($path)) {
                    require_once($path);
                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                        $model = JModelLegacy::getInstance('Articles', 'JiCustomFieldsModel');
                    } else {
                        $model = JModel::getInstance('Articles', 'JiCustomFieldsModel');
                    }
                } else {
                    require_once(JPATH_SITE.'/components/com_content/models/articles.php');
                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                        $model = JModelLegacy::getInstance('Articles', 'ContentModel');
                    } else {
                        $model = JModel::getInstance('Articles', 'ContentModel');
                    }
                }
                $model->setState('filter.category_id', $source);
                $model->setState('list.limit', $params->get('maxslides', 5));

                $articles = $model->getItems();
                if($articles!=null) {
                    if($order=='random') shuffle($articles);
                    if($params->get('trigger_events', 1)==1) {
                        // Load Plugin Triggers
                        JPluginHelper::importPlugin('content');
                        $dispatcher = JEventDispatcher::getInstance();
                        $aparams = JComponentHelper::getParams('com_content');
                    }
                    foreach($articles as $article) {
                        $fulltext = $article->introtext;
                        if($params->get('trigger_events', 1)==1) {
                            $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_jinfinity.article', &$article, &$aparams, 0));
                        }

                        $item = new stdClass();
                        $item->images = array();
                        $item->article = $article;
                        $item->title = $article->title;
                        $item->alias = $article->alias;

                        $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
                        $oldtext = $article->introtext;
                        $text = $article->introtext;

                        // Find images
                        preg_match_all($regex, $fulltext, $matches, PREG_SET_ORDER);
                        $imgtag = '';
                        if($matches!=null) {
                            foreach($matches as $match) {
                                $image = new stdClass();
                                //$match = $matches[0];
                                $source = $match[2];
                                $path = $this->createThumbnail($source, $params);
                                $path = str_replace($this->rootpath, '', $path);
                                $path = $this->rootpath.'/'.ltrim($path, '/');
                                $image->path = $path;
                                if($params->get('removeimages', 1)==1) {
                                    $text = preg_replace('#<\img(.*?)src\="(.*?)"(.*?)\>#s', '', $text);
                                } else {
                                    $text = preg_replace('#'.$match[0].'#s', '', $text);
                                }

                                // Find all IMG attributes
                                if($params->get('link_preserveattribs', 1)==1) {
                                    $imageattribs = array();
                                    preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                                    if($iattribmatches!=null) {
                                        foreach($iattribmatches as $iattribmatch) {
                                            if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = $iattribmatch[2];
                                        }
                                    }
                                    $image->imageattribs = $imageattribs;
                                }
                                if($params->get('image_preserveattribs', 1)!=1 || !isset($image->imageattribs)) $image->imageattribs = array();
                                $image->imageattribs['src'] = $image->path;
                                if(isset($image->imageattribs['width'])) unset($image->imageattribs['width']);
                                if(isset($image->imageattribs['height'])) unset($image->imageattribs['height']);
                                if(!isset($image->imageattribs['alt'])) $image->imageattribs['alt'] = $item->title;
                                $image->imageattribs['class'] = (isset($image->imageattribs['class']))? 'slideimg '.$image->imageattribs['class'] : 'slideimg';

                                $item->images[] = $image;
                            }
                        }


                        // Setup Links
                        $item->link = '#';
                        $linksource = $params->get('linksource', 'imagelink');
                        if($params->get('link_preserveattribs', 1)==1 || $linksource=='imagelink') {
                            // Find all Link attributes
                            $linkattribs = array();
                            preg_match_all('#<a\s(.*)>(.*)'.$imgtag.'(.*)<\/a>#siU', $oldtext, $linkmatches, PREG_SET_ORDER);
                            if(isset($linkmatches[0][0])) {
                                preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $linkmatches[0][1], $lattribmatches, PREG_SET_ORDER);
                                if($lattribmatches!=null) {
                                    foreach($lattribmatches as $lattribmatch) {
                                        if(isset($lattribmatch[1])) $linkattribs[$lattribmatch[1]] = $lattribmatch[2];
                                    }
                                }
                                // Strip link from HTML
                                $text = preg_replace('#'.$linkmatches[0][0].'#', '', $text);

                            }

                            $item->linkattribs = $linkattribs;
                        }
                        if($params->get('links', 1)==1) {
                            // Set Link
                            if($linksource=='article') {
                                $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
                            } elseif($linksource=='imagelink') {
                                if(isset($item->linkattribs['href'])) $item->link = $item->linkattribs['href'];
                                if(isset($item->linkattribs['target'])) $item->linktarget = $item->linkattribs['target'];
                            }
                        }

                        // Remove Curly Code
                        if(isset($article->Caption)) {
                            $caption = $article->Caption->renderOutput();
                            if(!empty($caption)) $text = $caption;
                        }

                        if($params->get('caption_nocurly', 1)==1) {
                            $text = preg_replace('#{(.*?)(.*?){/(.*?)}#s', '', $text);
                        }
                        // Strip Tags
                        $captiontext = trim(strip_tags($text, $params->get('caption_excludedtags')));
                        if($captiontext!='') $item->captiontext = $captiontext;
                        if($params->get('caption_striptags', 1)==1) {
                            $text = $captiontext;
                        } else {
                            // Remove empty paragraph tags
                            $text = preg_replace('#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#', '', $text);
                        }
                        // Truncate
                        if($params->get('caption_truncate', 1)==1) {
                            $text = $this->truncate($text, $params->get('caption_length', 220));
                        }

                        if(trim($text)!='') $item->caption = $text;

                        // Prepare link attributes
                        if($params->get('link_preserveattribs', 1)!=1 || !isset($item->linkattribs)) $item->linkattribs = array();
                        if(!isset($item->linkattribs['href'])) $item->linkattribs['href'] = $item->link;
                        if(!isset($item->linkattribs['target']) && isset($item->linktarget)) $item->linkattribs['target'] = $item->linktarget;
                        if(!isset($item->linkattribs['title'])) $item->linkattribs['title'] = (isset($item->captiontext))? $item->captiontext:$item->title;
                        $item->linkattribs['class'] = (isset($item->linkattribs['class']))? $item->linkattribs['class'] : '';

                        // Prepare image attributes
                        /*foreach($item->images as &$image) {
                            if($params->get('image_preserveattribs', 1)!=1 || !isset($image->imageattribs)) $image->imageattribs = array();
                            $image->imageattribs['src'] = $image->path;
                            if(isset($image->imageattribs['width'])) unset($image->imageattribs['width']);
                            if(isset($image->imageattribs['height'])) unset($image->imageattribs['height']);
                            if(!isset($image->imageattribs['alt'])) $image->imageattribs['alt'] = $image->title;
                            $image->imageattribs['class'] = (isset($image->imageattribs['class']))? 'slideimg '.$image->imageattribs['class'] : 'slideimg';
                        }*/

                        if(!($params->get('skipempty', 1) && count($item->images)==0)) $items[] = $item;
                    }
                }
            break;
            case 'directory':
                // Get Images from directory
                $source = $this->rootdir.DS.trim($params->get('source'), DS);
                $path = str_replace(DS, '/', $this->rootpath.'/'.trim($params->get('source'), DS));
                
                if(is_dir($source)) {
                    $dir = $source;
                    if(is_dir($dir)) {
                        if($dh = opendir($dir)) {
                            while(($file=readdir($dh))!==false) {
                                $fileparts = explode('.', $file);
                                $filetype = end($fileparts);
                                if(in_array($filetype, array('gif', 'jpg', 'jpeg', 'png'))) {
                                    $item = new stdClass();
                                    $item->title = $file;

                                    $item->images = array();

                                    $path = $this->createThumbnail($source.'/'.$file, $params);
                                    $path = str_replace($this->rootpath, '', $path);
                                    $path = $this->rootpath.'/'.ltrim($path, '/');

                                    $image = new stdClass();
                                    $image->path = $path;
                                    if($image->path) {
                                        // Prepare link attributes
                                        if($params->get('link_preserveattribs', 1)!=1 || !isset($item->linkattribs)) $item->linkattribs = array();
                                        if(!isset($item->linkattribs['href']) && isset($item->link)) $item->linkattribs['href'] = $item->link;
                                        if(!isset($item->linkattribs['target']) && isset($item->linktarget)) $item->linkattribs['target'] = $item->linktarget;
                                        if(!isset($item->linkattribs['title'])) $item->linkattribs['title'] = (isset($item->captiontext))? $item->captiontext:$item->title;
                                        $item->linkattribs['class'] = (isset($item->linkattribs['class']))? 'imagelink '.$item->linkattribs['class'] : 'imagelink';

                                        // Prepare image attributes
                                        if($params->get('image_preserveattribs', 1)!=1 || !isset($image->imageattribs)) $image->imageattribs = array();
                                        $image->imageattribs['src'] = $image->path;
                                        if(isset($image->imageattribs['width'])) unset($image->imageattribs['width']);
                                        if(isset($image->imageattribs['height'])) unset($image->imageattribs['height']);
                                        if(!isset($image->imageattribs['alt'])) $image->imageattribs['alt'] = $item->title;
                                        $image->imageattribs['class'] = (isset($image->imageattribs['class']))? 'slideimg '.$image->imageattribs['class'] : 'slideimg';
                                        $item->images[] = $image;

                                        $items[] = $item;
                                        if(count($items)>=$params->get('maxslides', 5)) break;
                                    }
                                }
                            }
                        }
                        closedir($dh);
                    }
                }
            break;
            case 'xml':
                // Get Images from XML
                $source = $this->rootdir.DS.trim($params->get('source'), DS);
                if(is_file($source)) {
                    $fileparts = explode('.', $source);
                    $filetype = end($fileparts);
                    if($filetype=='xml') {
                        $xml = simplexml_load_file($source);
                        // Get Params
                        foreach($xml->image as $image) {
                            // Get Attributes
                            $item = new stdClass();
                            foreach($image->attributes() as $attrkey=>$attrvalue) {
                                if($attrkey=='path') {
                                    $filepath = (string) $attrvalue;
                                    $item->path = $this->createThumbnail($this->rootpath.'/'.$filepath, $params);
                                } else {
                                    $item->{$attrkey} = (string) $attrvalue;
                                }
                            }
                            $items[] = $item;
                            if(count($items)>=$params->get('maxslides', 5)) break;
                        }
                    }
                }
            break;
        }
        if($params->get('uniqueclass')==null) $params->set('uniqueclass', $this->randomString());
        
        // Set Javascript Params
        $jsparams = array(
            'links'=>$params->get('links', true),
            'captions'=>$params->get('captions', true),
            'discs'=>$params->get('discs', true),
            'width'=>$params->get('width', '100%'),
            'height'=>$params->get('height', '200px'),
            'padding'=>$params->get('padding', '0 0 50px 0'),
            'autosizing'=>$params->get('autosizing', 'aspectfill'),
            'verticalAlign'=>$params->get('verticalAlign', 'middle'),
            'horizontalAlign'=>$params->get('horizontalAlign', 'middle'),
            'numberslides'=>$params->get('numberslides', 1),
            'transition'=>$params->get('transition', 'slideleft'),
            'speed'=>$params->get('speed', 250),
            'delay'=>$params->get('delay', 5000),
            'autoplay'=>$params->get('autoplay', 1),
            'responsive'=>$params->get('responsive', 1)
        );
        
        // Return Data
        $data = new stdClass();
        $data->jsparams = $jsparams;
        $data->items = $items;
        $data->total = count($items);
        
        return $data;
    }
    public function createThumbnail($filepath, $params) {
        $this->setPaths();

        $pcontext = 'sli';
        
        $pathparts = explode('/', $filepath);
        $file = end($pathparts);
        $nameparts = explode('.', $file);
        $name = $nameparts[0];
        $type = end($nameparts);
        $imageset = false;
        if($params->get($pcontext.'_thumbs_resize', 1)==1) {
            if(JFile::exists($this->thumbsdir.'/'.$name.'_'.$pcontext.'.jpg') && $params->get('thumbs_cache', 1)==1) {
                $imageset = true;
            } else {
                jiimport('jiimageprocessor');
                require_once(JPATH_SITE.DS.'modules'.DS.'mod_jicontentslider'.DS.'helpers'.DS.'imageprocessor.php');
                $JiImageProcessor = new JiContentSliderImageProcessor();
                $imageset = $JiImageProcessor->resizeImage($filepath, $this->thumbsdir.'/'.$name.'_'.$pcontext.'.jpg', $params, $type, $pcontext);
            }
        }
        if($imageset) {
            return $this->thumbspath.'/'.$name.'_'.$pcontext.'.jpg';
        } else {
            $filepath = str_replace(JPATH_SITE.DS, '', $filepath);
            return $filepath;
        }
    }
    public function truncate($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0)
    {
        $text = $text.' ';
        $i = 0;
        $simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'image'=>true,'link'=>true,'meta'=>true);
        $tags = array();
        if($isHTML){
            preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach($m as $o){
                if($o[0][1] - $i >= $length)
                    break;
                $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
                // test if the tag is unpaired, then we mustn't save them
                if($t[0] != '/' && (!isset($simpleTags[$t])))
                    $tags[] = $t;
                elseif(end($tags) == substr($t, 1))
                    array_pop($tags);
                $i += $o[1][1] - $o[0][1];
            }
        }
        
        // output without closing tags
        $output = substr($text, 0, $length = min(strlen($text),  $length + $i));
        // closing tags
        $output2 = (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');
        
        // Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
        $osplit = preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $oend1 = end($osplit);
        $pos = (int)end($oend1);
        // Append closing tags to output
        $output.=$output2;

        // Get everything until last space
        $one = substr($output, 0, $pos);
        // Get the rest
        $two = substr($output, $pos, (strlen($output) - $pos));
        // Extract all tags from the last bit
        preg_match_all('/<(.*?)>/s', $two, $tags);
        // Add suffix inside last tag if needed
        if((strlen($text) > $length || $force==1) && $insert==0) { $one .= $suffix; }
        // Re-attach tags
        $output = $one . implode($tags[0]);
        // Add suffix outside last tag if needed
        if((strlen($text) > $length || $force==1)&& $insert==1) { $output .= $suffix; }

        //added to remove  unnecessary closure
        $output = str_replace('</!-->','',$output); 

        return $output;
    }
    public function randomString($length=5) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        $return = '';
        for($i = 0; $i<$length; $i++) {
            $return.= $chars[rand(0, $size - 1)];
        }
        return $return;
    }
    public function getHTML($currentfolder) {
        $lang = JFactory::getLanguage();
        $lang->load('plg_content_jicontentslider', JPATH_ADMINISTRATOR);

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $params = $this->getParams();
        $this->debug = $params->get('debug', 0);

        // Get Data
        $this->data = $this->getData();

        // Check for template overrides
        $app = JFactory::getApplication();
        $path = JPATH_THEMES.'/'.$app->getTemplate().'/html/plg_jicontentslider/default.php';
        if(!file_exists($path)) $path = dirname(__FILE__).'/tmpl/default.php';
        // Render template
        ob_start();
        require($path);
        $html = ob_get_clean();

        // Return html
        return $html;
    }
}