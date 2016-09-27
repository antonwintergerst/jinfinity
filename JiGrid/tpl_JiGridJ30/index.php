<?php
/**
 * @version     $Id: index.php 040 2014-04-10 10:58:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Provide easy access to common functions
$app = JFactory::getApplication();
$template = $app->getTemplate(true);
$templateparams = $template->params;
$gridparams = JComponentHelper::getParams('com_jigrid');

$doc = JFactory::getDocument();
function countModules($position) {
    $doc = JFactory::getDocument();
    return $doc->countModules($position);
}
// Init grid
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
$helperpath = JPATH_SITE.DS.'media'.DS.'jigrid'.DS.'helper.php';
if(!file_exists($helperpath)) {
    echo JText::_('TPL_JIGRID_WARNING_GRIDMISSING');
    exit;
}
require_once($helperpath);

$LayoutTools = new JiGridLayoutTools();

$GridHelper = new JiGridLayoutHelper();
$GridHelper->set('layoutdir', JPATH_SITE.DS.'media'.DS.'jigrid'.DS.'layout');
$GridHelper->set('styledir', JPATH_SITE.DS.'templates'.DS.$this->template.DS.'layout');
$grid = $GridHelper->getGrid();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
    <?php require_once($GridHelper->loadLayout('head'));  ?>
</head>
<?php require_once($GridHelper->loadLayout('index'));  ?>
</html>