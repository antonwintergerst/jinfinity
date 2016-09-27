<?php
/**
 * @version     $Id: head.php 048 2014-11-27 13:44:00Z Anton Wintergerst $
 * @package     JiGrid Template for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// grid head
$GridHelper->getHead();
$jinput = JFactory::getApplication()->input;

// shortcuts to client attributes
$screencontext = $jinput->get('screencontext');
$browsercontext = $jinput->get('browsercontext', null, 'raw');
?>
<jdoc:include type="head" />
<?php
// head modules ?>
<jdoc:include type="modules" name="head" style="jiplain" />
<?php
// bootstrap stylesheets
if($templateparams->get('bootstrap', 1)): ?>
    <link rel="stylesheet" type="text/css" href="media/jui/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="media/jui/css/icomoon.css" />
<?php endif; ?>
<?php
// template stylesheets ?>
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/assets/css/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/assets/css/style.css" type="text/css" />
<?php
// browser specific stylesheets
if(strpos($browsercontext, 'notie')==false):
    if(strpos($browsercontext, 'oldie')!==false):
        // old ie ?>
        <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/assets/css/oldie.css" type="text/css" />
    <?php elseif(strpos($browsercontext, 'ie')!==false):
        // ie ?>
        <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/assets/css/ie.css" type="text/css" />
    <?php endif; ?>
<?php endif; ?>
<?php
// screen specific stylesheets
if($screencontext=='phone'): ?>
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/assets/css/stylephone.css" type="text/css" />
<?php endif; ?>
<meta name="viewport" content="width=device-width, initial-scale = 1.0, user-scalable=yes" />
<?php
// google analytics
echo $templateparams->get('analytics', ''); ?>