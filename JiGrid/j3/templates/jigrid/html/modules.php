<?php
/**
 * @version     $Id: modules.php 010 2013-07-19 11:58:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

function modChrome_jigrid($module, &$params, $attribs) {
	if(!empty($module->content)): ?>
		<div class="moduletable<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
            <?php if((bool) $module->showtitle): ?>
                <div class="moduletitle">
                    <h3><?php echo $module->title; ?></h3>
                </div>
            <?php endif; ?>
		    <div class="modulebody">
			    <?php echo $module->content; ?>
			</div>
		</div>
	<?php endif;
}
