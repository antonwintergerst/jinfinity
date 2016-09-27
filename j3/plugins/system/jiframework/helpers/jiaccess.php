<?php
/**
 * @version     $Id: jiaccess.php 050 2014-12-08 11:05:00Z Anton Wintergerst $
 * @package     Jinfinity Rules Extended Field for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
defined('_JEXEC') or die;

class JiAccess extends JAccess {
    protected static $assetRules = array();

    protected static $userGroups = array();

    protected static $userGroupPaths = array();

    public static function checkGroup($groupId, $action, $asset = null)
    {
        // Sanitize inputs.
        $groupId = (int) $groupId;
        $action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
        $asset = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

        // Get group path for group
        $groupPath = self::getGroupPath($groupId);

        // Default to the root asset node.
        if(empty($asset)) $asset = 1;
        /*if (empty($asset))
        {
            $db = JFactory::getDbo();
            $assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
            $asset = $assets->getRootId();
        }*/

        // Get the rules for the asset recursively to root if not already retrieved.
        if (empty(self::$assetRules[$asset]))
        {
            self::$assetRules[$asset] = self::getAssetRules($asset, true);
        }

        return self::$assetRules[$asset]->allow($action, $groupPath);
    }

    public static function getAssetRules($data=array(), $recursive = false)
    {
        // convert nested object to nested associative array
        if(is_object($data)) {
            $newdata = array();
            foreach($data as $key=>$value) {
                $newdata[$key] = (array) $value;
            }
            $data = $newdata;
        }
        // Instantiate and return the JAccessRules object for the asset rules.
        $rules = new JAccessRules((array)$data);

        self::$assetRules[1] = $rules;

        return $rules;
    }

    public static function check($userId, $action, $asset = null)
    {
        // Sanitise inputs.
        $userId = (int) $userId;

        $action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
        $asset = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

        // Default to the root asset node.
        if(empty($asset)) $asset = 1;
        /*if (empty($asset))
        {
            $db = JFactory::getDbo();
            $assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
            $asset = $assets->getRootId();
        }*/

        // Get the rules for the asset recursively to root if not already retrieved.
        if (empty(self::$assetRules[$asset]))
        {
            self::$assetRules[$asset] = self::getAssetRules($asset, true);
        }

        // Get all groups against which the user is mapped.
        $identities = self::getGroupsByUser($userId);
        array_unshift($identities, $userId * -1);

        return self::$assetRules[$asset]->allow($action, $identities);
    }
}
class JiAccessUser extends JUser {
    public function authorise($action, $assetname = null)
    {
        // check even for super users
        return JiAccess::check($this->id, $action, $assetname);
    }
}