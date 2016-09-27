<?php 
/**
 * @version     $Id: jiblogtools.php 209 2014-12-23 09:42:00Z Anton Wintergerst $
 * @package     Jinfinity Blog Tools Content Plugin for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class plgContentJiBlogTools extends JPlugin 
{
    private $excludedrts = array();
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        if($app->isSite()) {
            $this->blogTools($context, $article);
        }
    }

    private function blogTools($context, &$item)
    {

        // prevent recursion
        if(isset($item->hasJiBlogTools)) return;

        // return if no valid text found
        if(!$sources = $this->getText($item)) return;

        $params = $this->getParams('content', 'jiblogtools');
        $pagecontext = $this->getPageContext();

        $app = JFactory::getApplication();
        $jinput = $app->input;


        if(isset($item->id) && isset($item->catid)) {
            // article type content
            $itemtype = 'article';
            $catid = $item->catid;
        } else {
            // either category or other such as module
            if($context=='com_content.category' && $pagecontext=='com_content.category') {
                $item->id = $jinput->get('id');
                $catid = $item->id;
                $itemtype = 'category';
            } else {
                $itemtype = 'other';
                return;
            }
        }
        
        if($pagecontext=='com_content.article' || $pagecontext=='com_content.category') {
            // process assignment
            $assign_method = $params->get('assign_method', 'all');

            $process_c = 1;
            if(isset($catid)) {
                // Category Assignment
                $assign_categorymethod = $params->get('assign_categorymethod', 'all');
                $assign_categories = $params->get('assign_categories', array());
                // Category Children
                if(($assign_categorymethod=="include" || $assign_categorymethod=="exclude") && count($assign_categories)>0) {
                    $levels = (int) $params->get('filter_categorylevels', 3);
                    if($params->get('assign_categorychildren', 1) && $levels>0) {
                        if(!$levels) $levels = 9999;
                        $app = JFactory::getApplication();
                        $appParams = $app->getParams();
                        // Get an instance of the generic categories model
                        $categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
                        $categories->setState('params', $appParams);
                        $categories->setState('filter.get_children', $levels);
                        $categories->setState('filter.published', 1);
                        $additional_catids = array();

                        foreach ($assign_categories as $acatid) {
                            $categories->setState('filter.parentId', $acatid);
                            $recursive = true;
                            $categoryObjects = $categories->getItems($recursive);

                            if($categoryObjects) {
                                foreach ($categoryObjects as $category) {
                                    $condition = (($category->level - $categories->getParent()->level) <= $levels);
                                    if ($condition) {
                                        $additional_catids[] = $category->id;
                                    }
                                }
                            }
                        }
                        $assign_categories = array_unique(array_merge($assign_categories, $additional_catids));
                    }
                }
                if($assign_categorymethod == "none") {
                    // Dont process any articles
                    $process_c = 0;
                } elseif($assign_categorymethod == "include" && !in_array($catid, $assign_categories)) {
                    // Dont process articles that are NOT in the include list
                    $process_c = 0;
                } elseif($assign_categorymethod == "exclude" && in_array($catid, $assign_categories)) {
                    // Dont process articles that ARE in the exclude list
                    $process_c = 0;
                }
            }
            // Article Assignment
            $process_a = 1;
            $assign_articlemethod = $params->get('assign_articlemethod', 'all');
            $assign_articles = $params->get('assign_articles', array());
            if($assign_articlemethod == "none") {
                // Dont process any articles
                $process_a = 0;
            } elseif($assign_articlemethod == "include" && !in_array($item->id, $assign_articles)) {
                // Dont process articles that are NOT in the include list
                $process_a = 0;
            } elseif($assign_articlemethod == "exclude" && in_array($item->id, $assign_articles)) {
                // Dont process articles that ARE in the exclude list
                $process_a = 0;
            }
            // Assignment Method
            if($assign_method=='all') {
                // Return if not all checks match
                if($process_c!=1 || $process_a!=1) return;
            } elseif($assign_method=='any') {
                // Return if none of the checks match
                if($process_c!=1 && $process_a!=1) return;
            }
        }
        
        $excludetags = explode(',', $params->get('excludetags', 'article,nothumbs,mp3,total,youtube,video'));
        $this->excludedrts = $excludetags;

        // load helper class
        require_once(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jiblogtools'.DS.'helper.php');
        $this->helper = new plgJiBlogToolsHelper();

        // load scripts and stylesheets
        $document = JFactory::getDocument();

        // load modal libraries
        $this->loadModalLibrary($params);

        $document->addStyleSheet(JURI::root().'media/jiblogtools/css/jiblogtools.css');

        // get menu item id
        if(version_compare(JVERSION, '2.5.0', 'ge' )) {
            $Itemid = $jinput->get('Itemid', 0, 'int');
        } else {
            $Itemid = JRequest::getVar('Itemid');
        }

        $contextparts = explode('.', $context);
        $itemcontext = end($contextparts);
        if($itemtype=='category') {
            if($params->get('cat_mirror_blog', 0)==0) {
                $prefix = 'cat';
            } else {
                $prefix = 'blog';
            }
        } elseif($itemtype=='other') {

        } elseif($itemcontext=='article') {
            $prefix = 'art';
        } elseif($itemcontext=='category' || $itemcontext=='featured') {
            $prefix = 'blog';
        }
        // not a compatible item type
        if(!isset($prefix)) return;
        
        if($itemtype=='article' && $prefix=='blog' && isset($item->params)) {
            // count blog items to find leading
            $GLOBALS['JiBTArticle_Count'] = (isset($GLOBALS['JiBTArticle_Count']))? $GLOBALS['JiBTArticle_Count']+1 : 1;
            if($GLOBALS['JiBTArticle_Count'] <= $item->params->get('num_leading_articles', 1) && $params->get('lead_mirror_blog', 1)==0) {
                // leading items have different settings
                $prefix = 'lead';
            }
        }

        // check for static readmore
        if(isset($sources['fulltext'])) {
            // static readmore exists
            $hasReadmore = true;
            if($params->get($prefix.'_readmore_respect', 1)==0 && ($itemtype=='article' || $itemtype=='category')) {
                // ignore existing readmore

                // com_content strips the fulltext if there is a static readmore, so lets go fetch it again
                if($itemtype=='article') {
                    list($introtext, $fulltext) = $this->getArticleText($item);
                } elseif($itemtype=='category') {
                    list($introtext, $fulltext) = $this->getCategoryText($item);
                }

                $item->introtext = $introtext;
                $item->fulltext = $introtext.$fulltext;
                $item->text = $item->fulltext;

                // re-apply content plugins to new text
                /*$dispatcher	= JEventDispatcher::getInstance();
                $params = new JRegistry();
                $item->hasJiBlogTools = true;
                $results = $dispatcher->trigger('onContentPrepare', array($context, &$item, &$params, null));*/
                $sources['introtext'] = $item->introtext;
                $sources['fulltext'] = $item->fulltext;
                $sources['text'] = $item->text;
            }
        } else {
            // static readmore does not exist
            $hasReadmore = false;
        }

        // check whether to include in featured contexts
        if(strstr($context, 'featured')!==false) {
            if($params->get($prefix.'_featured_articles', 1)==0) return;
        }

        // choose source text
        if(isset($sources['text'])) {
            $text = $sources['text'];
        } elseif(isset($sources['fulltext'])) {
            $text = $sources['fulltext'];
        }

        // article overrides
        $regex = "#{(.*?)nothumbs(.*?)}#s";
        preg_match_all($regex, $text, $matches);
        $count = count($matches[0]);
        if($count>0) {
            $override = $matches[0][0];
            if(strstr($override, 'nothumbs')!=false) {
                $replacement = '';
                // remove overrides
                if($pagecontext=='com_content.article' && strstr($override, 'article')==false) {
                    // return without processing
                    $item->introtext = preg_replace($regex, $replacement, $item->introtext);
                    $item->fulltext = preg_replace($regex, $replacement, $item->fulltext);
                    $item->text = preg_replace($regex, $replacement, $item->text);
                    return;
                } else {
                    $text = preg_replace($regex, $replacement, $text);
                }
            }
        }

        // process item
        $icount = 1;

        require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
        if($itemtype=='article') {
            // create article link (adding it to this class allows all functions to access it)
            $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid));
        } elseif($itemtype=='category') {
            $item->link = ContentHelperRoute::getCategoryRoute($catid);
        } else {
            $item->link = '#';
        }
        $item->readmore = $this->displayReadmore($item, $prefix);
        $item->count = $icount;
        $item->mid = $Itemid;
        $this->item = &$item;
        $this->helper->item = &$item;

        /* >>> PRO >>> */
        $images = array();
        $image = '';
        $thumbpos = $params->get($prefix.'_thumbs_pos', 'beforedesc');

        $group = $params->get($prefix.'_thumbs_group', 'all');
        $groupi = 1;
        if($params->get($prefix.'_thumbs_enabled', 1)==1) {
            // check if the text has an image tag
            if(stripos($text, '<img')!==false) {
                // find existing images
                $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
                preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
                if($matches!=null) {
                    foreach($matches as $match) {
                        $data = array('source'=>$match[2]);
                        // Find all IMG attributes
                        $imageattribs = array();
                        preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                        if($iattribmatches!=null) {
                            foreach($iattribmatches as $iattribmatch) {
                                if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = $iattribmatch[2];
                            }
                        }
                        $data['imageattribs'] = $imageattribs;
                        // Group test
                        if($group=='include' || $group=='exclude') {
                            $groupi = 0;
                            // Find image classes
                            $iclasses = array();
                            if(isset($imageattribs['class'])) $iclasses = explode(' ', $imageattribs['class']);

                            if(in_array($params->get($prefix.'_thumbs_groupclass'), $iclasses)) {
                                if($group=='include') $groupi = 1;
                            } elseif($group=='exclude') {
                                $groupi = 1;
                            }
                        }
                        $this->item->class = (isset($imageattribs['class']))? $imageattribs['class'] : '';
                        $imagex = preg_quote($match[0]);

                        if($params->get($prefix.'_thumbs_removeparas', 1)) {
                            // remove surrounding paragraphs
                            $text = preg_replace('#<p(.*)>+\s*('.$imagex.')\s*</p>+#i', $match[0], $text);
                        }

                        $linkattribs = array();
                        // Find all Link attributes
                        preg_match_all('#<a(.*)>+\s*('.$imagex.')\s*</a>+#i', $text, $linkmatches, PREG_SET_ORDER);
                        if(isset($linkmatches[0][0])) {
                            preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $linkmatches[0][1], $lattribmatches, PREG_SET_ORDER);
                            if($lattribmatches!=null) {
                                foreach($lattribmatches as $lattribmatch) {
                                    if(isset($lattribmatch[1])) $linkattribs[$lattribmatch[1]] = $lattribmatch[2];
                                }
                            }
                            // Strip link from HTML
                            $text = preg_replace('@'.$linkmatches[0][0].'@s', '', $text);
                        }
                        $data['linkattribs'] = $linkattribs;

                        $item->count = $icount;
                        $image = $this->addImage($data, $prefix, $item, $icount, $imageattribs);
                        if($image!=false) {
                            $imagex = '@'.preg_quote($match[0]).'@s';
                            if($params->get($prefix.'_thumbs_count', 10)>0 && $icount>$params->get($prefix.'_thumbs_count', 10)) {
                                // Remove images from the rest of the article
                                $text = preg_replace('#'.$match[0].'#s', '', $text);
                                $icount = $params->get($prefix.'_thumbs_count', 10)+1;
                            } elseif($group!='none' && $groupi==1) {
                                // Remove images and group the thumbnails
                                $images[] = $image;
                                $text = preg_replace($imagex, '', $text);
                                $icount++;
                            } else {
                                // Display the thumbnails in place of the images
                                $text = preg_replace($imagex, $image, $text);
                                $icount++;
                            }
                        }
                    }
                }
            }

            $placeholdertype = $params->get($prefix.'_placeholder', 'none');
            if($icount==1 && $placeholdertype!='none') {
                if($placeholdertype=='image') {
                    $image = $this->addImage(array('source'=>$params->get($prefix.'_imageplaceholder')), $prefix, $item, $icount);
                    $placeholder = $image;
                } elseif($placeholdertype=='html') {
                    $placeholder = $params->get($prefix.'_htmlplaceholder');
                }
            }
        }
        $item->thumbs = $images;
        $this->item->count = ($icount-1);
        if($group=='all' || isset($placeholder)) {
            $groupprefix = $params->get($prefix.'_group_prefix', '');
            $groupsuffix = $params->get($prefix.'_group_suffix', '');
            if(isset($placeholder)) {
                $imageshtml = $groupprefix.$placeholder.$groupsuffix;
            } else {
                $imageshtml = $groupprefix.implode('', $images).$groupsuffix;
            }
            // Dynamic Replacement Tags
            $imageshtml = $this->getDRT($imageshtml);
        }
        /* <<< PRO <<< */

        // build description
        $introtext = '';
        $fulltext = '';
        /* >>> PRO >>> */
        if($thumbpos=='beforedesc') {
            $introtext.= $imageshtml;
            $fulltext.= $imageshtml;
        }
        /* <<< PRO <<< */

        $fulltext.= $text;
        if($prefix!='art') {
            if(isset($item->title) && $params->get($prefix.'_title_truncate', 1)==1) {
                // truncate title
                $item->title = $this->truncate($item->title, $params->get($prefix.'_title_length', 35));
            }
            $truncate = $params->get($prefix.'_desc_truncate', 1);
            if($truncate==0 || ($hasReadmore && $params->get($prefix.'_readmore_respect', 1)==1)) {
                // Don't crop article text
                $introtext.= $text;
                if($params->get($prefix.'_readmore_insert', 1)==1) {
                    // Add Readmore link
                    $introtext.= $item->readmore;
                    // Remove old read mores
                    $item->readmore = 0;
                }
            } elseif($truncate==1) {
                // Truncate Article Text
                $cropsuffix = $params->get($prefix.'_desc_truncate_suffix', '{readmore}');
                $cropsuffix = $this->getDRT($cropsuffix);
                $text = $this->truncate($text, $params->get($prefix.'_desc_length', 220), $cropsuffix, true, $params->get($prefix.'_desc_truncate_suffixpos', 0), 0);
                $introtext.= $text;
                // Remove old read mores
                if($params->get($prefix.'_readmore_insert', 1)==1) $item->readmore = 0;
            } elseif($truncate==2) {
                // Truncate Article Text
                $cropsuffix = $params->get($prefix.'_desc_truncate_suffix', '{readmore}');
                $cropsuffix = $this->getDRT($cropsuffix);
                $text = $this->truncateWords($text, $params->get($prefix.'_desc_length', 20), $cropsuffix, true, $params->get($prefix.'_desc_truncate_suffixpos', 0), 0);
                $introtext.= $text;
                // Remove old read mores
                if($params->get($prefix.'_readmore_insert', 1)==1) $item->readmore = 0;
            } elseif($truncate==3) {
                // Truncate Article Text
                $cropsuffix = $params->get($prefix.'_desc_truncate_suffix', '{readmore}');
                $cropsuffix = $this->getDRT($cropsuffix);
                $text = $this->truncateParagraphs($text, $params->get($prefix.'_desc_length', 20), $cropsuffix, true, $params->get($prefix.'_desc_truncate_suffixpos', 0), 0);
                $introtext.= $text;
                // Remove old read mores
                if($params->get($prefix.'_readmore_insert', 1)==1) $item->readmore = 0;
            } elseif($truncate==4) {
                $break = $params->get($prefix.'_desc_truncate_break', '/(<\/p>\s*|<br>|<br?\/>)|i\s/');
                if(!is_null($break)) {
                    // Truncate Article Text
                    $cropsuffix = $params->get($prefix.'_desc_truncate_suffix', '{readmore}');
                    $cropsuffix = $this->getDRT($cropsuffix);
                    $text = $this->truncateParagraphs($text, $params->get($prefix.'_desc_length', 20), $cropsuffix, true, $params->get($prefix.'_desc_truncate_suffixpos', 0), 0, $break);
                }
                $introtext.= $text;
                // Remove old read mores
                if($params->get($prefix.'_readmore_insert', 1)==1) $item->readmore = 0;
            }
        }

        /* >>> PRO >>> */
        if($thumbpos=='afterdesc') {
            $introtext.= $imageshtml;
            $fulltext.= $imageshtml;
        }
        /* <<< PRO <<< */

        // overwrite the text variables
        if($pagecontext=='com_content.category') {
            $item->introtext = $introtext;
            $item->fulltext = $fulltext;
            $item->text = $introtext;
        } else {
            $item->text = $fulltext;
        }
        $item->hasJiBlogTools = true;
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

    public function addImage($data=array(), $prefix, $item, $icount)
    {
        // Get thumbnail
        $thumbnail = $this->helper->getHTML($data, $item->link, $prefix, $item, $icount);
        return $thumbnail;
    }

    public function displayReadmore(&$item, $prefix)
    {
        $params = $this->getParams();
        
        $html = '<a href="'.$item->link.'">'.$params->get($prefix.'_readmore_text', 'Read more...').'</a>';
        // Add prefix
        $prefix = $params->get($prefix.'_readmore_prefix');
        if($prefix!=null) $html = $prefix.$html;
        // Add suffix
        $suffix = $params->get($prefix.'_readmore_suffix');
        if($suffix!=null) $html = $html.$suffix;
        return $html;
    }

    public function getArticleText($item)
    {
        // Load full article from database
        $db = JFactory::getDBO();
        $query = 'SELECT `introtext`, `fulltext` FROM #__content WHERE id="'.$item->id.'"';
        $db->setQuery($query);
        $result = $db->loadAssoc();
        if(!$result) return array('', '');
        return array_values($result);
    }

    public function getCategoryText($item)
    {
        // Load full article from database
        $db = JFactory::getDBO();
        $query = 'SELECT `introtext`, `fulltext` FROM #__categories WHERE id="'.$item->id.'"';
        $db->setQuery($query);
        $result = $db->loadAssoc();
        if(!$result) return array('', '');
        return array_values($result);
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
        if((strlen($text) > $length || $force==1) && $insert==1) { $one .= $suffix; }
        // Re-attach tags
        $output = $one . implode($tags[0]);
        // Add suffix outside last tag if needed
        if((strlen($text) > $length || $force==1)&& $insert==0) { $output .= $suffix; }

        //added to remove  unnecessary closure
        $output = str_replace('</!-->','',$output); 

        return $output;
    }

    public function truncateWords($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0)
    {
        $tok = strtok($text, " \n");
        $output = $tok;
        $i = 1;
        while($tok!==false && $i<$length) {
            $i++;
            $tok = strtok(" \n");
            $output.= ' '.$tok;
        }
        if($i>=$length || $force==1) {
            if($isHTML) {
                if($insert==1) $output.= $suffix;
                $output = $this->restoreTags($output);
            }
            if($insert==0 || !$isHTML) $output.= $suffix;
        }
        return $output;
    }

    public function truncateParagraphs($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0, $break='/(<\/p>)|i\s/')
    {
        // Find bits
        $bits = preg_split($break, $text, -1, PREG_SPLIT_NO_EMPTY);
        // Find delimiters (endtags)
        preg_match_all($break, $text, $endtags, PREG_SET_ORDER);

        if($bits==null) return $text;

        if(count($bits)>$length || $force==1) {
            $output = '';
            $key = 0;
            // Rebuild text up to the length limit
            while($key<$length && isset($bits[$key])) {
                $output.= $bits[$key];
                if($key==($length-1) && $insert==1) $output.= $suffix;
                $output.= $endtags[$key][0];
                $key++;
            }
            if($isHTML) $output = $this->restoreTags($output);
            if($insert==0 || !$isHTML) $output.= $suffix;
        } else {
            $output = $text;
        }
        return $output;
    }

    public function restoreTags($input)
    {
        $opened = array();

        // loop through opened and closed tags in order
        if(preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches)) {
            foreach($matches[1] as $tag) {
                if(preg_match("/^[a-z]+$/i", $tag, $regs)) {
                    // a tag has been opened
                    if(strtolower($regs[0]) != 'br') $opened[] = $regs[0];
                } elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs)) {
                    // a tag has been closed'
                    $tmp = array_keys($opened, $regs[1]);
                    $key = array_pop($tmp);
                    unset($opened[$key]);
                }
            }
        }

        // close tags that are still open
        if($opened) {
            $tagstoclose = array_reverse($opened);
            foreach($tagstoclose as $tag) $input .= "</$tag>";
        }

        return $input;
    }

    public function getDRT($text) {
        $regex = "#{(.*?)}#s";
        $text = preg_replace_callback($regex, array(&$this,'replaceAttribute'), $text);
        return $text;
    }
    public function replaceAttribute($matches) {
        $result = '';
        if(isset($matches[1])) {
            $attr = $matches[1];
            if(strstr($matches[1], '{')!=false || strstr($matches[1], '}')!=false || in_array($attr, $this->excludedrts) || (isset($this->includedrts) && !in_array($attr, $this->includedrts))) {
                $result = $matches[0];
            } else {
                if(isset($this->item->{$attr})) $result = $this->item->{$attr};
            }
        }
        return $result;
    }

    private function getPageContext()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->get('option');
        $view = $jinput->get('view');

        $pagecontext = '';
        if(($option=='com_content' && ($view=='category' || $view=='featured' || $view=='search')) || ($option=='com_jicustomfields' && ($view=='category' || $view=='featured' || $view=='search'))) {
            $pagecontext = 'com_content.category';
        } elseif($option=='com_content' && $view=='article') {
            $pagecontext = 'com_content.article';
        }
        return $pagecontext;
    }
    
    private function getParams($pluginType='content', $pluginName='jiblogtools')
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
            if(!isset($this->params) || !is_array($this->params)) $this->params = array();
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