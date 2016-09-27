<?php 
/**
 * @version     $Id: default.php 041 2014-12-12 13:37:00Z Anton Wintergerst $
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
} else {
    JHTML::_('script', 'jquery.min.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.noconflict.js', 'media/jimigrator/js/');
    JHTML::_('script', 'jquery.formsubmit.js', 'media/jimigrator/js/');
}
?>
<script type="text/javascript">
    var restoreform = null;
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.snapshotform').jiformsubmit({formtype:'async', btn:'.snapshotbtn', baseurl:'index.php?option=com_jimigrator&view=status', maxcalls:<?php echo (int) $jparams->get('maxcalls', 60); ?>});
            restoreform = jQuery('.restoreform').jiformsubmit({formtype:'async', baseurl:'index.php?option=com_jimigrator&view=status', maxcalls:<?php echo (int) $jparams->get('maxcalls', 60); ?>});
            jQuery('.restorebtn').on('click', function(e) {
                var btn = e.target != null ? e.target : e.srcElement;
                jQuery('.restoreform').attr('action', jQuery(btn).attr('href'));
                restoreform.formAsyncSubmit();
                e.preventDefault();
                e.stopPropagation();
            });
        });
    }
</script>
<div class="jimigrator snapshots">
    <div class="jistatus header">
        <div class="meter blue animate totalprogress"><span class="bar" style="width: 0%"><span></span></span></div>
        <div class="meter animate passprogress"><span class="bar" style="width: 0%"><span></span></span></div>
        <div class="jistatustext">
            <h2>Ready to Snapshot</h2>
        </div>
    </div>
    <form class="snapshotform" id="snapshotform" method="post" action="index.php?option=com_jimigrator&view=snapshots&task=snapshot">
        <h2><a class="jibtn tier1action small snapshotbtn" href="index.php?option=com_jimigrator&view=snapshots&task=snapshot" title="Click to create snapshot" target="_blank">Create Snapshot</a></h2>
        <p>Use Create Snapshot to generate a snapshot of the entire database.<br>For large databases this may take some time.</p>
    </form>
    <h2>Database Snapshots</h2>
    <?php if(is_array($this->snapshots)): ?>
        <form class="restoreform" id="restoreform" method="post" action="index.php?option=com_jimigrator&view=snapshots&task=restore">
            <div class="snapshotlist">
                <?php $i = 0; ?>
                <?php foreach($this->snapshots as $snapshot): ?>
                    <div class="snapshotrow row<?php echo $i % 2; ?>">
                        <a class="jibtn tier1action small restorebtn" href="<?php echo $snapshot->restorelink; ?>" title="Click to restore this snapshot" target="_blank">Restore</a>
                        <a class="viewlabel" href="<?php echo $snapshot->viewlink; ?>" title="Click to view this snapshot" target="_blank"><?php echo $snapshot->name; ?></a>
                    </div>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </div>
        </form>
    <?php else: ?>
    	<p>No snapshots have been created yet.</p>
	<?php endif; ?>
</div>