<?php
/**
 * @version     $Id: default.php 064 2014-12-16 20:18:00Z Anton Wintergerst $
 * @package     JiMediaBrowser for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css');


$modaltype = $params->get('modaltype');
// jQuery modals
if($params->get('load_jquery', 1)==1) {
    if(version_compare( JVERSION, '3.0.0', 'ge' )) {
        JHtml::_('jquery.framework');
    } else {
        // Joomla 2.5 Legacy
        $document->addScript(JURI::root().'media/jiframework/js/jquery.min.js');
        $document->addScript(JURI::root().'media/jiframework/js/jquery.noconflict.js');
    }
}
switch($modaltype) {
    case 'slimbox2':
        if($params->get('load_slimbox2', 1)==1) {
            $document->addStyleSheet(JURI::root().'media/jiframework/modals/slimbox2/css/slimbox2.css');
            $document->addScript(JURI::root().'media/jiframework/modals/slimbox2/js/slimbox2.js');
        }
        break;
    case 'shadowbox':
        if($params->get('load_shadowbox', 1)==1) {
            $document->addStyleSheet(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.css');
            $document->addScript(JURI::root().'media/jiframework/modals/shadowbox/shadowbox.js');
            $document->addScriptDeclaration('Shadowbox.init();');
        }
        break;
    case 'fancybox':
        if($params->get('load_fancybox', 1)==1) {
            $document->addStyleSheet(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.css?v=2.1.5');
            $document->addScript(JURI::root().'media/jiframework/modals/fancybox/jquery.fancybox.js?v=2.1.5');
            $document->addScriptDeclaration('
            jQuery(document).ready(function() {
                jQuery(".fancybox").fancybox({
                    openEffect	: \'none\',
                    closeEffect	: \'none\'
                });
            });');
        }
        break;
    default:
        break;
}
// jimediabrowser
$document->addStyleSheet(JURI::root().'media/jimediabrowser/css/jimediabrowser.css');
$document->addScript(JURI::root().'media/jimediabrowser/js/jquery.jimediabrowser.js');
?>
<div class="jimediabrowser <?php echo strtolower($this->get('id')); ?>">
    <script type="text/javascript">
        if(typeof jQuery!='undefined') {
            jQuery(document).ready(function() {
                jQuery('#<?php echo $this->get('id'); ?>mediabrowser').jimediabrowser({
                    style:'<?php echo $params->get('default_layout', 'large grid'); ?>',
                    baseurl:'<?php echo JURI::root(); ?>',
                    url:'<?php echo JURI::root().'media/jimediabrowser/index.php' ?>',
                    id:'<?php echo $this->get('id'); ?>',
                    data: '<?php echo str_replace("'", "\\'", json_encode($this->get('data'))); ?>',
                    buffertime:<?php echo (int) $params->get('buffertime', 200); ?>
                });
            });
        }
    </script>
    <div class="jbouter">
        <div id="<?php echo $this->get('id'); ?>mediabrowser" class="jimbinner">
            <ul class="mmbox">
                <li class="mbhead">
                    <ul class="mbrow titlebar">
                        <li class="col1 span8 path">
                            <div class="icontainer"><input class="inputbox" name="mbpath" type="text">
                                <a class="jibtn icon16 ui refresh" href="#" title="Refresh list"><i class="jiicon icon-repeat"></i></a>
                            </div>
                        </li>
                        <li class="col2 span3 search">
                            <div class="icontainer">
                                <input class="inputbox" name="mbsearchword" type="text">
                                <a class="jibtn icon16 ui search" href="#" title="Search"><i class="jiicon icon-search"></i></a>
                            </div>
                        </li>
                        <li class="col3 span1 liststyle">
                            <a class="jibtn icon16 ui liststyle" href="#" title="Toggle list style"><i class="jiicon liststyleicon icon-th-large"></i></a>
                        </li>
                        <li class="mbbody large grid cols4">
                            <!-- AJAX Media Browser will continue to render here -->
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>