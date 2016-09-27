<?php
/**
 * @version     $Id: index.php 035 2014-03-17 13:09:00Z Anton Wintergerst $
 * @package     JiGrid Template for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$browsercontext = $jinput->get('browsercontext', null, 'raw');
?>
<body<?php if($browsercontext!=null) echo ' class="'.$browsercontext.'"'; ?>>
<?php include($GridHelper->loadLayout($grid->get('alias'), 'grid')); ?>
</body>