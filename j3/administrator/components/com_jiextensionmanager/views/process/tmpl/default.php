<?php
/**
 * @version     $Id: default.php 055 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$params = JComponentHelper::getParams('com_jiextensionmanager');

$task = JFactory::getApplication()->input->get('task');

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHTML::stylesheet('media/jiextensionmanager/css/jiextensionmanager.css');
    JHtml::script('media/jiextensionmanager/js/jquery.jiextensionmanager.js');
} else {
    JHTML::_('behavior.tooltip', '.tooltip');
    JHtml::_('bootstrap.loadCSS');
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('script', 'jquery.jiextensionmanager.js', 'media/jiextensionmanager/js/');
    JHTML::_('stylesheet', 'jiextensionmanager.css', 'media/jiextensionmanager/css/');
}
?>
<script type="text/javascript">
    var jimanager;
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jimanager = jQuery('.jiextensionmanager').jiextensionmanager({
                url:'<?php echo JURI::root().'administrator/index.php?option=com_jiextensionmanager'; ?>',
                remoteurl:'http://www.jinfinity.com/index.php?option=com_jiextensionserver',
                dlkey:'<?php echo $params->get('dlkey', ''); ?>',
                jversion:'<?php echo JVERSION; ?>',
                ids: ['<?php echo implode("', '", array_keys($this->items)); ?>'],
                token: '<?php echo JSession::getFormToken(); ?>'
            });
        });
    }
</script>
<div class="jinfinity jiextensionmanager">
    <div class="titles">
        <div class="title pre process">
            <h2>
                <?php echo JText::_('JIEXTENSIONMANAGER_' . strtoupper($task)); ?>:
				<span class="btn btn-primary" onclick="jimanager.process('<?php echo $task; ?>');">
					<?php echo JText::_('JIEXTENSIONMANAGER_START'); ?>
				</span>
            </h2>
        </div>
        <div class="title failed process hide">
            <h2>
                <?php echo JText::_('JIEXTENSIONMANAGER_' . strtoupper($task)); ?>:
				<span class="btn btn-primary" onclick="jimanager.process('<?php echo $task; ?>');">
					<?php echo JText::_('JIEXTENSIONMANAGER_RETRY'); ?>
				</span>
            </h2>
        </div>
        <div class="title processing hide">
            <h2><?php echo JText::sprintf('JIEXTENSIONMANAGER_PROCESSING_' . strtoupper($task), '...'); ?></h2>
        </div>
        <div class="title done process hide">
            <div class="alert alert-success">
                <h2><?php echo JText::_('JIEXTENSIONMANAGER_FINISHED'); ?></h2>
            </div>
            <?php if ($task != 'uninstall') : ?>
                <div class="alert alert-warning"><?php echo JText::_('JIEXTENSIONMANAGER_WARNING_CLEANCACHE'); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <table class="table processlist">
        <tbody>
        <?php foreach ($this->items as $item) : ?>
            <tr id="row_<?php echo $item->id; ?>">
                <td width="1%" nowrap="nowrap" class="ext_name">
                    <i class="jiicon icon-<?php echo $item->alias; ?>"></i>
                    <?php echo JText::_($item->title); ?>
                </td>
                <td class="statuses">
                    <input type="hidden" id="url_<?php echo $item->id; ?>" value="<?php echo $item->url; ?>" />

                    <div class="queue_<?php echo $item->id; ?> status process queued">
                        <span class="label"><?php echo JText::_('JIEXTENSIONMANAGER_QUEUED'); ?></span>
                    </div>
                    <div class="processing_<?php echo $item->id; ?> status processing hide">
                        <div class="progress progress-striped active">
                            <div class="bar" style="width: 100%;"></div>
                        </div>
                    </div>
                    <div class="success_<?php echo $item->id; ?> status success process hide">
                        <span class="label label-success"><?php echo JText::_(($task == 'uninstall') ? 'JIEXTENSIONMANAGER_UNINSTALLED' : 'JIEXTENSIONMANAGER_INSTALLED'); ?></span>
                    </div>
                    <div class="failed_<?php echo $item->id; ?> status failed process hide">
                        <span class="label label-important"><?php echo JText::_('JIEXTENSIONMANAGER_FAILED'); ?></span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>