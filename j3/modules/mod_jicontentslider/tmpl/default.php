<?php
/**
 * @version     $Id: default.php 176 2014-10-30 19:31:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
$sliderid = 'slider'.$params->get('uniqueclass', 'jis');
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.jislider.container<?php echo $params->get('uniqueclass', 'jis'); ?>').jislider(<?php echo json_encode($data->jsparams); ?>);
        });
    }
</script>
<div class="jislider jislider-module<?php echo $moduleclass_sfx; ?> container<?php echo $params->get('uniqueclass', 'jis').' '.$sliderid.' ji'.$params->get('transition', 'slideleft').' '.$params->get('caption_style', 'overlay'); ?>">
    <?php $i = 1; ?>
    <?php foreach($data->items as $item): ?>
        <?php
            // Build link attributes
            $linkattribs = '';
            if(isset($item->linkattribs) && is_array($item->linkattribs)) {
                foreach($item->linkattribs as $attrib=>$value) {
                    $linkattribs.= $attrib.'="'.$value.'" ';
                }
            }
            // Build img attributes
            $imgs = array();
            foreach($item->images as $img) {
                $imgattribs = '';
                if(isset($img->imageattribs) && is_array($img->imageattribs)) {
                    foreach($img->imageattribs as $attrib=>$value) {
                        $imgattribs.= $attrib.'="'.$value.'" ';
                    }
                }
                $imgs[] = $imgattribs;
            }
        ?>
        <div class="slide slide<?php echo $i; ?><?php if(isset($item->alias)) echo ' '.$item->alias; ?>">
            <?php if(isset($item->link) && $params->get('links', 1)==1): ?>
                <?php // Image with link ?>
                <?php foreach($imgs as $k=>$img): ?>
                    <a class="imagelink img<?php echo ($k+1); ?>"<?php echo $linkattribs; ?>>
                        <img <?php echo $img; ?>/>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php // Image ?>
                <?php foreach($imgs as $img): ?>
                    <img <?php echo $img; ?>/>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if((isset($item->caption) || $params->get('readmores', 1)==1) && $params->get('captions', 1)==1): ?>
                <div class="jicaption <?php echo $params->get('caption_style', 'overlay'); ?>">
                    <?php if(isset($item->title) && $params->get('titles', 1)==1): ?>
                        <<?php echo $params->get('title_tag', 'h4'); ?> class="title">
                            <?php if($params->get('links', 1)==1): ?>
                                <a <?php echo $linkattribs; ?>>
                                    <?php echo $item->title; ?>
                                </a>
                            <?php else: ?>
                                <?php echo $item->title; ?>
                            <?php endif; ?>
                        </<?php echo $params->get('title_tag', 'h4'); ?>>
                    <?php endif; ?>
                    <?php if(isset($item->caption)): ?>
                        <div class="text">
                            <?php if($params->get('caption_striptags', 1)!=1): ?>
                                <?php echo $item->caption; ?>
                            <?php else: ?>
                                <?php if(isset($item->link) && $params->get('caption_striptags', 1)==1): ?>
                                    <?php // Caption with link ?>
                                    <a <?php echo $linkattribs; ?>>
                                        <span><?php echo $item->caption; ?></span>
                                    </a>
                                <?php else: ?>
                                    <?php // Caption ?>
                                    <?php if($params->get('caption_striptags', 1)==1): ?>
                                        <span><?php echo $item->caption; ?></span>
                                    <?php else: ?>
                                        <?php echo $item->caption; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if($params->get('readmores', 1)==1 && isset($item->link) && $params->get('links', 1)==1): ?>
                        <?php // Readmore ?>
                        <a <?php echo $linkattribs; ?>>
                            <span><?php echo $params->get('readmore_text', 'Read More...'); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php $i++; ?>
    <?php endforeach; ?>
    
    <?php if($params->get('discs', 1)==1): ?>
        <div class="discs">
            <?php $i = 1; $k = 1; $p = 1; ?>
            <?php foreach($data->items as $item):?>
                <?php $i++; $k++; ?>
                <?php if($k>$params->get('numberslides', 1) || $i==$data->total): ?>
                    <a href="#" class="jislidergotobtn <?php echo $sliderid; ?> icon" rel="<?php echo $p; ?>" title="GoTo Slide <?php echo $p; ?>">
                        <span><?php echo $p; ?></span>
                    </a>
                    <?php $k = 1; $p++; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if($params->get('paddles', 1)==1): ?>
        <div class="prevnav nav">
            <div class="paddelbox paddel">
                <a href="#" class="jisliderprevbtn paddelbtn <?php echo $sliderid; ?>">Prev</a>
            </div>
        </div>
        <div class="nextnav nav">
            <div class="paddelbox paddel">
                <a href="#" class="jislidernextbtn paddelbtn <?php echo $sliderid; ?>">Next</a>
            </div>
        </div>
    <?php endif; ?>
</div>