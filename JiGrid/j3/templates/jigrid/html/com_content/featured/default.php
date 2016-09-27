<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: default.php 022 2014-12-23 14:26:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space

// #Jinfinity
$JiLayoutTools = new JiGridLayoutTools();
$imagefilter = array('removeimages'=>1, 'maskclass'=>'jiimg', 'maskmode'=>'exclude', 'limit'=>1);
$imageparams = array('context'=>'grid', 'resize'=>1, 'width'=>200, 'height'=>200, 'fill'=>1, 'cache'=>1);
?>
<div class="jigrid blog-featured<?php echo $this->pageclass_sfx;?>" itemscope itemtype="http://schema.org/Blog">
    <?php if ($this->params->get('show_page_heading') != 0) : ?>
        <div class="page-header">
            <h1>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
    <?php endif; ?>

    <?php $leadingcount = 0; ?>
    <?php if (!empty($this->lead_items)) : ?>
        <div class="items-leading clearfix">
            <?php foreach ($this->lead_items as &$item) : ?>
                <?php
                // #Jinfinity
                $item->layoutTools = $JiLayoutTools;

                $hasimage = '';
                $item->image = false;

                // article overrides
                $skip = false;
                $regex = "#{nothumbs(.*?)}#s";
                preg_match($regex, $item->text, $match);
                if(isset($match[0])) {
                    $replacement = '';
                    $item->introtext = preg_replace($regex, '', $item->introtext);
                    $skip = strstr($match[0], 'category')==false;
                }

                if(!$skip) {
                    // find all the images in the introtext
                    $JiLayoutTools->getImages($item, 'introtext', $imagefilter);

                    if(count($item->jiimages)>0) {
                        // only display the first image
                        $item->image = $item->jiimages[0];
                        // create a thumbnail
                        $item->thumbnail = $JiLayoutTools->createThumbnail($item->image->path, $imageparams);
                        $hasimage = 'hasimage';
                    } else {
                        $hasimage = 'noimage';
                    }
                } ?>
                <div class="grid-item featured-item <?php echo $hasimage; ?> leading leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?> clearfix"
                     itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
                    <?php
                    $this->item = &$item;
                    echo $this->loadTemplate('item');
                    ?>
                </div>
                <?php
                $leadingcount++;
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
    $introcount = (count($this->intro_items));
    $counter = 0;
    // #Jinfinity
    $span = round(12/$this->columns);
    ?>
    <?php if (!empty($this->intro_items)) : ?>
        <?php foreach ($this->intro_items as $key => &$item) : ?>
            <?php
            // #Jinfinity
            $item->layoutTools = $JiLayoutTools;

            $hasimage = '';
            $item->image = false;

            // article overrides
            $skip = false;
            $regex = "#{nothumbs(.*?)}#s";
            preg_match($regex, $item->text, $match);
            if(isset($match[0])) {
                $replacement = '';
                $item->introtext = preg_replace($regex, '', $item->introtext);
                $skip = strstr($match[0], 'category')==false;
            }

            if(!$skip) {
                // find all the images in the introtext
                $JiLayoutTools->getImages($item, 'introtext', $imagefilter);

                if(count($item->jiimages)>0) {
                    // only display the first image
                    $item->image = $item->jiimages[0];
                    // create a thumbnail
                    $item->thumbnail = $JiLayoutTools->createThumbnail($item->image->path, $imageparams);
                    $hasimage = 'hasimage';
                } else {
                    $hasimage = 'noimage';
                }
            } ?>
            <?php
            $key = ($key - $leadingcount) + 1;
            $rowcount = (((int) $key - 1) % (int) $this->columns) + 1;
            $row = $counter / $this->columns;

            if ($rowcount == 1) : ?>
                <?php // #Jinfinity ?>
                <div class="jirow items-row cols-12 colsphone-<?php echo $span.' row-'.$row; ?> clearfix"><div class="outer outerwrap">
            <?php endif; ?>
            <?php // #Jinfinity ?>
            <div class="grid-item featured-item <?php echo $hasimage; ?> jicell span-<?php echo $span; ?>">
                <div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>"
                     itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
                    <?php
                    $this->item = &$item;
                    echo $this->loadTemplate('item');
                    ?>
                </div>
                <?php $counter++; ?>
            </div>
            <?php if (($rowcount == $this->columns) or ($counter == $introcount)) : ?>
                <?php // #Jinfinity ?>
                </div></div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($this->link_items)) : ?>
        <div class="items-more">
            <?php echo $this->loadTemplate('links'); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->pagesTotal > 1)) : ?>
        <div class="pagination">

            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter pull-right">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php  endif; ?>
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>

</div>