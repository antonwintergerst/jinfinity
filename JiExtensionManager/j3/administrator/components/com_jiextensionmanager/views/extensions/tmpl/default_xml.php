<?php
/**
 * @version     $Id: default_update.php 026 2014-12-17 14:32:00Z Anton Wintergerst $
 * @package     JiExtensionManager for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

header("Expires: Wed, 1 Jun 1988 09:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8"?>'
?>
<extensions>
    <?php foreach($this->items as $item): ?>
        <extension>
            <alias><?php echo $item->alias; ?></alias>
            <version><?php echo $item->version; ?></version>
            <pro><?php echo $item->pro; ?></pro>
            <missing><?php echo (!empty($item->missing) ? JText::sprintf('JIEXTENSIONMANAGER_WARNING_MISSING_EXTENSIONS', implode(',', $item->missing)) : ''); ?></missing>
        </extension>
    <?php endforeach; ?>
</extensions>
<?php exit;