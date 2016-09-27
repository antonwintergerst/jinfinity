<?php 
/**
 * @version     $Id: default.php 022 2014-12-12 13:37:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="jimigrator logs">
    <h2>Migration Logs</h2>
    <?php if(is_array($this->logs)): ?>
    <div class="loglist">
    	<?php foreach($this->logs as $log): ?>
    		<a href="<?php echo $log->link; ?>" title="Click to view this log" target="_blank"><?php echo $log->name; ?></a>
		<?php endforeach; ?>
    </div>
    <?php else: ?>
    	<p>No logs have been created yet.</p>
	<?php endif; ?>
</div>