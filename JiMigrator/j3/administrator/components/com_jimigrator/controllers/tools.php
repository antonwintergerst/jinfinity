<?php
/**
 * @version     $Id: tools.php 048 2013-06-28 12:49:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 3.0+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerTools extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
    }
    function menuresolver()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->menuResolver();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Menu Extensions Resolved!');
    }
    function menuparams()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->menuParams();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Menu Params Rebuilt!');
    }
    function menuimages()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->menuImages();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Menu Images Resolved!');
    }
    function moduleparams()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->moduleParams();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Module Params Rebuilt!');
    }
    function contentaliases()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->contentAliases();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Content Aliases Repaired!');
    }
    function contentassetids()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->contentAssetIds();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Content Asset IDs Rebuilt!');
    }
    function contentattribs()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->contentAttribs();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link, 'Content Aliases Repaired!');
    }
    function contentadopt()
    {
        if(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->contentAdopt();

        $link = 'index.php?option=com_jimigrator&view=tools';
        //$this->setRedirect($link, 'Orphaned Content Adopted!');
    }
    function clearall()
    {
        if(version_compare(JVERSION,'3.0','ge')) {
            $model = $this->getModel('j30tools');
        } elseif(version_compare(JVERSION,'1.6.0','ge')) {
            $model = $this->getModel('j25tools');
        } else {
            $model = $this->getModel('j15tools');
        }
        $response = $model->clearAll();

        $link = 'index.php?option=com_jimigrator&view=tools';
        $this->setRedirect($link);
    }
}