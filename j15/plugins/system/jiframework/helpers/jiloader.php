<?php
/**
 * @version     $Id: jiloader.php 105 2013-08-07 23:53:00Z Anton Wintergerst $
 * @package     Jinfinity Loader for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
abstract class JiLoader {
    private static $loaded = array();

    /**
     * Method to load a class. Returns true on success
     * @param $classname
     * @param string|null $path
     * @return bool
     */
    public static function import($classname, $path=null) {
        // Sanitize classname
        $classname = strtolower($classname);
        // Set default source path
        if($path==null) $path = dirname(__FILE__).DS.$classname.'.php';
        if(isset(self::$loaded[$classname])) {
            // Class already loaded or not found
            return self::$loaded[$classname];
        } elseif(file_exists($path)) {
            require_once($path);
            if(class_exists($classname)) {
                self::$loaded[$classname] = true;
            } else {
                // Class not found
                self::$loaded[$classname] = false;
            }
        } else {
            // File not found
            self::$loaded[$classname] = false;
        }
        return self::$loaded[$classname];
    }
}

/**
 * Shortcut method to load a class via JiLoader. Returns true on success
 * @param $classname
 * @param string $path
 * @return bool
 */
function jiimport($classname, $path=null) {

    return JiLoader::import($classname, $path);
}

/**
 * Depreciated references
 */
jiimport('jiimageprocessor');
class plgJiImageProcessor extends JiImageProcessor
{
}