<?php
/**
 * @version     $Id: grid.php 036 2014-03-17 13:09:00Z Anton Wintergerst $
 * @package     JiGrid Template for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$rows = $grid->get('children');
if($rows!=null && ($totalrows = count($rows))>0):
    $GridHelper->setState('grid.start', $totalrows); ?>
    <div class="<?php echo $grid->getClass(); ?>"<?php echo $grid->getStyle(); ?>>
        <?php foreach($rows as $row): ?>
            <?php include($GridHelper->loadLayout($row->get('alias'), 'row')); ?>
        <?php endforeach; ?>
        <div class="clear"></div>
    </div>
    <?php $GridHelper->setState('grid.end'); ?>
<?php endif; ?>