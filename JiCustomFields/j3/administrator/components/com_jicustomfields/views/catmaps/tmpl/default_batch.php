<?php
/**
 * @version     $Id: default_batch.php 001 2014-05-12 09:20:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
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
        <h3><?php echo JText::_('JICUSTOMFIELDS_BATCH_OPTIONS');?></h3>
    </div>
    <div class="modal-body">
        <p><?php echo JText::_('JICUSTOMFIELDS_BATCH_TIP'); ?></p>
        <?php if ($published >= 0) : ?>
            <div class="control-group">
                <div class="controls">
                    <?php echo JHtml::_('catmapbatch.item', 'com_jicustomfields');?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button class="btn" type="button" onclick="document.id('batch-context').value='';" data-dismiss="modal">
            <?php echo JText::_('JCANCEL'); ?>
        </button>
        <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('field.batch');">
            <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
        </button>
    </div>
</div>