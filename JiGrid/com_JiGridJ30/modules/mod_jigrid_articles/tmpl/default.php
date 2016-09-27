<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="category-module<?php echo $moduleclass_sfx; ?>">
    <?php if ($grouped) : ?>
        <?php foreach ($list as $group_name => $group) : ?>
            <li>
                <ul>
                    <?php foreach ($group as $item) : ?>
                        <li>
                            <?php if ($params->get('link_titles') == 1) : ?>
                                <a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            <?php else : ?>
                                <?php echo $item->title; ?>
                            <?php endif; ?>

                            <?php if ($item->displayHits) : ?>
                                <span class="mod-articles-category-hits">
								(<?php echo $item->displayHits; ?>)
							</span>
                            <?php endif; ?>

                            <?php if ($params->get('show_author')) : ?>
                                <span class="mod-articles-category-writtenby">
								<?php echo $item->displayAuthorName; ?>
							</span>
                            <?php endif;?>

                            <?php if ($item->displayCategoryTitle) : ?>
                                <span class="mod-articles-category-category">
								(<?php echo $item->displayCategoryTitle; ?>)
							</span>
                            <?php endif; ?>

                            <?php if ($item->displayDate) : ?>
                                <span class="mod-articles-category-date"><?php echo $item->displayDate; ?></span>
                            <?php endif; ?>

                            <?php if ($params->get('show_introtext')) : ?>
                                <p class="mod-articles-category-introtext">
                                    <?php echo $item->displayIntrotext; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($params->get('show_readmore')) : ?>
                                <p class="mod-articles-category-readmore">
                                    <a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
                                        <?php if ($item->params->get('access-view') == false) : ?>
                                            <?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
                                        <?php elseif ($readmore = $item->alternative_readmore) : ?>
                                            <?php echo $readmore; ?>
                                            <?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                                            <?php if ($params->get('show_readmore_title', 0) != 0) : ?>
                                                <?php echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit')); ?>
                                            <?php endif; ?>
                                        <?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
                                            <?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
                                        <?php else : ?>
                                            <?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
                                            <?php echo JHtml::_('string.truncate', ($item->title), $params->get('readmore_limit')); ?>
                                        <?php endif; ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    <?php else : ?>
        <?php
        $listcount = (count($list));
    $columns = $params->get('columns', 3);
        $counter = 0;
        // #Jinfinity
        $span = round(12/$columns);
        ?>
        <?php foreach ($list as $key=>$item) : ?>
            <?php
            $key = ($key - $listcount) + 1;
            $rowcount = (((int) $key - 1) % (int) $columns) + 1;
            $row = $counter / $columns;

            if ($rowcount == 1) : ?>
                <div class="jirow items-row cols-12 colsphone-<?php echo $span.' row-'.$row; ?> clearfix"><div class="outer outerwrap">
            <?php endif; ?>
            <div class="jicell span-<?php echo $span; ?> grid-item module-item">
                <?php if($params->get('show_titles')): ?>
                    <?php if ($params->get('link_titles') == 1) : ?>
                        <a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
                            <?php echo $item->title; ?>
                        </a>
                    <?php else : ?>
                        <?php echo $item->title; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($item->displayHits) : ?>
                    <span class="mod-articles-category-hits">
						(<?php echo $item->displayHits; ?>)
					</span>
                <?php endif; ?>

                <?php if ($params->get('show_author')) : ?>
                    <span class="mod-articles-category-writtenby">
						<?php echo $item->displayAuthorName; ?>
					</span>
                <?php endif;?>

                <?php if ($item->displayCategoryTitle) : ?>
                    <span class="mod-articles-category-category">
						(<?php echo $item->displayCategoryTitle; ?>)
					</span>
                <?php endif; ?>

                <?php if ($item->displayDate) : ?>
                    <span class="mod-articles-category-date">
						<?php echo $item->displayDate; ?>
					</span>
                <?php endif; ?>

                <?php // #Jinfinity
                // find and remove the images in the text $item->introtext
                if ($params->get('show_image')) :
                    $JiLayoutTools->getImages($item, 'introtext'); ?>
                    <?php if(count($item->jiimages)>0): ?>
                        <?php // only show the first image
                        $image = $item->jiimages[0];
                        // create a thumbnail
                        $image->path = $JiLayoutTools->createThumbnail($image->path, array('context'=>'blog', 'resize'=>1, 'width'=>160, 'height'=>160, 'cache'=>1));
                        ?>
                        <?php if(isset($image->linkattribs) && count($image->linkattribs)>0):
                            // use original link attributes ?>
                            <a<?php echo $JiLayoutTools->getHTMLAttribs($image->linkattribs, array('href','class','title')); ?>>
                                <img src="<?php echo $image->path; ?>"<?php echo $JiLayoutTools->getHTMLAttribs($image->imageattribs, array('class')); ?>/>
                            </a>
                        <?php else:
                            // link thumbnail to article ?>
                            <a class="grid-image module-image" href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>">
                                <img src="<?php echo $image->path; ?>"<?php echo $JiLayoutTools->getHTMLAttribs($image->imageattribs, array('class')); ?>/>
                            </a>
                        <?php endif; ?>
                        <a class="grid-item-bg" style="background-image: url('<?php echo $image->path; ?>');" href="<?php echo $item->link; ?>">&nbsp;</a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($params->get('show_introtext')) : ?>
                    <p class="mod-articles-category-introtext">
                        <?php echo $item->displayIntrotext; ?>
                    </p>
                <?php endif; ?>

                <?php if ($params->get('show_readmore')) : ?>
                    <p class="mod-articles-category-readmore">
                        <a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
                            <?php if ($item->params->get('access-view') == false) : ?>
                                <?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
                            <?php elseif ($readmore = $item->alternative_readmore) : ?>
                                <?php echo $readmore; ?>
                                <?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                            <?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
                                <?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
                            <?php else : ?>
                                <?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
                                <?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
                            <?php endif; ?>
                        </a>
                    </p>
                <?php endif; ?>
                <?php $counter++; ?>
            </div>
            <?php if (($rowcount == $columns) or ($counter == $listcount)) : ?>
                <?php // #Jinfinity ?>
                </div></div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
