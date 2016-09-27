<?php
/**
 * @version     $Id: default.php 061 2014-10-28 10:24:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$JiField->prepareInput(); ?>

<ul class="jitrow row-fluid jifield jid<?php echo $JiField->get('id'); ?>">
    <li class="span12"><h2><?php echo $JiField->renderInputLabel(); ?></h2></li>
    <li class="fieldvalue span12">
        <div class="fieldvalue">
            <?php echo $JiField->renderInputScript(); ?>
            <?php echo $JiField->renderInput(); ?>
        </div>
    </li>
</ul>