<?php 
/**
 * @version     $Id: default.php 048 2014-12-15 10:24:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="jimigrator tools">
    <?php echo JText::_('JIMIGRATOR_TOOLS_WARNING'); ?>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=contentaliases">Repair Content Aliases</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=contentassetids">Rebuild Content Asset IDs</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=contentattribs">Reset Content Attribs</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=contentadopt">Adopt Content Orphans</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=menuresolver">Resolve Menu Items</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=menuparams">Rebuild Menu Params</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=menuimages">Resolve Menu Images</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=moduleparams">Rebuild Module Params</a></h2>
    <h2><a href="index.php?option=com_jimigrator&view=tools&task=clearall" onclick="return confirm('WARNING! Use caution with this tool. \n\nContinuing will attempt to erase all content and return to factory defaults.\n\nThere is NO UNDO!')">Reset Content</a></h2>
    <?php echo sprintf(JText::_('JIMIGRATOR_FOOTER_TEXT'), JVERSION, date('Y')); ?>
</div>