<?php
/**
 * @version     $Id: default.php 010 2014-12-19 09:13:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHtml::_('bootstrap.tooltip');
    JHtml::_('behavior.multiselect');
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');

    $user		= JFactory::getUser();
    $userId		= $user->get('id');
    $listOrder	= $this->escape($this->state->get('list.ordering'));
    $listDirn	= $this->escape($this->state->get('list.direction'));
    $archived	= $this->state->get('filter.published') == 2 ? true : false;
    $trashed	= $this->state->get('filter.published') == -2 ? true : false;
    $saveOrder	= $listOrder == 'ordering';
    if ($saveOrder)
    {
        $saveOrderingUrl = 'index.php?option=com_jicustomfields&task=catmaps.saveOrderAjax&tmpl=component';
        JHtml::_('sortablelist.sortable', 'itemList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
    }

    $sortFields = $this->getSortFields();
} else {
}
?>
<div class="jicustomfields catmaps">
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
    <form action="<?php echo JRoute::_('index.php?option=com_jicustomfields&view=catmaps');?>" method="post" name="adminForm" id="adminForm">
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
                        <label for="filter_search" class="element-invisible"><?php echo JText::_('JICUSTOMFIELDS_FILTER_SEARCH');?></label>
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JICUSTOMFIELDS_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JICUSTOMFIELDS_FILTER_SEARCH'); ?>" />
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

                <table class="table table-striped" id="itemList">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                        </th>
                        <th width="1%" class="hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th width="20%">
                            <?php echo JHtml::_('grid.sort', 'JICUSTOMFIELDS_CATMAP', 'cat_title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="20%">
                            <?php echo JHtml::_('grid.sort', 'JICUSTOMFIELDS_CATEGORY', 'cat_title', $listDirn, $listOrder); ?>
                        </th>
                        <th>
                            <?php echo JHtml::_('grid.sort', 'JICUSTOMFIELDS_FIELD', 'title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JICUSTOMFIELDS_FIELD_ID', 'fid', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $item->max_ordering = 0;
                        $ordering   = ($listOrder == 'ordering');
                        //$canCreate  = $user->authorise('core.create',     'com_content.category.'.$item->catid);
                        $canEdit    = $user->authorise('core.edit',       'com_content.article.'.$item->id);
                        $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        //$canEditOwn = $user->authorise('core.edit.own',   'com_content.article.'.$item->id) && $item->created_by == $userId;
                        $canChange  = $user->authorise('core.edit.state', 'com_content.article.'.$item->id) && $canCheckin;
                        if($item->fid==0) $item->title = JText::_('JICUSTOMFIELDS_ALL_FIELDS');
                        if($item->catid==0) $item->cat_title = JText::_('JICUSTOMFIELDS_ALL_CATEGORIES');
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">
                            <td class="order nowrap center hidden-phone">
                                <?php if ($canChange) :
                                    $disableClassName = '';
                                    $disabledLabel	  = '';

                                    if (!$saveOrder) :
                                        $disabledLabel    = JText::_('JORDERINGDISABLED');
                                        $disableClassName = 'inactive tip-top';
                                    endif; ?>
                                    <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
                            <i class="icon-menu"></i>
                        </span>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
                                <?php else : ?>
                                    <span class="sortable-handler inactive" >
                            <i class="icon-menu"></i>
                        </span>
                                <?php endif; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="nowrap has-context">
                                <div class="pull-left">
                                    <a href="<?php echo JRoute::_('index.php?option=com_jicustomfields&task=catmap.edit&id=' . $item->id);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->cat_title).' <=> '.$this->escape($item->title); ?></a>
                                </div>
                                <div class="pull-left">
                                    <?php
                                    // Create dropdown items
                                    JHtml::_('dropdown.edit', $item->id, 'catmap.');

                                    // Render dropdown list
                                    echo JHtml::_('dropdown.render');
                                    ?>
                                </div>
                            </td>
                            <td class="nowrap">
                                <a href="<?php echo JRoute::_('index.php?option=com_categories&task=category.edit&id=' . $item->catid);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->cat_title); ?></a>
                            </td>
                            <td class="nowrap">
                                <a href="<?php echo JRoute::_('index.php?option=com_jicustomfields&task=field.edit&id=' . $item->fid);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->title); ?></a>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo (int) $item->fid; ?>
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