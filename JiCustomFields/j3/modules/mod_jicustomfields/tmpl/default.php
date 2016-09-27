<?php
/**
 * @version     $Id: default.php 015 2014-10-27 13:32:00Z Anton Wintergerst $
 * @package     JiCustomFields Fields Module for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
$included = $params->get('fields');
?>
<?php if(is_array($fields)): ?>
    <?php foreach($fields as $JiField): ?>
        <?php $JiField->prepareOutput(); ?>
        <?php echo $JiField->renderOutput(); ?>
    <?php endforeach; ?>
<?php endif; ?>