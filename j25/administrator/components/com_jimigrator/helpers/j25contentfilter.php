<?php
/**
 * @version     $Id: j25contentfilter.php 154 2014-07-21 17:19:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiListFilterContent extends JiListFilter
{
    // TODO: Move this out of construct
    function __construct() {
        $this->rootdir = '/';
        $this->rootlvl = 1;

        // Set Paths
        jimport('joomla.application.component.helper');
        $jparams = JComponentHelper::getParams('com_jimigrator');

        if(version_compare(JVERSION, '2.5.0', 'ge')) {
            $jinput = JFactory::getApplication()->input;
            $this->setPaths($jinput->get('ffpath', '', 'raw'));
            $this->start = $jinput->get('start', 0);
            $this->limit = (int) $jparams->get('list_limit',50);
            $this->reset = $jinput->get('reset', 0);

            $this->params = null;
            $this->searchword = $jinput->get('ffsearchword', null);

            $this->setScope($jinput->get('scope'));
            $this->setName($jinput->get('name'));
        } else {
            $this->setPaths(JRequest::getVar('ffpath'));
            $this->start = JRequest::getVar('start', 0);
            $this->limit = JRequest::getVar('list_limit', 50);
            $this->reset = JRequest::getVar('reset', 0);

            $this->params = null;
            $this->searchword = JRequest::getVar('ffsearchword');

            $this->setScope(JRequest::getVar('scope'));
            $this->setName(JRequest::getVar('name'));
        }

        $this->helper = new ContentHelper();
        $this->helper->searchword = $this->searchword;
    }

    /**
     * Sets current path/s
     * @param $data
     */
    function setPaths($data) {
        $this->paths = array();
        if(is_array($data)) {
            foreach($data as $datapath) {
                $path = new stdClass();
                $path->relpath = $this->sanitizePath($datapath);
                $path->abspath = ($path->relpath!='')? $this->rootdir.$path->relpath : $this->rootdir;
                $this->paths[] = $path;
            }
        } else {
            $path = new stdClass();
            //$data = '/8:tier1/9:tier2';
            $path->relpath = $this->sanitizePath($data);
            $path->abspath = ($path->relpath!='')? $this->rootdir.$path->relpath : $this->rootdir;
            $this->paths[] = $path;
        }
    }

    /**
     * Strips path of invalid characters
     * @param $path
     * @return string
     */
    function sanitizePath($path) {
        // Sanitize path
        $path = str_replace(array('..', './'), '', $path);
        $path = preg_replace('/\/\/+/', '/', $path);
        $path = trim($path, '/');
        return $path;
    }

    /**
     * Method to build crumbs and set parent path
     * @param object $path
     * @return object
     */
    function processPath($path) {
        $result = new stdClass();
        $parentpath = '';

        $crumbs = array();
        $subpaths = explode('/', $path->relpath);
        $total = count($subpaths);
        if($total>0) {
            $relpath = '';
            foreach($subpaths as $key=>$subpath) {
                $relpath.= $subpath;
                if($relpath!='') {
                    $crumb = array('path'=>$relpath, 'name'=>$subpath);
                    $crumbs[] = $crumb;
                }
                // Set parent folder
                if($key==$total-2) $parentpath = $relpath;
                $relpath.= '/';
            }
        }

        $result->currentlevel = count(explode('/', trim($path->abspath, '/')));
        $result->currentpath = end($subpaths);
        $result->parentpath = $parentpath;
        $result->crumbs = $crumbs;
        return $result;
    }

    /**
     * Main function used to "open" content "directories"
     * @return array|void
     */
    function open() {
        $return = array();
        $this->itemlist = array();
        $this->sortlist = array();

        if($this->reset==1) $this->clearParams();

        $return['searchword'] = $this->searchword;
        $return['start'] = $this->start;

        // Process Path
        $response = $this->processPath($this->paths[0]);
        $currentlevel = $response->currentlevel;
        $currentpath = $response->currentpath;
        $parentpath = $response->parentpath;
        $return['crumbs'] = $response->crumbs;

        $hasdata = true;
        if($this->searchword==null) {
            // Regular open path
            if($currentpath=='') {
                $response = $this->helper->getCategories(null, 1, $this->start, $this->limit);
                if($response->total>0) {
                    foreach($response->items as $category) {
                        $item = $this->categoryToItem($category);
                        $this->itemlist[] = $item;
                        $this->sortlist[] = '0'.$item['name'];
                    }
                    if($response->hasmore) {
                        $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit));
                        $this->itemlist[] = $moreitem;
                        $this->sortlist[] = '2'.$moreitem['name'];
                    }
                }
            } else {
                // Add root item
                $rootitem = array('path'=>$parentpath, 'name'=>'..', 'type'=>'folder', 'root'=>'true');
                $this->itemlist[] = $rootitem;
                $this->sortlist[] = '0';

                $hasdata = false;
                $exists = $this->helper->is_category($currentpath, $currentlevel);
                if($exists!=null) {
                    $hasdata = true;
                    $response = $this->helper->getCategories($exists->id, null, $this->start, $this->limit);
                    if($response->total>0) {
                        foreach($response->items as $category) {
                            $item = $this->categoryToItem($category);
                            $this->itemlist[] = $item;
                            $this->sortlist[] = '1'.$item['name'];
                        }
                        if($response->hasmore) {
                            $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit));
                            $this->itemlist[] = $moreitem;
                            $this->sortlist[] = '2'.$moreitem['name'];
                        }
                    }
                    $response = $this->helper->getArticles($exists->id, $this->start, $this->limit);
                    if($response->total>0) {
                        foreach($response->items as $article) {
                            $item = $this->articleToItem($article);
                            $this->itemlist[] = $item;
                            $this->sortlist[] = '2'.$item['name'];
                        }
                        if($response->hasmore) {
                            $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit));
                            $this->itemlist[] = $moreitem;
                            $this->sortlist[] = '2'.$moreitem['name'];
                        }
                    }
                } else {
                    $hasdata = false;
                }
            }
        } else {
            // Open path with searchword
            $response = $this->helper->getCategories(null, null, $this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $category) {
                    $item = $this->categoryToItem($category);
                    $this->itemlist[] = $item;
                    $this->sortlist[] = '1'.$item['name'];
                }
                if($response->hasmore) {
                    $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit), 'searchmore'=>true);
                    $this->itemlist[] = $moreitem;
                    $this->sortlist[] = '2'.$moreitem['name'];
                }
            }
            $response = $this->helper->getArticles(null, $this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $article) {
                    $item = $this->articleToItem($article);
                    $this->itemlist[] = $item;
                    $this->sortlist[] = '2'.$item['name'];
                }
                if($response->hasmore) {
                    $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit), 'searchmore'=>true);
                    $this->itemlist[] = $moreitem;
                    $this->sortlist[] = '2'.$moreitem['name'];
                }

            }
        }
        if($hasdata) {
            $resetitem = array('path'=>$currentpath, 'name'=>'- Reset Selection -', 'type'=>'system', 'task'=>'reset');
            $this->itemlist[] = $resetitem;
            $this->sortlist[] = '3'.$resetitem['name'];

            array_multisort($this->sortlist, $this->itemlist, SORT_ASC);

            $return['items'] = $this->itemlist;
            $return['valid'] = true;
        } else {
            $return['valid'] = false;
        }

        return $return;
    }

    /**
     * Converts category to list item
     * @param $category
     * @return array
     */
    function categoryToItem($category) {
        $path = $this->getCategoryPath($category);
        $item = array();
        $item['path'] = $path;
        $item['slug'] = $category->id.':'.$category->alias;
        $item['name'] = $category->title;
        $item['state'] = $this->getState('/'.$item['path']);
        $response = $this->getCategoryChildStates('/'.$item['path'], $item['state']);
        $item['childoverrides'] = array('included'=>count($response['included']), 'excluded'=>count($response['excluded']));
        $item['type'] = 'folder';
        return $item;
    }

    /**
     * Returns path for a category
     * @param $category
     * @return string
     */
    function getCategoryPath($category) {
        $parentcats = $this->helper->getParentCats();
        $paths = array();
        $paths[] = $category->id.':'.$category->alias;
        $parentid = $category->parent_id;
        $i = 0;
        while(isset($parentcats[$parentid]) && $i<10) {
            $parent = $parentcats[$parentid];
            if($parentid==$parent->parent_id) break;
            $paths[] = $parent->id.':'.$parent->alias;
            $parentid = $parent->parent_id;
            $i++;
        }
        $paths = array_reverse($paths);
        $path = implode('/', $paths);
        return $path;
    }

    /**
     * Converts article to a list item
     * @param $article
     * @return array
     */
    function articleToItem($article) {
        $path = $this->getArticlePath($article);
        $item = array();
        $item['path'] = $path;
        $item['slug'] = $article->id.':'.$article->alias;
        $item['name'] = $article->title;
        $item['state'] = $this->getState('/'.$item['path']);
        return $item;
    }

    /**
     * Returns path for an article
     * @param $article
     * @return string
     */
    function getArticlePath($article) {
        $parentcats = $this->helper->getParentCats();
        $paths = array();
        $paths[] = $article->id.':'.$article->alias;
        $parentid = $article->catid;
        while(isset($parentcats[$parentid])) {
            $parent = $parentcats[$parentid];
            if($parentid==$parent->parent_id) break;
            $paths[] = $parent->id.':'.$parent->alias;
            $parentid = $parent->parent_id;
        }
        $paths = array_reverse($paths);
        $path = implode('/', $paths);
        return $path;
    }

    /**
     * Gets params for the current field
     * @return array|mixed|null
     */
    function getParams() {
        if($this->params==null) {
            // Setup params directory
            $paramsdir = $this->tmpdir.DS.'params';
            if(!file_exists($paramsdir)) {
                mkdir($paramsdir);
                // Set correct directory permssions
                chmod($paramsdir, 0755);
                // Add index.html
                if(file_exists($this->rootdir.'/images/index.html')) copy($this->rootdir.'/images/index.html', $paramsdir.'/index.html');
            }
            // Check for existing params
            if(file_exists($this->paramspath)) {
                $params = file_get_contents($this->paramspath);
            } else {
                $params = '';
            }
            $params = json_decode($params, true);
            $this->params = ($params==null || !is_array($params))? array() : $params;
        }
        return $this->params;
    }

    function clearParams() {
        if(file_exists($this->paramspath)) unlink($this->paramspath);
    }

    /**
     * Returns state for a path (Optionally checking parent state inheritance)
     * @param $path
     * @param bool $checkparent
     * @return string
     */
    function getState($path, $checkparent=true) {
        $state = '';
        $this->getParams();

        if(isset($this->params[$this->name][$path])) {
            // Check if file/dir has a state
            $state = $this->params[$this->name][$path];
        } elseif($checkparent) {
            // Check if a parent directory has a state
            $parentpaths = explode('/', $path);
            $total = count($parentpaths);
            foreach($parentpaths as $key=>$path) {
                $path = implode('/', array_slice($parentpaths, 0, $total - $key));
                if(isset($this->params[$this->name][$path])) {
                    $state = $this->params[$this->name][$path];
                    return $state;
                    break;
                }
            }
        }
        return $state;
    }

    /**
     * Returns child states of a category
     * @param $path
     * @param string $parentstate
     * @return array
     */
    function getCategoryChildStates($path, $parentstate='') {
        $pathparts = explode('/', trim($path, '/'));
        $level = count($pathparts);
        $currentpath = end($pathparts);
        $included = array();
        $excluded = array();
        $exists = $this->helper->is_category($currentpath, $level);
        if($exists!=null) {
            $hasdata = true;
            $response = $this->helper->getCategories($exists->id, null, $this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $item) {
                    $subpath = $path.'/'.$item->id.':'.$item->alias;
                    $state = $this->getState($subpath, false);
                    if($state=='include' && $parentstate!='include') {
                        $included[$subpath] = 'include';
                    } elseif($state=='exclude' && $parentstate!='exclude') {
                        $excluded[$subpath] = 'exclude';
                    }
                    // Get child category states
                    $parentid = $item->parent_id;
                    $childcats = $this->helper->getChildCats($parentid);
                    if($childcats!=null) {
                        foreach($childcats as $childcategory) {
                            $response = $this->getCategoryChildStates($subpath, $parentstate);
                            $included = array_merge($included, $response['included']);
                            $excluded = array_merge($excluded, $response['excluded']);
                        }
                    }
                }
            }
            $response = $this->helper->getArticles($exists->id, $this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $item) {
                    $subpath = $path.'/'.$item->id.':'.$item->alias;
                    $state = $this->getState($subpath, false);
                    if($state=='include' && $parentstate!='include') {
                        $included[$subpath] = 'include';
                    } elseif($state=='exclude' && $parentstate!='exclude') {
                        $excluded[$subpath] = 'exclude';
                    }
                }
            }
        }
        $return = array('included'=>$included, 'excluded'=>$excluded);
        return $return;
    }

    /**
     * Add a direct inclusion path
     * @return array
     */
    function includePath() {
        $return = array();
        $this->getParams();

        // Add path/s to params
        foreach($this->paths as $path) {
            $this->params[$this->name][$path->abspath] = 'include';
        }

        // Save params
        file_put_contents($this->paramspath, json_encode($this->params));

        $return['valid'] = true;
        return $return;
    }

    /**
     * Add a direct exclusion path
     * @return array
     */
    function excludePath() {
        $return = array();
        $this->getParams();

        // Add path/s to params
        foreach($this->paths as $path) {
            $this->params[$this->name][$path->abspath] = 'exclude';
        }

        // Save params
        file_put_contents($this->paramspath, json_encode($this->params));

        $return['valid'] = true;
        return $return;
    }

    /**
     * Converts bytes to a human readable form
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}

/**
 * Common functions for Content list building
 * Class ContentHelper
 */
class ContentHelper {
    public $db;
    /**
     * Method to test of path is for a category
     * @param $path
     * @param $level
     * @return mixed
     */
    function is_category($path, $level=0) {
        $db = JFactory::getDBO();

        $id = (int) $path;
        $level = (int) $level;
        $query = 'SELECT `id`, `parent_id` FROM #__categories';
        if($id!=0) {
            $query.= ' WHERE `id`='.$db->quote($path);
        } else {
            $query.= ' WHERE `alias`='.$db->quote($path);
        }
        if($level!=0) $query.= ' AND `level`='.$level;
        $db->setQuery($query);
        $result = $db->loadObject();
        // Try again for title
        if($result==null) {
            $search = '%'.$db->getEscaped($path, true).'%';
            $query = 'SELECT `id`, `parent_id` FROM #__categories WHERE `title` LIKE '.$db->quote($search, false);
            if($level!=0) $query.= ' AND `level`='.$level;
            $db->setQuery($query);
            $result = $db->loadObject();
        }
        if($result==null) return false;
        return $result;
    }

    /**
     * Returns array of category objects
     * @param int $pid
     * @param int $level
     * @param int $start
     * @param int $limit
     * @return stdClass
     */
    function getCategories($pid=null, $level=null, $start=0, $limit=20) {
        $db = JFactory::getDBO();

        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `title`, `alias`, `parent_id` FROM #__categories';
        $query.= ' WHERE `extension`="com_content"';
        if($level!=null && (int)$level!=0) $query.= ' AND `level`='.(int)$level;
        if($pid!=null) {
            $query.= ' AND `parent_id`='.(int)$pid;
        } elseif($this->searchword!=null) {
            $search = '%'.$db->getEscaped($this->searchword, true).'%';
            $query.= ' AND `title` LIKE '.$db->quote($search, false);
        }
        $query.= ' ORDER BY `title` ASC';

        $db->setQuery($query, $start, $limit);
        $items = $db->loadObjectList();
        if($items!=null) {
            $query = 'SELECT FOUND_ROWS();';
            $db->setQuery($query);
            $total = $db->loadResult();
        } else {
            $items = array();
            $total = 0;
        }
        $return = new stdClass();
        $return->items = $items;
        $return->total = $total;
        $return->hasmore = ($start + $limit < $total);
        return $return;
    }
    function getParentCats($start=0, $limit=20) {
        if(!isset($this->parentcats)) {
            $db = JFactory::getDBO();
            // Get Parents
            $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `title`, `alias`, `parent_id` FROM #__categories';
            $query.= ' WHERE `extension`="com_content"';
            $db->setQuery($query, $start, $limit);
            $this->parentcats = $db->loadObjectList('id');
        }
        return $this->parentcats;
    }
    function getChildCats($pid) {
        $this->getParentCats();
        if(!isset($this->childcats)) {
            $childcats = array();
            foreach($this->parentcats as $category) {
                $childcats[$category->parent_id][] = $category;
            }
            $this->childcats = $childcats;
        }
        if(isset($this->childcats[$pid])) return $this->childcats[$pid];
        return;
    }

    /**
     * Method to test of path is for an article
     * @param $path
     * @return mixed
     */
    function is_article($path) {
        $db = JFactory::getDBO();
        $id = (int) $path;
        $query = 'SELECT `id` FROM #__content';
        if($id!=0) {
            $query.= ' WHERE `id`='.$db->quote($path);
        } else {
            $query.= ' WHERE `alias`='.$db->quote($path);
        }
        $db->setQuery($query);
        $result = $db->loadResult();
        // Try again for title
        if($result==null) {
            $search = '%'.$db->getEscaped($path, true).'%';
            $query = 'SELECT `id` FROM #__content WHERE `title` LIKE '.$db->quote($search, false);
            $db->setQuery($query);
            $result = $db->loadResult();
        }
        if($result==null) return false;
        return $result;
    }

    /**
     * Returns array of article objects
     * @param int $cid
     * @param int $start
     * @param int $limit
     * @return stdClass
     */
    function getArticles($cid=null, $start=0, $limit=20) {
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS a.id, a.title, a.alias, a.catid, c.alias AS calias FROM #__content AS a';
        $query.= ' LEFT JOIN #__categories AS c ON (c.id=a.catid)';
        if($cid!=null) {
            $query.= ' WHERE a.`catid`='.(int)$cid;
        } elseif($this->searchword!=null) {
            $search = '%'.$db->getEscaped($this->searchword, true).'%';
            $query.= ' WHERE a.`title` LIKE '.$db->quote($search, false);
        }
        $query.= ' ORDER BY a.`title` ASC';

        $db->setQuery($query, $start, $limit);
        $items = $db->loadObjectList();
        if($items!=null) {
            $query = 'SELECT FOUND_ROWS();';
            $db->setQuery($query);
            $total = $db->loadResult();
        } else {
            $items = array();
            $total = 0;
        }
        $return = new stdClass();
        $return->items = $items;
        $return->total = $total;
        $return->hasmore = ($start + $limit < $total);
        return $return;
    }
}