<?php 
/**
 * @version     $Id: default.php 061 2014-12-15 10:09:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="jiadmin jimigrator">
    <?php /* >>> FREE >>> */ ?>
    <div class="processors">
        <div class="general">
            <?php $lang = JFactory::getLanguage();
            $lang->load('plg_system_jiframework', JPATH_ADMINISTRATOR, null, false, true); ?>
            <?php echo sprintf(JText::_('JI_PRO_UPGRADE'), JText::_('JiMigrator')); ?>
            <h2><?php echo JText::_('JI_PRO_FEATURES_TITLE'); ?></h2>
            <?php echo JText::_('JIMIGRATOR_PRO_FEATURES'); ?>
        </div>
    </div>
    <?php /* <<< FREE <<< */ ?>
    <div class="items actions">
        <div class="item">
            <span class="item-image"><img src="<?php echo JURI::root(); ?>media/jimigrator/images/jimigrator-importicon.png" /></span>
            <div class="item-text">
                <a class="btn btn-large btn-outline item-title" href="index.php?option=com_jimigrator&view=import">
                    <span><?php echo JText::_('JIMIGRATOR_IMPORT'); ?></span>
                </a>
                <p><?php echo JText::_('JIMIGRATOR_IMPORT_DESC'); ?></p>
            </div>
        </div>
        <div class="item">
            <span class="item-image"><img src="<?php echo JURI::root(); ?>media/jimigrator/images/jimigrator-exporticon.png" /></span>
            <div class="item-text">
                <a class="btn btn-large btn-outline item-title" href="index.php?option=com_jimigrator&view=export">
                    <span><?php echo JText::_('JIMIGRATOR_EXPORT'); ?></span>
                </a>
                <p><?php echo JText::_('JIMIGRATOR_EXPORT_DESC'); ?></p>
            </div>
        </div>
    </div>
    <?php echo sprintf(JText::_('JIMIGRATOR_FOOTER_TEXT'), JVERSION, date('Y')); ?>
</div>