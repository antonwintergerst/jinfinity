<?php
/**
 * @version     $Id: snapshots.php 036 2013-07-31 19:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiMigratorControllerSnapshots extends JiMigratorController
{
    function __construct()
    {
        parent::__construct();
    }
    function show() {
        // Get the Application Object.
        $app = JFactory::getApplication();
        // Set Page Header
        header('Content-Type: text/plain;charset=UTF-8');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        echo $model->getSnapshot();
        // Close the Application.
        $app->close();
    }
    function snapshot() {
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        echo $model->saveSnapshot();
    }
    function dosnapshot() {
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        echo $model->dosnapshot();
    }
    function restore() {
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        echo $model->restoreSnapshot();
    }
    function dorestore() {
        // Get Data
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Snapshots', 'JiMigratorModel');
        } else {
            $model = JModel::getInstance('Snapshots', 'JiMigratorModel');
        }
        echo $model->dorestore();
    }
}