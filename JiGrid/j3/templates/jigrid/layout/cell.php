<?php
/**
 * @version     $Id: cell.php 041 2014-03-17 13:09:00Z Anton Wintergerst $
 * @package     JiGrid Template for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$gridparams = JComponentHelper::getParams('com_jigrid');
$GridHelper->setState('cell.start');
?>
<?php if(is_a($cell, 'JiGrid')): ?>
    <?php $grid = &$cell; ?>
    <?php include($GridHelper->loadLayout($grid->get('alias'), 'grid')); ?>
<?php else: ?>
    <div class="<?php echo $cell->getClass(); ?>"<?php echo $cell->getStyle(); ?>>
        <div class="outer">
            <?php if($cell->get('message')): ?>
                <div class="inner innermessage">
                    <jdoc:include type="message" />
                </div>
            <?php endif; ?>
            <?php if($cell->get('component')): ?>
                <div class="inner innercomponent">
                    <jdoc:include type="component" />
                </div>
            <?php endif; ?>
            <?php if($cell->get('position')!=null && (!($gridparams->get('hideempty', 0)==1 && countModules($cell->get('position'))==0)) || $gridparams->get('showmodulepositions', 1)==1): ?>
                <?php if($gridparams->get('showmodulepositions', 1)==1): ?>
                    <span class="modulepositionlabel label label-info"><?php echo $cell->get('position'); ?></span>
                <?php endif; ?>
                <div class="inner innermodule inner<?php echo $cell->get('position'); ?>">
                    <jdoc:include type="modules" name="<?php echo $cell->get('position'); ?>" style="jigrid" />
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php $GridHelper->setState('cell.end'); ?>