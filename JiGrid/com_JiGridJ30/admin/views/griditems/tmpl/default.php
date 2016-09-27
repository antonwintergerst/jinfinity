<?php
/**
 * @version     $Id: default.php 025 2013-07-17 22:27:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;

$ordering 	= ($listOrder == 'lft');
$saveOrder 	= ($listOrder == 'lft' && $listDirn == 'asc');

$sortFields = $this->getSortFields();

// Load Scripts
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('administrator/components/com_jigrid/assets/css/jigrid.css');
    if($saveOrder) {
        $saveOrderingUrl = 'index.php?option=com_jigrid&task=griditems.saveOrderAjax&tmpl=component';
        JHtml::_('sortablelist.sortable', 'griditemList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
    }
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jigrid.css', 'administrator/components/com_jigrid/assets/css/');
}
?>
<div class="jinfinity jigrid<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' row-fluid'; ?>">
    <div class="griditems<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' span12'; ?>">
        <script type="text/javascript">
            Joomla.orderTable = function() {
                table = document.getElementById("sortTable");
                direction = document.getElementById("directionTable");
                order = table.options[table.selectedIndex].value;
                if (order != '<?php echo $listOrder; ?>') {
                    dirn = 'asc';
                } else {
                    dirn = direction.options[direction.selectedIndex].value;
                }
                Joomla.tableOrdering(order, dirn, '');
            }
        </script>
        <form action="<?php echo JRoute::_('index.php?option=com_jigrid&view=griditems');?>" method="post" name="adminForm" id="adminForm">
            <?php if(!empty( $this->sidebar)): ?>
                <div id="j-sidebar-container" class="span2">
                    <?php echo $this->sidebar; ?>
                </div>
                <div id="j-main-container" class="span10">
            <?php else : ?>
                <div id="j-main-container">
            <?php endif;?>
                <div id="filter-bar" class="btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JIGRID_FILTER_SEARCH');?></label>
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_JIGRID_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JIGRID_FILTER_SEARCH'); ?>" />
                    </div>
                    <div class="btn-group pull-left hidden-phone">
                        <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                    </div>
                    <div class="btn-group pull-right hidden-phone">
                        <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
                        <?php echo $this->pagination->getLimitBox(); ?>
                    </div>
                    <div class="btn-group pull-right hidden-phone">
                        <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
                        <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                            <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
                            <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
                            <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
                        </select>
                    </div>
                    <div class="btn-group pull-right">
                        <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
                        <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                            <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
                            <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
                        </select>
                    </div>
                </div>
                <div class="clearfix"> </div>
                <table class="table table-striped" id="griditemList">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                        </th>
                        <th width="1%" class="hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
                        </th>
                        <th>
                            <?php echo JHtml::_('grid.sort', 'COM_JIGRID_TITLE_LABEL', 'title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIGRID_ALIAS_LABEL', 'alias', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIGRID_TYPE_LABEL', 'type', $listDirn, $listOrder); ?>
                        </th>
                        <th width="40%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIGRID_ATTRIBS_LABEL', 'attribs', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $canCreate  = true;
                        $canEdit    = true;
                        $canCheckin = true;
                        $canEditOwn = true;
                        $canChange  = true;

                        $orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
                        // Get the parents of item for sorting
                        if ($item->level > 1)
                        {
                            $parentsStr = "";
                            $_currentParentId = $item->parent_id;
                            $parentsStr = " " . $_currentParentId;
                            for ($x = 0; $x < $item->level; $x++)
                            {
                                foreach ($this->ordering as $k => $v)
                                {
                                    $v = implode("-", $v);
                                    $v = "-".$v."-";
                                    if (strpos($v, "-" . $_currentParentId . "-") !== false)
                                    {
                                        $parentsStr .= " " . $k;
                                        $_currentParentId = $k;
                                        break;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $parentsStr = "";
                        }

                        $item->attribs = json_decode($item->attribs, true);
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id;?>" item-id="<?php echo $item->id?>" parents="<?php echo $parentsStr?>" level="<?php echo $item->level?>">
                            <td class="order nowrap center hidden-phone">
                                <?php if ($canChange) :
                                    $disableClassName = '';
                                    $disabledLabel    = '';
                                    if (!$saveOrder) :
                                        $disabledLabel    = JText::_('JORDERINGDISABLED');
                                        $disableClassName = 'inactive tip-top';
                                    endif; ?>
                                    <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
                                        <i class="icon-menu"></i>
                                    </span>
                                <?php else : ?>
                                    <span class="sortable-handler inactive">
                                        <i class="icon-menu"></i>
                                    </span>
                                <?php endif; ?>
                                <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" />
                            </td>
                            <td class="center hidden-phone">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'griditems.', $canChange, 'cb'); ?>
                                </div>
                            </td>
                            <td class="nowrap has-context">
                                <div class="pull-left">
                                    <?php for($i=0; $i<$item->level-1; $i++): ?>
                                        <span class="gi">|—</span>
                                    <?php endfor; ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_jigrid&task=griditem.edit&id=' . $item->id);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->title); ?></a>
                                </div>
                                <div class="pull-left">
                                    <?php
                                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                                        // Create dropdown items
                                        JHtml::_('dropdown.edit', $item->id, 'article.');
                                        JHtml::_('dropdown.divider');
                                        if ($item->state) :
                                            JHtml::_('dropdown.unpublish', 'cb' . $i, 'extensions.');
                                        else :
                                            JHtml::_('dropdown.publish', 'cb' . $i, 'extensions.');
                                        endif;

                                        JHtml::_('dropdown.divider');

                                        if ($archived) :
                                            JHtml::_('dropdown.unarchive', 'cb' . $i, 'extensions.');
                                        else :
                                            JHtml::_('dropdown.archive', 'cb' . $i, 'extensions.');
                                        endif;

                                        if ($trashed) :
                                            JHtml::_('dropdown.untrash', 'cb' . $i, 'extensions.');
                                        else :
                                            JHtml::_('dropdown.trash', 'cb' . $i, 'extensions.');
                                        endif;

                                        // Render dropdown list
                                        echo JHtml::_('dropdown.render');
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="center small hidden-phone">
                                <?php echo $this->escape($item->alias); ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->type; ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php
                                $attribs = array();
                                if($item->type=='grid') {

                                } elseif($item->type=='row') {
                                    if(isset($item->attribs['cols']) && $item->attribs['cols']!='') $attribs[] = 'Cols: '.$item->attribs['cols'];
                                    if(isset($item->attribs['cols-phone']) && $item->attribs['cols-phone']!='') $attribs[] = 'Cols-Phone: '.$item->attribs['cols-phone'];
                                } elseif($item->type=='cell') {
                                    if(isset($item->attribs['span']) && $item->attribs['span']!='') $attribs[] = 'Span: '.$item->attribs['span'];
                                    if(isset($item->attribs['ypercent']) && $item->attribs['ypercent']!='') $attribs[] = 'Y Precent: '.$item->attribs['ypercent'];
                                    if(isset($item->attribs['position']) && $item->attribs['position']!='') $attribs[] = 'Module Pos: '.$item->attribs['position'];
                                }
                                echo implode(', ', $attribs);
                                ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php echo $this->pagination->getListFooter(); ?>
                <?php //Load the batch processing form. ?>
                <?php echo $this->loadTemplate('batch'); ?>

                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
                <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
                <?php echo JHtml::_('form.token'); ?>
            </div>
        </form>
    </div>
    <div class="footer">
        <span class="browsers">Supported Browsers: Internet Explorer 7 +, Mozilla Firefox 3 +, Google Chrome 10 +, Safari 5 +, Safari iOS 4 +, Opera 10 +</span>
        <span class="support">This component has been thoroughly tested to ensure optimal stability. If you do find a glitch, please report it to support@jinfinity.com</span>
        <span class="copyright">Jinfinity Grid Template Framework for Joomla 2.5-3.0 Copyright (C) 2013 Jinfinity. http://www.jinfinity.com</span>
    </div>
</div>