<?php
/**
 * @version     $Id: default_batch.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$published = $this->state->get('filter.state');
?>
<div class="modal hide fade" id="collapseModal">
    <div class="modal-header">
        <button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
        <h3><?php echo JText::_('COM_JIGRID_BATCH_OPTIONS');?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo JText::_('COM_JIGRID_BATCH_TIP'); ?></p>
        <?php if ($published >= 0) : ?>
            <div class="control-group">
                <div class="controls">
                    <?php echo JHtml::_('griditembatch.item', 'com_jigrid');?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button class="btn" type="button" onclick="document.id('batch-context').value='';" data-dismiss="modal">
            <?php echo JText::_('JCANCEL'); ?>
        </button>
        <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('griditem.batch');">
            <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
        </button>
    </div>
</div>
