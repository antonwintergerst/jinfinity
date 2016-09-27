<?php
/**
 * @version     $Id: default.php 070 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die;

$params = JComponentHelper::getParams('com_jiextensionmanager');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'ordering';

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.popover');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal');

// Add Scripts
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
    JHtml::script('media/jiextensionmanager/js/jquery.jiextensionmanager.js');
    JHTML::stylesheet('media/jiextensionmanager/css/jiextensionmanager.css');

    $sortFields = $this->getSortFields();
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('script', 'jquery.jiextensionmanager.js', 'media/jiextensionmanager/js/');
    JHTML::_('stylesheet', 'jiextensionmanager.css', 'media/jiextensionmanager/css/');
}
// Set Manager Vars
$ids = array();
foreach ($this->items as $item) {
    $ids[] = $item->id;
}
function makeSafe($str)
{
    return str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
}
$loading = '<div class="progress progress-striped active" style="min-width: 60px;"><div class="bar" style="width: 100%;"></div></div>';
?>
<script type="text/javascript">
    var jimanager;
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jimanager = jQuery('.jiextensionmanager').jiextensionmanager({
                url:'<?php echo JURI::root().'administrator/index.php?option=com_jiextensionmanager'; ?>',
                remoteurl:'http://www.jinfinity.com/index.php?option=com_jiextensionserver',
                dlkey:'<?php echo $params->get('dlkey', '', 'raw'); ?>',
                jversion:'<?php echo JVERSION; ?>'
            });
            jimanager.refresh();
        });
    }
</script>
<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo $this->btnToolbar; ?>
<div class="jiadmin jinotices"></div>
<div class="jiadmin jiextensionmanager<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' row-fluid'; ?>">
    <div class="extensions<?php if(version_compare(JVERSION, '3.0.0', 'l')) echo ' span12'; ?>">
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
        <form action="<?php echo JRoute::_('index.php?option=com_jiextensionmanager&view=extensions');?>" method="post" name="adminForm" id="adminForm">
            <?php if(version_compare(JVERSION, '3.0.0', 'ge')): ?>
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
                        <label for="filter_search" class="element-invisible"><?php echo JText::_('JIEXTENSIONMANAGER_FILTER_SEARCH');?></label>
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JIEXTENSIONMANAGER_FILTER_SEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JIEXTENSIONMANAGER_FILTER_SEARCH'); ?>" />
                    </div>
                    <div class="btn-group pull-left hidden-phone">
                        <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
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
                <?php endif; ?>

                <table class="table table-striped" id="extensionList">
                    <thead>
                    <tr>
                        <th width="1%" class="hidden-phone">
                            <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                        </th>
                        <th width="200">
                            <?php echo JHtml::_('grid.sort', 'JIEXTENSIONMANAGER_TITLE_LABEL', 'title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="16" class="hidden-tablet"><!-- website --></th>
                        <th width="80" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JIEXTENSIONMANAGER_TYPE_LABEL', 'type', $listDirn, $listOrder); ?>
                        </th>
                        <th width="200" class="nowrap hidden-phone">
                            <?php echo JHtml::_('grid.sort', 'JIEXTENSIONMANAGER_INSTALLED_LABEL', 'version', $listDirn, $listOrder); ?>
                        </th>
                        <th width="100" class="center">
                            <span class="loaded hide"><?php echo JText::_('JIEXTENSIONMANAGER_ACTION_LABEL'); ?></span>
                        </th>
                        <th width="200"><span class="loaded hide"><?php echo JText::_('JIEXTENSIONMANAGER_AVAILABLE'); ?></span></th>
                        <th width="10%" class="center hidden-phone">
                            <span class="upgradesubscription"><?php echo JText::_('JIEXTENSIONMANAGER_UPGRADE_SUBSCRIPTION_LABEL'); ?></span>
                        </th>
                        <th width="20"><!-- uninstall --></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; ?>
                    <?php foreach ($this->items as $item): ?>
                        <?php if($item->installed) {
                            $class = 'installed'.(empty($item->missing)? '' : ' has_missing');
                        } else {
                            $class = 'not_installed';
                        } ?>
                        <tr class="row<?php echo $i % 2; ?> jiext<?php echo $item->alias; ?> <?php echo $class; ?>">
                            <td class="center hidden-phone ext_checkbox">
								<span class="select hide"><?php echo JHtml::_('grid.id', $i, $item->id); ?></span>
                            </td>
                            <td class="nowrap ext_title item-title">
                                <div class="pull-left">
                                    <input type="hidden" class="downloadurl" id="url_<?php echo $item->id; ?>" value="" />
                                    <span class="item-image"><img src="<?php echo JURI::root().'media/jiextensionmanager/images/'.$item->alias.'-icon.png'; ?>" alt="" /></span>
                                    <span class="item-name"><?php echo JText::_($this->escape($item->title)); ?></span>
                                </div>
                            </td>
                            <td class="center hidden-phone hidden-tablet ext_website">
                                <a href="http://www.jinfinity.com/<?php echo $item->id; ?>" target="_blank">
                                    <i class="icon-out-2"></i>
                                </a>
                            </td>
                            <td class="nowrap hidden-phone hidden-tablet ext_types">
                                <?php echo $loading; ?>
                                <div class="loaded">
                                    <?php foreach ($item->types as $type) : ?>
                                        <?php
                                        switch ($type->type) {
                                            case 'mod':
                                                $icon = '<span class="label label-important">M</span>';
                                                break;
                                            case 'plg_content':
                                                $icon = '<span class="label label-info">P<small>C</small></span>';
                                                break;
                                            case 'plg_system':
                                                $icon = '<span class="label label-info">P<small>S</small></span>';
                                                break;
                                            case 'plg_editors-xtd':
                                                $icon = '<span class="label label-info">P<small>B</small></span>';
                                                break;
                                            default:
                                                $icon = '<span class="label label-success">C</span>';
                                                break;
                                        }
                                        ?>
                                        <span class="not_installed data hide disabled" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_' . strtoupper($type->type)); ?>">
											<?php echo $icon; ?>
										</span>
                                        <span class="installed data hide" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_' . strtoupper($type->type)); ?>">
											<a href="index.php?<?php echo $type->link; ?>" target="_blank"><?php echo $icon; ?></a>
										</span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="nowrap ext_installed">
                                <?php echo $loading; ?>
                                <div class="loaded hide">
									<span class="installed nowrap data hide">
										<span class="uptodate data hide">
											<span class="current_version badge badge-success" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_UPTODATE_DESC'); ?>">
												<?php echo $item->version; ?>
											</span>
										</span>
										<span class="downgrade data hide">
											<span class="current_version badge badge-success" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_DOWNGRADE_DESC'); ?>">
												<?php echo $item->version; ?>
											</span>
										</span>
										<span class="update data hide">
											<span class="current_version badge badge-warning" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_UPDATE_DESC'); ?>">
												<?php echo $item->version; ?>
											</span>
										</span>
                                        <?php
                                        $missing = '';
                                        if ($item->installed && !empty($item->missing)) {
                                            $missing = array();
                                            foreach ($item->missing as $m) {
                                                $missing[] = JText::_('JIEXTENSIONMANAGER_' . strtoupper($m));
                                            }
                                            $missing = JText::sprintf('JIEXTENSIONMANAGER_WARNING_MISSING_EXTENSIONS', implode(',', $missing));
                                        }
                                        ?>
                                        <span class="missing data <?php echo $missing ? '' : 'hide'; ?>">
											<span class="current_version badge badge-important hasPopover" data-trigger="hover" data-placement="right"
                                                  title="<?php echo '<i class="icon-warning"></i>'; ?>"
                                                  data-content="<?php echo $missing; ?>">
												<?php echo $item->version; ?>
											</span>
										</span>
                                        <span class="hasfree label label-success data hide" rel="tooltip" title="<?php echo makeSafe(sprintf(JText::_('JIEXTENSIONMANAGER_HASFREE_HINT'), JText::_($item->title))); ?>"><?php echo JText::_('JI_FREE'); ?></span>
                                        <span class="haspro label label-info data hide" rel="tooltip" title="<?php echo makeSafe(sprintf(JText::_('JIEXTENSIONMANAGER_HASPRO_HINT'), JText::_($item->title))); ?>"><?php echo JText::_('JI_PRO'); ?></span>
									</span>
                                </div>
                            </td>
                            <td class="center nowrap ext_install">
								<span>
									<span class="install btn btn-small btn-success data hide" onclick="jimanager.controller('install', '<?php echo $item->id; ?>', this);">
										<i class="icon-box-add"></i> <?php echo JText::_('JIEXTENSIONMANAGER_INSTALL'); ?>
									</span>
									<span class="update btn btn-small btn-warning data hide" onclick="jimanager.controller('update', '<?php echo $item->id; ?>', this);">
										<i class="icon-upload"></i> <?php echo JText::_('JIEXTENSIONMANAGER_UPDATE'); ?>
									</span>
									<span class="reinstall btn btn-small btn data hide" onclick="jimanager.controller('reinstall', '<?php echo $item->id; ?>', this);">
										<?php echo JText::_('JIEXTENSIONMANAGER_REINSTALL'); ?>
									</span>
									<span class="downgrade btn btn-small data hide" onclick="jimanager.controller('downgrade', '<?php echo $item->id; ?>', this);"
                                          rel="tooltip" title="<?php echo makeSafe(JText::_('JIEXTENSIONMANAGER_DOWNGRADE_DESC')); ?>">
										<?php echo JText::_('JIEXTENSIONMANAGER_DOWNGRADE'); ?>
									</span>
                                    <span class="hidden-tablet hidden-desktop nowrap">
										<div class="clearfix"></div>
										<span class="changelog data hide">
											<a href="http://www.jinfinity.com/<?php echo $item->id; ?>#changelog" target="_blank"><span class="new_version badge"></span></a>
										</span>
									</span>
								</span>
                            </td>
                            <td class="hidden-phone nowrap ext_new">
								<span class="nowrap">
									<span class="refresh no_external btn btn-small btn-primary data hide" onclick="jimanager.refresh();">
										<i class="icon-refresh"></i> <?php echo JText::_('JIEXTENSIONMANAGER_REFRESH'); ?>
									</span>
									<span class="changelog data hide">
										<span class="hidden-tablet">
											<a href="http://www.jinfinity.com/<?php echo $item->id; ?>#changelog" target="_blank" class="hasPopover changelogsummary" data-trigger="hover" title="<?php echo JText::_('JIEXTENSIONMANAGER_CHANGELOG'); ?>" data-content="">
                                                <span class="new_version badge"></span>
                                            </a>
										</span>
										<span class="hidden-desktop">
											<a href="http://www.jinfinity.com/<?php echo $item->id; ?>#changelog" target="_blank" class="changelog data hide">
                                                <span class="new_version badge"></span>
                                            </a>
										</span>
									</span>
                                    <span class="getfree label label-success hide disabled" rel="tooltip" title="<?php echo makeSafe(sprintf(JText::_('JIEXTENSIONMANAGER_GETFREE_HINT'), JText::_($item->title))); ?>"><?php echo JText::_('JI_FREE'); ?></span>
                                    <span class="getproupgrade label label-info hide disabled" rel="tooltip" title="<?php echo makeSafe(sprintf(JText::_('JIEXTENSIONMANAGER_GETPROUPGRADE_HINT'), JText::_($item->title))); ?>"><?php echo JText::_('JI_PRO'); ?></span>
                                    <span class="getpro label label-info hide disabled" rel="tooltip" title="<?php echo makeSafe(sprintf(JText::_('JIEXTENSIONMANAGER_GETPRO_HINT'), JText::_($item->title))); ?>"><?php echo JText::_('JI_PRO'); ?></span>
								</span>
                            </td>
                            <td class="center nowrap hidden-phone">
                                <a style="margin-bottom:4px;" class="btn btn-small btn-info hidden-tablet hide upgradesubscription" href="http://www.jinfinity.com/subscribe?ext=<?php echo $item->id; ?>" target="_blank">
                                    <i class="icon-basket"></i> <?php echo JText::_('JIEXTENSIONMANAGER_GET_PRO'); ?>
                                </a>
                                <a style="margin-bottom:4px;" class="btn btn-small btn-info hidden-desktop hide upgradesubscription" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_GET_PRO_DESC'); ?>" href="http://www.jinfinity.com/subscribe?ext=<?php echo $item->id; ?>" target="_blank">
                                    <i class="icon-basket"></i>
                                </a>
                                <a style="margin-bottom:4px;" class="btn btn-small btn-warning hidden-tablet hide renewsubscription" href="http://www.jinfinity.com/subscribe?ext=<?php echo $item->id; ?>" target="_blank">
                                    <i class="icon-basket"></i> <?php echo JText::_('JIEXTENSIONMANAGER_RENEW_SUBSCRIPTION_LABEL'); ?>
                                </a>
                                <a style="margin-bottom:4px;" class="btn btn-small btn-warning hidden-desktop hide renewsubscription" rel="tooltip" title="<?php echo JText::_('JIEXTENSIONMANAGER_RENEW_SUBSCRIPTION_LABEL'); ?>" href="http://www.jinfinity.com/subscribe?ext=<?php echo $item->id; ?>" target="_blank">
                                    <i class="icon-basket"></i>
                                </a>
                            </td>
                            <td class="center nowrap hidden-phone ext_uninstall">
                                <?php if ($item->id != 'jiextensionmanager') : ?>
                                    <span class="installed btn btn-micro btn-danger data hide" rel="tooltip" data-placement="left" title="<?php echo JText::_('JIEXTENSIONMANAGER_UNINSTALL'); ?>" onclick="jimanager.controller('uninstall', '<?php echo $item->id; ?>', this);">
										<i class="icon-cancel-2"></i>
									</span>
                                <?php endif; ?>
                            </td>
                            <td></td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <input type="hidden" name="task" value="" />
                <input type="hidden" name="boxchecked" value="0" />
                <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
                <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
                <?php echo JHtml::_('form.token'); ?>
            </div>
        </form>
    </div>
    <div class="footer">
        <div class="credits"><?php echo JText::_('JIEXTENSIONMANAGER_NONUMBER_THANKYOU'); ?></div>
    </div>
</div>