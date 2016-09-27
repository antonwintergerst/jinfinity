<?php 
/**
 * @version     $Id: script.jimigrator.php 044 2014-12-19 13:02:00Z Anton Wintergerst $
 * @package     JiMigrator for Joomla 2.5-3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class Com_JiMigratorInstallerScript
{
    public function postflight($type, $parent)
    {
        $language = JFactory::getLanguage();
        $language->load('com_jimigrator');

        // remove front-end parts
        jimport('joomla.filesystem.folder');
        $src = JPATH_SITE.'/components/com_jimigrator';
        if(JFolder::exists($src)) JFolder::delete($src);
        
        $this->installationResults();
    }
    private function installationResults()
    {
        ?>
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
    <?php
    }
}