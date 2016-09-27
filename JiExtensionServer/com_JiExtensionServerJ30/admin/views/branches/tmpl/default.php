<?php
/**
 * @version     $Id: default.php 018 2013-06-20 10:22:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
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
$saveOrder	= $listOrder == 'ordering';
if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_jiextensionserver&task=branches.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'branchList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

// Load Scripts
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHTML::stylesheet('administrator/components/com_jiextensionserver/assets/css/jiextensionserver.css');
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jiextensionserver.css', 'administrator/components/com_jiextensionserver/assets/css/');
}
?>
<div class="jinfinity jiextensionserver<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' row-fluid'; ?>">
    <div class="branches<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' span12'; ?>">
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
        <form action="<?php echo JRoute::_('index.php?option=com_jiextensionserver&view=branches');?>" method="post" name="adminForm" id="adminForm">
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
                        <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_JIEXTENSIONSERVER_FILTER_SEARCH');?></label>
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_JIEXTENSIONSERVER_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_JIEXTENSIONSERVER_FILTER_SEARCH'); ?>" />
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
                <table class="table table-striped" id="articleList">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                        </th>
                        <th width="1%" class="hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
                        </th>
                        <th>
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_TITLE_LABEL', 'e.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_ALIAS_LABEL', 'e.alias', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_PUBLISHER_LABEL', 'e.publisher', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_PREMIUM_LABEL', 'b.premium', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_BRANCH_LABEL', 'b.branch', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_PUBLISHUP_LABEL', 'b.publish_up', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_PUBLISHDOWN_LABEL', 'b.publish_down', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $currentextension = '';
                    foreach ($this->items as $i => $item) :
                        $item->max_ordering = 0;
                        $ordering   = ($listOrder == 'ordering');
                        //$canCreate  = $user->authorise('core.create',     'com_content.category.'.$item->catid);
                        $canEdit    = $user->authorise('core.edit',       'com_content.article.'.$item->id);
                        $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        //$canEditOwn = $user->authorise('core.edit.own',   'com_content.article.'.$item->id) && $item->created_by == $userId;
                        $canChange  = $user->authorise('core.edit.state', 'com_content.article.'.$item->id) && $canCheckin;
                        ?>
                        <?php if($currentextension!=$item->eid): ?>
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
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->estate, $i, 'extensions.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                </div>
                            </td>
                            <td class="nowrap has-context">
                                <div class="pull-left">
                                    <a href="<?php echo JRoute::_('index.php?option=com_jiextensionserver&task=extension.edit&id=' . $item->eid);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->title); ?></a>
                                </div>
                                <div class="pull-left">
                                    <?php
                                    if(version_compare(JVERSION, '3.0.0', 'ge')) {
                                        // Create dropdown items
                                        JHtml::_('dropdown.edit', $item->id, 'article.');
                                        JHtml::_('dropdown.divider');
                                        if ($item->estate) :
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
                            <td class="center small hidden-phone">
                                <?php echo JText::_('Jinfinity'); ?>
                            </td>
                            <td class="small hidden-phone">
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->downloadhits; ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->updatehits; ?>
                            </td>
                            <td class="center nowrap small hidden-phone">
                                <?php echo JHtml::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="center nowrap small hidden-phone">
                                <?php echo ($item->publish_down!=null && $item->publish_down!='0000-00-00 00:00:00')? JHtml::_('date', $item->publish_down, JText::_('DATE_FORMAT_LC4')) : 'Never'; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo (int) $item->eid; ?>
                            </td>
                        </tr>
                        <?php $currentextension = $item->eid; ?>
                    <?php endif; ?>
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
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'branches.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                                </div>
                            </td>
                            <td class="nowrap has-context">
                                <div class="pull-left">
                                    <span class="gi">|—</span>
                                    <a href="<?php echo JRoute::_('index.php?option=com_jiextensionserver&task=branch.edit&id=' . $item->id);?>" title="<?php echo JText::_('JACTION_EDIT');?>">
                                        <?php if(in_array($item->branch, array('free', 'pro'))): ?>
                                            <?php echo strtoupper($item->branch); ?>
                                        <?php else: ?>
                                            <img src="<?php echo JURI::root().'administrator/components/com_jiextensionserver/assets/images/jversion'.str_replace('.', '', $item->branch).'.png'; ?>" alt="<?php echo $item->branch; ?>" />
                                        <?php endif; ?>
                                    </a>
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
                            <td class="center small hidden-phone">
                                <?php echo JText::_('Jinfinity'); ?>
                            </td>
                            <td class="small hidden-phone">
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->downloadhits; ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->updatehits; ?>
                            </td>
                            <td class="center nowrap small hidden-phone">
                                <?php echo JHtml::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="center nowrap small hidden-phone">
                                <?php echo ($item->publish_down!=null && $item->publish_down!='0000-00-00 00:00:00')? JHtml::_('date', $item->publish_down, JText::_('DATE_FORMAT_LC4')) : 'Never'; ?>
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
        <span class="copyright">Jinfinity Extension Server for Joomla 2.5-3.0 Copyright (C) 2013 Jinfinity. http://www.jinfinity.com</span>
    </div>
</div>