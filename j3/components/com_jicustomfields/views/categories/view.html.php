<?php 
// Code marked with #Jinfinity author/copyright
/*
 * @version     $Id: view.html.php 015 2014-10-27 11:40:00Z Anton Wintergerst $
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
 * Content categories view.
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
#Jinfinity
class JiCustomFieldsViewCategories extends JViewCategories
{
    /**
     * Language key for default page heading
     *
     * @var    string
     * @since  3.2
     */
    protected $pageHeading = 'JGLOBAL_ARTICLES';

    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_content';
}