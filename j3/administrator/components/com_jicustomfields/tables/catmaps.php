<?php
/**
 * @version     $Id: catmaps.php 001 2014-05-12 09:20:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsTableCatMaps extends JTable
{
    var $id = null;

    var $title = null;

    function __construct(&$db)
    {
        parent::__construct('#__jifields_map', 'id', $db);
    }

    public function check()
    {
        return true;
    }

    public function store($updateNulls = false)
    {
        $result = parent::store($updateNulls);

        return $result;
    }
}