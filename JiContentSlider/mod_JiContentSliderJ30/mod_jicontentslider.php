<?php
/**
 * @version     $Id: mod_jicontentslider.php 138 2014-10-30 19:31:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
jimport('joomla.filesystem.folder');

// Set Source
switch($params->get('sourcetype', 'category')) {
    case 'article':
        $params->set('source', $params->get('sourcearticle'));
        break;
    case 'category':
        $params->set('source', $params->get('sourcecategory'));
        break;
    case 'directory':
        $params->set('source', $params->get('sourcedirectory'));
    break;
    case 'xml':
        $params->set('source', $params->get('sourcexml'));
    break;
}

JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');

// Load Data
require_once dirname(__FILE__).'/helper.php';
$helper = new JiContentSliderHelper();
$data = $helper->getData($params);

if($params->get('load_jquery', true)) JHtml::_('jquery.framework');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('media/jicontentslider/css/jislider.css');
    JHTML::script('media/jicontentslider/js/jquery.touchswipe.min.js');
    JHTML::script('media/jicontentslider/js/jquery.jislider.js');
} else {
    JHTML::_('stylesheet', 'jislider.css', 'media/jicontentslider/css/');
    JHTML::_('script', 'jquery.touchswipe.min.js', 'media/jicontentslider/js/');
    JHTML::_('script', 'jquery.jislider.js', 'media/jicontentslider/js/');
}
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
if($data->total>0) require(JModuleHelper::getLayoutPath('mod_jicontentslider', $params->get('layout', 'default')));