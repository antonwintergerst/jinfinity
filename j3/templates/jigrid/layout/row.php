<?php
/**
 * @version     $Id: row.php 042 2014-10-30 19:34:00Z Anton Wintergerst $
 * @package     JiGrid Template for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$cells = $row->get('children');
if($cells!=null && ($totalcells = count($cells))>0):
    $GridHelper->setState('row.start', $totalcells);
    ?>
    <div id="<?php echo $row->get('alias'); ?>" class="<?php echo $row->getClass(); ?>">
        <div class="rowouter outer level<?php echo $row->get('level'); ?><?php echo $row->getOuterClass(); ?>"<?php echo $row->getOuterStyle(); ?>>
            <?php foreach($cells as $cell): ?>
                <?php include($GridHelper->loadLayout($cell->get('alias'), 'cell')); ?>
            <?php endforeach; ?>
            <div class="clear"></div>
        </div>
    </div>
    <?php $GridHelper->setState('row.end'); ?>
<?php endif; ?>