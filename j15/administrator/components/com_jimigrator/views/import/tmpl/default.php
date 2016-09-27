<?php 
/**
 * @version     $Id: default.php 157 2015-01-04 12:17:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2015 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.helper');
$jparams = JComponentHelper::getParams('com_jimigrator');

$state = $this->importstate;
$importstatus = ($state=='upload')? 'Waiting for Migration ZIP' : 'Ready to Import';

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

$maxfilesize = ini_get('post_max_size');
switch(substr($maxfilesize,-1))
{
    case 'G':
        $maxfilesize = $maxfilesize * 1024;
    case 'M':
        $maxfilesize = $maxfilesize * 1024;
    case 'K':
        $maxfilesize = $maxfilesize * 1024;
}
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.customcheckbox').jicheckbox({image:'<?php echo JURI::root(); ?>media/jimigrator/images/ui24/checkbox.png', width:'24', height:'24'});
            jQuery('.jitogglerbtn').jitoggler({tab:'.jitogglertab'});
            var uploader = jQuery('.uploadform').jiformsubmit({
                btn:'.uploadbtn',
                baseurl:'index.php?option=com_jimigrator&view=status',
                maxfilesize:<?php echo (int) $maxfilesize; ?>
            });
            jQuery('.uploadform').on('success', function() {
                window.location.href = '<?php echo JURI::root(); ?>administrator/index.php?option=com_jimigrator&view=import';
            });
            jQuery('.importform').jiformsubmit({
                formtype:'async',
                btn:'.importbtn',
                baseurl:'index.php?option=com_jimigrator&view=status',
                maxcalls:<?php echo (int) $jparams->get('maxcalls', 60); ?>
            });
        });
    }
</script>
<div class="jimigrator import">
    <form class="uploadform" id="uploadform" method="post" action="index.php?option=com_jimigrator&view=import&task=upload" enctype="multipart/form-data">
    	<?php if($state=='import'): ?>
    		<div class="processors">
	    		<h2 class="header"><span>Current Migration Contents</span></h2>
		    	<?php if(isset($this->content) && is_array($this->content) && count($this->content)>0): ?>
                    <div class="general contents">
                        <ul class="adminformlist">
                            <?php foreach($this->content as $contentfile): ?>
                                <li class="contentfile">
                                    <span><?php echo $contentfile->name; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <p class="clearcontents">Clear the pending archive contents listed above: <a class="label label-info clearbtn" href="index.php?option=com_jimigrator&amp;view=import&amp;task=clear" title="Click to clear the current contents waiting to be imported">Clear Contents</a></p>
	        	<?php endif; ?>
        	</div>
		<?php endif; ?>
		<div class="processors">
    		<h2 class="header"><span>Upload New Migration</span></h2>
	        <div class="general">
                <p>Use this field to upload a migration archive,<br>or manually place the migration archive here <strong>.../administrator/components/com_jimigrator/tmp/input.zip</strong> using your favourite FTP client<br>(manual method still requires the Upload Content button to be clicked to extract the archive).</p>
	            <ul class="adminformlist">
	                <li>
	                    <label for="sourcezip">Migration ZIP</label>
	                    <input class="inputbox" id="sourcezip" name="sourcezip" type="file" title="Migration ZIP" />
	                </li>
	            </ul>
	        </div>
        </div>
        <div class="jistatus header">
            <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="jistatustext">
                <h2><?php echo $importstatus; ?></h2>
            </div>
        </div>
        <h2 class="action <?php echo ($state!='import')? 'ready':'waiting'; ?>">
            <a class="jibtn tier1action uploadbtn" href="#" title="Upload migration ZIP and prepare for new import">STEP 1: Upload Content</a>
        </h2>
        <div class="debugprocess">
            <input type="submit" value="Debug Upload (no live progress)" />
        </div>
    </form>
    <?php if($state=='import'): ?>
        <h2>STEP 1: Complete!</h2>
        <p>Now select the content types to import below. Options for the importer will appear once a content type is selected. Once all required content types have been configured then <strong>scroll down for STEP 2.</strong></p>
    <?php endif; ?>
    <form class="importform" id="importform" method="post" action="index.php?option=com_jimigrator&amp;view=import&amp;task=import">
        <?php if(isset($this->groups) && is_array($this->groups)): ?>
            <?php foreach($this->groups as $group): ?>
                <div class="processors">
                    <h2 class="header"><span><?php echo $group->title; ?> Importers</span></h2>
                    <?php foreach($group->importers as $importer): ?>
                        <div class="importer <?php echo ($importer->ready)? 'ready':'waiting'; ?>">
                            <div class="checkbox">
                                <input id="<?php echo 'importer_'.$importer->name; ?>" class="customcheckbox jitogglerbtn jid<?php echo $importer->name; ?>" name="<?php echo 'importers['.$importer->name.']'; ?>" type="checkbox" />
                                <label for="<?php echo 'importer_'.$importer->name; ?>"><?php echo $importer->description; ?></label>
                            </div>
                            <?php if(isset($this->form[$importer->name])): ?>
                                <div class="jiparams jitogglertab jid<?php echo $importer->name; ?>">
                                    <div class="general">
                                        <ul class="adminformlist">
                                            <?php foreach($this->form[$importer->name] as $JiField): ?>
                                                <li><?php echo $JiField->renderInputLabel(); ?><?php echo $JiField->renderInput(); ?></li>
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
                    <p>WARNING: No import processors were found. Please disable Filters in the Ji Migrator component Options and try again.</p>
                </div>
            </div>
        <?php endif; ?>
        <div class="processors">
            <h2 class="header"><span>General Options</span></h2>
            <div class="general">
                <div class="checkbox">
                    <input id="dobackup" class="customcheckbox" name="dobackup" type="checkbox" checked="checked" />
                    <label for="dobackup">Take Backup</label>
                </div>
                <div class="checkbox">
                    <input id="resetglobalvars" class="customcheckbox" name="resetglobalvars" type="checkbox" checked="checked" />
                    <label for="resetglobalvars">Reset Content Transposition Maps</label>
                </div>
            </div>
        </div>
        <div class="jistatus header">
            <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
            <div class="jistatustext">
                <h2><?php echo $importstatus; ?></h2>
            </div>
        </div>
        <h2 class="action <?php echo ($state=='import')? 'ready':'waiting'; ?>"><a class="jibtn tier1action importbtn" href="#" title="Start the import process with live progress">STEP 2: Import Content</a></h2>
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
                <p>Please do not leave this page once starting the import process.</p>
                <p>View the active log as its generated here: <a class="label label-info activelogbtn" href="index.php?option=com_jimigrator&view=logs&task=showlog" title="Click to view the active log" target="_blank">View active log</a></p>
                <p>If the last import timed out, you can now resume it here: <a class="label label-info resumebtn" href="index.php?option=com_jimigrator&view=import&task=doimport" title="Click to continue import" target="_blank">Resume Import</a></p>
                <div class="debugprocess">
                    <input type="submit" value="Debug Import (no live progress)" />
                </div>
            </div>
        </div>
    </form>
    <?php echo sprintf(JText::_('JIMIGRATOR_FOOTER_TEXT'), JVERSION, date('Y')); ?>
</div>