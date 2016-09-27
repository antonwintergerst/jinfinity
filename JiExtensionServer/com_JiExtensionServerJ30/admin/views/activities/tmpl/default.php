<?php
/**
 * @version     $Id: default.php 050 2013-06-17 11:16:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'ordering';
if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_jiextensionserver&task=extensions.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'extensionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

//$sortFields = $this->getSortFields();

// Load Scripts
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHtml::_('bootstrap.tooltip');
    JHtml::_('behavior.multiselect');
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
    JHtml::_('behavior.framework', true);
    JHTML::script('administrator/components/com_jiextensionserver/assets/js/mootools.zeroclipboard.js');
} else {
    JHTML::_('script', 'jquery.min.js', 'administrator/components/com_jiextensionserver/assets/js/');
    JHTML::_('script', 'jquery.noconflict.js', 'administrator/components/com_jiextensionserver/assets/js/');
    JHtml::_('behavior.framework', true);
    JHTML::script('administrator/components/com_jiextensionserver/assets/js/mootools.zeroclipboard.js');
}
$model = JModelLegacy::getInstance('Token', 'JiExtensionServerModel');
$token = $model->getToken();
?>
<div class="jiextensionserver">
    <div class="extensions">
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
        <form action="<?php echo JRoute::_('index.php?option=com_jiextensionserver&view=activities');?>" method="post" name="adminForm" id="adminForm">
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
                        <th width="5%" class="nowrap center">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_USER_LABEL', 'a.uid', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_EXTENSION', 'e.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_JVERSION_LABEL', 'e.jversion', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_SUBVERSION_LABEL', 'e.subversion', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_SITE_LABEL', 'a.site', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_ACTIVITY', 'a.activity', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'COM_JIEXTENSIONSERVER_DATE_LABEL', 'date', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item): ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="nowrap">
                                <a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $item->uid);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->username); ?></a>
                            </td>
                            <td class="center nowrap">
                                <a href="<?php echo JRoute::_('index.php?option=com_jiextensionserver&task=extension.edit&id=' . $item->eid);?>" title="<?php echo JText::_('JACTION_EDIT');?>"><?php echo $this->escape($item->title); ?></a>
                            </td>
                            <td class="center small hidden-phone">
                                <img src="<?php echo JURI::root().'administrator/components/com_jiextensionserver/assets/images/jversion'.str_replace('.', '', $item->jversion).'.png'; ?>" alt="<?php echo $item->jversion; ?>" />
                            </td>
                            <td class="center small hidden-phone">
                                <?php echo $item->subversion; ?>
                            </td>
                            <td class="center small hidden-phone">
                                <a href="<?php echo $item->site; ?>" title="View site" target="_blank"><?php echo $item->site; ?></a>
                            </td>
                            <td class="center small hidden-phone">
                                <?php echo $item->activity; ?>
                            </td>
                            <td class="center nowrap small hidden-phone">
                                <?php echo ($item->date!=null && $item->publish_down!='0000-00-00 00:00:00')? JHtml::_('date', $item->date, 'Y-m-d H:i:s') : 'Unknown'; ?>
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