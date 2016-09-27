<?php 
/**
 * @version     $Id: default.php 131 2014-12-16 12:59:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.helper');
$jparams = JComponentHelper::getParams('com_jimigrator');

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHTML::script('media/jimigrator/js/jquery.formsubmit.js');
    JHTML::script('media/jimigrator/js/jquery.jicheckbox.js');
    JHTML::script('media/jimigrator/js/jquery.jitoggler.js');
} else {
    JHTML::_('script', 'jquery.min.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.noconflict.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.formsubmit.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.jicheckbox.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.jitoggler.js', 'media/jimigrator/js/');
}
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.customcheckbox').jicheckbox({image:'<?php echo JURI::root(); ?>media/jimigrator/images/ui24/checkbox.png', width:'24', height:'24'});
            jQuery('.jitogglerbtn').jitoggler({tab:'.jitogglertab'});
            jQuery('.exportform').jiformsubmit({formtype:'async', btn:'.exportbtn', baseurl:'index.php?option=com_jimigrator&view=status', maxcalls:<?php echo (int) $jparams->get('maxcalls', 60); ?>});
        });
    }
</script>
<div class="jimigrator export">
    <div class="jistatus header">
        <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
        <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
        <div class="jistatustext">
            <h2>Ready to Export</h2>
        </div>
    </div>
    <form class="exportform" id="exportform" method="post" action="index.php?option=com_jimigrator&view=export&task=export">
        <?php if(isset($this->groups) && is_array($this->groups)): ?>
            <?php foreach($this->groups as $group): ?>
                <div class="processors">
                    <h2 class="header"><span><?php echo $group->title; ?> Exporters</span></h2>
                    <?php foreach($group->exporters as $exporter): ?>
                        <div class="exporter">
                            <div class="checkbox">
                                <input id="<?php echo 'exporter_'.$exporter->name; ?>" class="customcheckbox jitogglerbtn jid<?php echo $exporter->name; ?>" name="<?php echo 'exporters['.$exporter->name.']'; ?>" type="checkbox" />
                                <label for="<?php echo 'exporter_'.$exporter->name; ?>"><?php echo $exporter->description; ?></label>
                            </div>
                            <?php if(isset($this->form[$exporter->name]) && count($this->form[$exporter->name])>0): ?>
                                <div class="jiparams jitogglertab jid<?php echo $exporter->name; ?>">
                                    <div class="general">
                                        <ul class="adminformlist">
                                            <?php foreach($this->form[$exporter->name] as $JiField): ?>
                                                <?php if(!method_exists($JiField,'getFilter') || (method_exists($JiField,'getFilter') && $jparams->get('filters_enabled', 1)==1)): ?>
                                                    <li><?php echo $JiField->renderInputLabel(); ?><?php echo $JiField->renderInput(); ?></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="processors">
                <div class="general">
                    <p>WARNING: No export processors were found. Please disable Filters in the Ji Migrator component Options and try again.</p>
                </div>
            </div>
        <?php endif; ?>
        <div class="processors">
            <h2 class="header"><span>General Options</span></h2>
            <div class="general">
                <div class="checkbox">
                    <input id="purgetmp" class="customcheckbox" name="purgetmp" type="checkbox" checked="checked" />
                    <label for="purgetmp">Purge Temporary Files</label>
                </div>
            </div>
        </div>
        <h2 class="action"><a class="jibtn tier1action exportbtn" href="#" title="Start the export process with live progress">Export Content</a></h2>
        <?php /* >>> FREE >>> */ ?>
        <div class="processors">
            <div class="general">
                <?php $lang = JFactory::getLanguage();
                $lang->load('plg_system_jiframework', JPATH_ADMINISTRATOR, null, false, true); ?>
                <?php echo sprintf(JText::_('JI_PRO_UPGRADE'), JText::_('JiMigrator')); ?>
                <h2><?php echo JText::_('JI_PRO_FEATURES_TITLE'); ?></h2>
                <?php echo JText::_('JIMIGRATOR_PRO_FEATURES'); ?>
            </div>
        </div>
        <?php /* <<< FREE <<< */ ?>
        <div class="processors">
            <div class="general">
                <p>Please do not leave this page once starting the export process.</p>
                <p>View the active log as its generated here: <a class="label label-info activelogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog" title="Click to view the active log" target="_blank">View active log</a></p>
                <p>If the last export timed out, you can now resume it here: <a class="label label-info resumebtn" href="index.php?option=com_jimigrator&view=export&task=doexport" title="Click to continue export" target="_blank">Resume Export</a></p>
                <div class="debugprocess">
                    <input type="submit" value="Debug Export (no live progress)" />
                </div>
            </div>
        </div>
    </form>
    <?php if($this->migration): ?>
        <div class="jistatus header">
            <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="jistatustext">
                <ul class="jiactions">
                    <li>Migration archive was found from a previous export.<br>This will be overwritten if you start the export process again</li>
                    <li><a class="jibtn tier1action downloadbtn" href="index.php?option=com_jimigrator&view=export&task=download&dlfile=<?php echo $this->migration->path; ?>">Download Migration ZIP (<?php echo $this->migration->size; ?>)</a></li>
                    <li><a class="jibtn tier2action deletebtn" href="index.php?option=com_jimigrator&view=export&task=delete&dlfile=<?php echo $this->migration->path; ?>">Delete Migration ZIP (Make sure download is finished first!)</a></li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <div class="jistatus header">
            <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="jistatustext">
                <h2>Ready to Export</h2>
            </div>
        </div>
    <?php endif; ?>
    <?php echo sprintf(JText::_('JIMIGRATOR_FOOTER_TEXT'), JVERSION, date('Y')); ?>
</div>