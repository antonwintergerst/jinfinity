<?php
/**
 * @version     $Id: default.php 036 2014-10-26 14:53:00Z Anton Wintergerst $
 * @package     JiCustomFields Articles Module for Joomla 3.0+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/dates.php');

$user = JFactory::getUser();
// Preserve itemid
$Itemid = ((int) $params->get('source_itemid')!=0)? '&Itemid='.JRequest::getVar('Itemid'):'';

$source_category = (int) $params->get('source_category');
?>
<div class="modjicustomfields articles<?php if($params->get('moduleclass_sfx')!=null) echo ' '.$params->get('moduleclass_sfx'); ?>">
    <?php if($items!=null): ?>
        <?php foreach($items as $item): ?>
            <div class="article_row<?php echo $params->get('moduleclass_sfx'); ?>">
                <?php
                // Merge Module Params with article params
                $params->merge($item->params);
                // Add router helpers.
                $item->slug         = $item->alias ? ($item->id.':'.$item->alias) : $item->id;
                $item->catslug      = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
                $item->parent_slug  = $item->category_alias ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;
                $item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
                if($params->get('feed_summary')) {
                    $item->text = $item->introtext.$item->fulltext;
                } else {
                    $item->text = $item->introtext;
                }
                ob_start();
                require(JModuleHelper::getLayoutPath('mod_jicustomfields_articles', 'item'));
                echo ob_get_clean(); ?>
                <span class="row_separator<?php echo $params->get('moduleclass_sfx'); ?>">&nbsp;</span>
            </div>
        <?php endforeach; ?>
        <?php if($params->get('show_more', 1)==1 && $params->get('show_more_link')!=null): ?>
            <div class="showmore">
                <a class="showmorelink" href="<?php echo $params->get('show_more_link'); ?>" title="<?php echo $params->get('show_more_title', 'View more articles like this'); ?>"><span><?php echo $params->get('show_more_title', 'View more articles like this'); ?></span></a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>