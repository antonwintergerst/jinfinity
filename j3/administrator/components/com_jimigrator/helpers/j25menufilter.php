<?php
/**
 * @version     $Id: j25menufilter.php 154 2014-07-21 17:19:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiListFilterMenu extends JiListFilter
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
            $this->limit = (int) $jparams->get('list_limit', 50);
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

        $this->helper = new MenuHelper();
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
            //$data = '/1:main';
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
                $response = $this->helper->getMenus($this->start, $this->limit);
                if($response->total>0) {
                    foreach($response->items as $menu) {
                        $item = $this->menuToItem($menu);
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
                if($currentlevel==1) {
                    $exists = $this->helper->is_menu($currentpath);
                    if($exists!=null) {
                        $hasdata = true;
                        $response = $this->helper->getMenuItems($exists->id, null, 1, $this->start, $this->limit);
                        if($response->total>0) {
                            foreach($response->items as $menuitem) {
                                $item = $this->menuItemToItem($menuitem);
                                $this->itemlist[] = $item;
                                $this->sortlist[] = '1'.$item['name'];
                            }
                            if($response->hasmore) {
                                $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit));
                                $this->itemlist[] = $moreitem;
                                $this->sortlist[] = '2'.$moreitem['name'];
                            }
                        }
                    }
                } else {
                    $exists = $this->helper->is_menuitem($currentpath);
                    if($exists!=null) {
                        $hasdata = true;
                        $response = $this->helper->getMenuItems(null, $exists->id, null, $this->start, $this->limit);
                        if($response->total>0) {
                            foreach($response->items as $menuitem) {
                                $item = $this->menuItemToItem($menuitem);
                                $this->itemlist[] = $item;
                                $this->sortlist[] = '1'.$item['name'];
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
            }
        } else {
            // Open path with searchword
            $response = $this->helper->getMenus($this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $menu) {
                    $item = $this->menuToItem($menu);
                    $this->itemlist[] = $item;
                    $this->sortlist[] = '1'.$item['name'];
                }
                if($response->hasmore) {
                    $moreitem = array('path'=>$currentpath, 'name'=>'Click to load more...', 'type'=>'system', 'task'=>'more', 'start'=>($this->start + $this->limit), 'searchmore'=>true);
                    $this->itemlist[] = $moreitem;
                    $this->sortlist[] = '2'.$moreitem['name'];
                }
            }
            $response = $this->helper->getMenuItems(null, null, null, $this->start, $this->limit);
            if($response->total>0) {
                foreach($response->items as $menuitem) {
                    $item = $this->menuItemToItem($menuitem);
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
     * Converts menu to list item
     * @param $menu
     * @return array
     */
    function menuToItem($menu) {
        $item = array();
        $item['path'] = '/'.$menu->id.':'.$menu->menutype;
        $item['slug'] = $menu->id.':'.$menu->menutype;
        $item['name'] = $menu->title;
        $item['state'] = $this->getState($item['path']);
        $response = $this->getMenuChildStates($item['path'], $item['state']);
        $item['childoverrides'] = array('included'=>count($response['included']), 'excluded'=>count($response['excluded']));
        $item['type'] = 'folder';
        return $item;
    }

    /**
     * Converts menu item to list item
     * @param $menuitem
     * @return array
     */
    function menuItemToItem($menuitem) {
        $path = $this->getMenuItemPath($menuitem);
        $item = array();
        $item['path'] = $path;
        $item['slug'] = $menuitem->id.':'.$menuitem->alias;
        $item['name'] = $menuitem->title;
        $item['state'] = $this->getState('/'.$item['path']);
        $response = $this->getMenuItemChildStates('/'.$item['path'], $item['state']);
        $item['childoverrides'] = array('included'=>count($response['included']), 'excluded'=>count($response['excluded']));
        // Show Menu Item as folder if it has children
        if($this->helper->getChildItems($menuitem->id)!=null) $item['type'] = 'folder';
        return $item;
    }

    /**
     * Returns path for a section
     * @param $menuitem
     * @return string
     */
    function getMenuItemPath($menuitem) {
        $parentitems = $this->helper->getParentItems();
        $paths = array();
        $paths[] = $menuitem->id.':'.$menuitem->alias;
        $parentid = $menuitem->parent_id;
        $i = 0;
        while(isset($parentitems[$parentid]) && $i<10) {
            $parent = $parentitems[$parentid];
            if($parentid==$parent->parent_id) break;
            $paths[] = $parent->id.':'.$parent->alias;
            $parentid = $parent->parent_id;
            $i++;
        }
        $paths[] = $menuitem->mtid.':'.$menuitem->menutype;
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
            // Check if path has a state
            $state = $this->params[$this->name][$path];
        } elseif($checkparent) {
            // Check if a parent path has a state
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
     * Returns child states of a menu
     * @param $path
     * @param string $parentstate
     * @return array
     */
    function getMenuChildStates($path, $parentstate='') {
        $pathparts = explode('/', trim($path, '/'));
        $currentpath = end($pathparts);
        $included = array();
        $excluded = array();
        $exists = $this->helper->is_menu($currentpath);

        if($exists!=null) {
            $items = $this->helper->getMenuItems($exists->id, null, 1);
            foreach($items as $item) {
                if(is_object($item)) {
                    $subpath = '/'.$currentpath.'/'.$item->id.':'.$item->alias;
                    $response = $this->getMenuItemChildStates($subpath, $parentstate);
                    $included = array_merge($included, $response['included']);
                    $excluded = array_merge($excluded, $response['excluded']);
                }
            }
        }
        $return = array('included'=>$included, 'excluded'=>$excluded);
        return $return;
    }

    /**
     * Returns child states of a menu item
     * @param $path
     * @param string $parentstate
     * @return array
     */
    function getMenuItemChildStates($path, $parentstate='') {
        $pathparts = explode('/', trim($path, '/'));
        $currentpath = end($pathparts);
        $included = array();
        $excluded = array();
        $exists = $this->helper->is_menuitem($currentpath);
        if($exists!=null) {
            $items = $this->helper->getMenuItems(null, $exists->id);
            foreach($items as $item) {
                if(is_object($item)) {
                    $subpath = $path.'/'.$item->id.':'.$item->alias;
                    $state = $this->getState($subpath, false);
                    if($state=='include' && $parentstate!='include') {
                        $included[$subpath] = 'include';
                    } elseif($state=='exclude' && $parentstate!='exclude') {
                        $excluded[$subpath] = 'exclude';
                    } else {
                        //$included[$subpath] = $state;
                    }
                    // Get child menu item states
                    $parentid = $item->parent_id;
                    $childitems = $this->helper->getChildItems($parentid);
                    if($childitems!=null) {
                        foreach($childitems as $childitem) {
                            $response = $this->getMenuItemChildStates($subpath, $parentstate);
                            $included = array_merge($included, $response['included']);
                            $excluded = array_merge($excluded, $response['excluded']);
                        }
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
 * Common functions for Menu list building
 * Class ContentHelper
 */
class MenuHelper {
    public $db;
    /**
     * Method to test if path is for a menu
     * @param $path
     * @return mixed
     */
    function is_menu($path) {
        $db = JFactory::getDBO();
        $id = (int) $path;
        $query = 'SELECT `id`, `menutype` FROM #__menu_types';
        if($id!=0) {
            $query.= ' WHERE `id`='.$db->quote($id);
        } else {
            $query.= ' WHERE `menutype`='.$db->quote($path);
        }
        $db->setQuery($query);
        $result = $db->loadObject();
        // Try again for title
        if($result==null) {
            $search = '%'.$db->getEscaped($path, true).'%';
            $query = 'SELECT `id`, `menutype` FROM #__menu_types WHERE `title` LIKE '.$db->quote($search, false);
            $db->setQuery($query);
            $result = $db->loadObject();
        }
        return $result;
    }

    /**
     * Returns array of menu objects
     * @param int $start
     * @param int $limit
     * @return stdClass
     */
    function getMenus($start=0, $limit=20) {
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `menutype`, `title` FROM #__menu_types';
        if($this->searchword!=null) {
            $search = '%'.$db->getEscaped($this->searchword, true).'%';
            $query.= ' WHERE `title` LIKE '.$db->quote($search, false);
        }
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

    /**
     * Method to test if path is for a menu item
     * @param $path
     * @return mixed
     */
    function is_menuitem($path) {
        $db = JFactory::getDBO();
        $id = (int) $path;
        $query = 'SELECT `id`, `menutype`, `parent_id` FROM #__menu WHERE `client_id`=0';
        if($id!=0) {
            $query.= ' AND `id`='.$db->quote($id);
        } else {
            $query.= ' AND `alias`='.$db->quote($path);
        }
        $db->setQuery($query);
        $result = $db->loadObject();
        // Try again for title
        if($result==null) {
            $search = '%'.$db->getEscaped($path, true).'%';
            $query = 'SELECT `id`, `menutype`, `parent_id` FROM #__menu WHERE `client_id`=0 AND `title` LIKE '.$db->quote($search, false);
            $db->setQuery($query);
            $result = $db->loadObject();
        }
        return $result;
    }

    /**
     * Returns array of menu item objects
     * @param int $mtid
     * @param int $pid
     * @param int $level
     * @param int $start
     * @param int $limit
     * @return stdClass
     */
    function getMenuItems($mtid=null, $pid=null, $level=null, $start=0, $limit=20) {
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS m.`id`, m.`title`, m.`alias`, m.`parent_id`, mt.`id` AS mtid, mt.`menutype` FROM #__menu AS m';
        $query.= ' LEFT JOIN #__menu_types AS mt ON (mt.`menutype`=m.`menutype`)';
        $query.= ' WHERE m.`client_id`=0';
        if($level!=null) $query.= ' AND m.`level`='.(int)$level;
        if($mtid!=null) $query.= ' AND mt.`id`='.(int)$mtid;
        if($pid!=null) {
            $query.= ' AND m.`parent_id`='.(int)$pid;
        } elseif($this->searchword!=null) {
            $search = '%'.$db->getEscaped($this->searchword, true).'%';
            $query.= ' AND m.`title` LIKE '.$db->quote($search, false);
        }
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
    function getParentItems($start=0, $limit=20) {
        if(!isset($this->parentitems)) {
            $db = JFactory::getDBO();
            // Get Parents
            $query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `title`, `alias`, `parent_id` FROM #__menu';
            $query.= ' WHERE `client_id`=0 AND `alias`!="root"';
            $db->setQuery($query, $start, $limit);
            $this->parentitems = $db->loadObjectList('id');
        }
        return $this->parentitems;
    }
    function getChildItems($pid) {
        $this->getParentItems();
        if(!isset($this->childitems)) {
            $childitems = array();
            foreach($this->parentitems as $menuitem) {
                $childitems[$menuitem->parent_id][] = $menuitem;
            }
            $this->childitems = $childitems;
        }
        if(isset($this->childitems[$pid])) return $this->childitems[$pid];
        return;
    }
}