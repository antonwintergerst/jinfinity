<?php
/**
 * @version     $Id: install.jimigrator.php 043 2014-12-16 08:06:00Z Anton Wintergerst $
 * @package     JiMigrator for Joomla 1.5 only
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
$lang = JFactory::getLanguage();
$lang->load('com_jimigrator');

// Remove front-end parts
jimport('joomla.filesystem.folder');
if(JFolder::exists(JPATH_SITE.'/components/com_jimigrator')) rmdir(JPATH_SITE.'/components/com_jimigrator');
?>
<link href="<?php echo JURI::root(true).'/media/jimigrator/css/jimigrator.css'; ?>" rel="stylesheet" media="screen">
<div class="jiadmin jiinstaller jimigrator">
    <h2 class="subtitle"><?php echo JText::_('JIMIGRATOR_CONTENT_MIGRATION'); ?></h2>
    <div class="items actions">
        <div class="item">
            <div class="item-text">
                <h2 class="item-title">
                    <a class="btn btn-large btn-outline" href="index.php?option=com_jimigrator&view=import">
                        <span><?php echo JText::_('JIMIGRATOR_IMPORT'); ?></span>
                    </a>
                </h2>
                <p><?php echo JText::_('JIMIGRATOR_IMPORT_DESC'); ?></p>
            </div>
        </div>
        <div class="item">
            <div class="item-text">
                <h2 class="item-title">
                    <a class="btn btn-large btn-outline" href="index.php?option=com_jimigrator&view=export">
                        <span><?php echo JText::_('JIMIGRATOR_EXPORT'); ?></span>
                    </a>
                </h2>
                <p><?php echo JText::_('JIMIGRATOR_EXPORT_DESC'); ?></p>
            </div>
        </div>
    </div>
</div>