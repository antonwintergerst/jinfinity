<?php
/*
 * @version     $Id: default.php 100 2013-05-24 16:00:00Z Anton Wintergerst $
 * @package     Jinfinity Disqus API Module
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       antonwintergerst@gmail.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
//print_r($data);
?>
<?php if($data!=null): ?>
    <ul class="<?php echo $params->get('moduleclass_sfx', ''); ?>">
    <?php foreach($data as $item): ?>
        <?php $item->introtext = $helper->truncate(strip_tags($item->introtext), $params->get('textlength', 100)); ?>
        <?php if($item->title!=null): ?>
            <li>
                <h4>
                    <a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
                </h4>
                <?php if($params->get('showdate', 1)==1): ?>
                    <span class="mod-articles-category-date">Published: <?php echo date('d F Y', strtotime($item->publish_up)); ?></span>
                <?php endif; ?>
                <?php if($params->get('showtext', 1)==1): ?>
                    <p class="mod-articles-category-introtext"><?php echo $item->introtext; ?></p>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>