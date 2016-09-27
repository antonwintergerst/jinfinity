<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: categories.php 012 2014-10-27 18:53:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of article categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.6
 */
require_once(JPATH_SITE.'/components/com_content/models/categories.php');
class JinfinityModelCategories extends ContentModelCategories
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        $this->setState('filter.extension', $this->_extension);

        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);

        // #Jinfinity
        $params = $app->getParams('com_content');
        $this->setState('params', $params);

        $this->setState('filter.published',	1);
        $this->setState('filter.access',	true);
    }
}
