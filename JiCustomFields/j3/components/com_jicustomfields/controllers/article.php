<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: article.php 010 2013-01-29 20:07:00Z Anton Wintergerst $
 * @package     Jinfinity Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die;

// #Jinfinity - Run this controller instead of the standard ContentControllerArticle
require_once(JPATH_SITE.'/components/com_content/controllers/article.php');
class JinfinityControllerArticle extends ContentControllerArticle
{
    // This class is intentionally blank
}