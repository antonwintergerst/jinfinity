<?php 
/*
 * @version     $Id: categories.php 050 2013-03-04 17:39:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.0 Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsModelCategories extends JModelLegacy
{
    public function getCategories()
    {
        // Load Database Object
        $db = JFactory::getDBO();
        // Query Statement
        $query = 'SELECT id, title FROM `#__categories`';
        $query.= ' WHERE `published`=1 AND `extension`="com_content"';
        $query.= ' ORDER BY `title` ASC';
        // Set Query
        $db->setQuery( $query );
        // Load Data
        $categories = $db->loadObjectList();
        
        return $categories;
    }
}