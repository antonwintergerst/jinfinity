<?php
/**
 * @version     $Id: listfilter.php 049 2014-07-21 17:19:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiListFilterHelper {
    function loadType($classname, $includepath) {
        if(isset($classname) && isset($includepath)) {
            if(file_exists($includepath)) {
                require_once($includepath);
                $classname = 'JiListFilter'.$classname;
                if(class_exists($classname)) $listfilter = new $classname;
            }
        }
        if(!isset($listfilter)) $listfilter = new JiListFilter();
        
        return $listfilter;
    }
}
class JiListFilter {
    protected $scope;
    protected $name;
    protected $paths;
    protected $tmpdir;
    protected $paramspath;
    function setScope($scope) {
        $this->scope = $scope;
        $this->tmpdir = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'tmp';
        $paramsdir = $this->tmpdir.DS.'params';
        $this->paramspath = $paramsdir.DS.$this->scope.'.json';
    }
    function setName($name) {
        $this->name = $name;
    }
    function open() {
    }
    function includePath() {
    }
    function excludePath() {
    }
}