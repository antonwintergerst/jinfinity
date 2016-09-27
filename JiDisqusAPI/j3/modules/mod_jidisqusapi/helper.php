<?php
/*
 * @version     $Id: mod_jidisuqsapi.php 100 2013-05-24 16:00:00Z Anton Wintergerst $
 * @package     Jinfinity Disqus API Module
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       antonwintergerst@gmail.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiDisqusAPIHelper
{
    private $apikey;
    private $forum;
    public function getData($params=null) {
        $this->params = $params;
        require_once JPATH_SITE.'/modules/mod_jidisqusapi/admin/cache.php';
        $this->cacheHelper = new JiDisqusAPICacheHelper();

        $this->apikey = $params->get('apikey');
        $this->forum = $params->get('forum');

        $data = $this->getListPopular();
        $items = array();
        if(isset($data->response)) {
            $sourceitems = array();
            $currenturl = JURI::current();
            // Get Filter Categories
            $filter_catids = $params->get('filter_categories', array(0));
            if(!is_array($filter_catids)) $filter_catids = array($filter_catids);
            $allcats = (in_array(0, $filter_catids))? true : false;
            // Include sub category articles
            $app = JFactory::getApplication();
            $appParams = $app->getParams();
            if($filter_catids) {
                if ($params->get('filter_categorychildren', 1) && (int) $params->get('filter_categorylevels', 3) > 0) {
                    // Get an instance of the generic categories model
                    $categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
                    $categories->setState('params', $appParams);
                    $levels = $params->get('filter_categorylevels', 3) ? $params->get('filter_categorylevels', 3) : 9999;
                    $categories->setState('filter.get_children', $levels);
                    $categories->setState('filter.published', 1);
                    //$categories->setState('filter.access', $access);
                    $additional_catids = array();

                    foreach ($filter_catids as $catid) {
                        $categories->setState('filter.parentId', $catid);
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
                    $filter_catids = array_unique(array_merge($filter_catids, $additional_catids));
                }
            }
            if($data->response!=null && is_array($data->response)) {
                foreach($data->response as $dataitem) {
                    $urlparams = $this->derouteURL($dataitem->link);
                    $proceed = false;
                    if(isset($urlparams['catid'])) {
                        if($allcats || in_array($urlparams['catid'], $filter_catids)) $proceed = true;
                    }
                    if($proceed) {
                        if(isset($sourceitems[$urlparams['id']])) {
                            $sourceitems[$urlparams['id']]->count++;
                        } else {
                            $sourceitem = new stdClass();
                            $sourceitem->count = 1;
                            $sourceitem->id = $urlparams['id'];
                            $sourceitem->catid = $urlparams['catid'];
                            $sourceitems[$urlparams['id']] = $sourceitem;
                        }
                    }
                }
                $i = 0;
                foreach($sourceitems as $sourceitem) {
                    $items[] = $this->getArticle($sourceitem);
                    $i++;
                    if($i==$params->get('limit', 5)) break;
                }
                $this->derouteURL($currenturl);
            }
        }
        return $items;
    }
    private function getListPopular() {
        $interval = $this->params->get('apiinterval');
        $limit = $this->params->get('apilimit');

        $request = 'http://disqus.com/api/3.0/threads/listPopular.json?';
        $request.= 'api_key='.$this->apikey;
        if($this->params->get('apisecret')) $request.= '&api_secret='.$this->params->get('apisecret');
        $request.= '&forum='.$this->forum.'&interval='.$interval.'&limit='.$limit;

        if($this->params->get('cachedata', 1)==1) {
            $data = $this->cacheHelper->get($request);
            if(!$data) {
                $data = file_get_contents($request);
                $this->cacheHelper->set($request, $data);
            }
        } else {
            $data = file_get_contents($request);
        }

        return json_decode($data);
    }
    public function derouteURL($link) {
        $router = JRouter::getInstance('site', array('mode'=>1));

        $linkparts = parse_url($link);
        $rellink = '';
        if(isset($linkparts['path'])) $rellink.= $linkparts['path'];
        if(isset($linkparts['query'])) $rellink.= $linkparts['query'];

        $uri =& JURI::getInstance($rellink);
        $routingArray = $router->parse($uri);
        return $routingArray;
    }
    public function getArticle($sourceitem) {
        $article = new stdClass();
        if(isset($sourceitem->id)) {
            $article->id = (int) $sourceitem->id;
            $article->catid = (int) $sourceitem->catid;

            $db =& JFactory::getDBO();
            $query = 'SELECT `alias`, `title`, `introtext`, `publish_up` FROM #__content WHERE state=1 AND id='.$article->id;
            $db->setQuery($query);
            $result = $db->loadObject();
            if($result!=null) {
                $article->alias = $result->alias;
                $article->title = $result->title;
                $article->introtext = $result->introtext;
                $article->publish_up = $result->publish_up;
            }

            $article->link = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
        }
        return $article;
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
}
?>
