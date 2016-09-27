<?php
/**
 * @version     $Id: selectmenu.php 032 2013-07-18 16:10:00Z Anton Wintergerst $
 * @package     JiGrid Module for Joomla
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if($params->get('load_jquery', true)) JHtml::_('jquery.framework');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('modules/mod_jigrid/assets/css/jiselectmenu.css');
    JHTML::script('modules/mod_jigrid/assets/js/jquery.jiselectmenu.js');
} else {
    JHTML::_('stylesheet', 'jiselectmenu.css', 'modules/mod_jigrid/assets/css/');
    JHTML::_('script', 'jquery.jiselectmenu.js', 'modules/mod_jigrid/assets/js/');
}
$urls = array();
$alink = 0;
foreach ($data->list as $i => &$item) {
    $urls[$i] = $item->flink;
}
$urls = json_encode($urls);
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.jiselectmenu').jiselectmenu({'urls':jQuery.parseJSON('<?php echo $urls; ?>')});
        });
    }
</script>
<div class="jitogglemenubar <?php echo 'has'.$params->get('btntype', 'text'); ?>">
    <a class="jitogglemenubtn btn btn-navbar" href="#">
        <?php if($params->get('btntype', 'text')=='text' || $params->get('btntype', 'text')=='texticon'): ?>
            <span class="btntext largetext"><?php echo $params->get('btntext', 'Menu'); ?></span>
        <?php endif; ?>
        <?php if($params->get('btntype', 'text')=='icon' || $params->get('btntype', 'text')=='texticon'): ?>
            <span class="btnicon">
                <?php if($params->get('btnicon', 'icon-bar')=='icon-bar'): ?>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </a>
    <div class="jitoggleheader largetext">
        <?php
        $document = JFactory::getDocument();
        $renderer = $document->loadRenderer('modules');
        echo $renderer->render($params->get('headerposition', 'toggleheader'), array('style' =>'raw'), null);
        ?>
    </div>
</div>
<label class="jiselectmenulabel"></label>
<select class="nav jiselectmenu hideselect <?php echo $class_sfx;?>"<?php
$tag = '';
if ($params->get('tag_id') != null)
{
    $tag = $params->get('tag_id').'';
    echo ' id="'.$tag.'"';
}
?>>
<?php
foreach ($data->list as $i => &$item) :
    $class = 'item-'.$item->id;
    if ($item->id == $data->active_id) {
        $class .= ' current';
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }

    if (in_array($item->id, $data->path)) {
        $class .= ' active';
    }
    elseif ($item->type == 'alias') {
        $aliasToId = $item->params->get('aliasoptions');
        if (count($data->path) > 0 && $aliasToId == $data->path[count($data->path) - 1]) {
            $class .= ' active';
        }
        elseif (in_array($aliasToId, $data->path)) {
            $class .= ' alias-parent-active';
        }
    }

    if ($item->type == 'separator') {
        $class .= ' divider';
    }

    if ($item->deeper) {
        $class .= ' deeper';
    }

    if ($item->parent) {
        $class .= ' parent';
    }

    if (!empty($class)) {
        $class = ' class="'.trim($class) .'"';
    }


    // Render the menu item.
    echo '<option value="'.$i.'"'.$selected.'>'.$item->title.'</option>';

    // The next item is deeper.
    if ($item->deeper) {
    }
    // The next item is shallower.
    elseif ($item->shallower) {
    }
    // The next item is on the same level.
    else {
    }
endforeach;
?></select>